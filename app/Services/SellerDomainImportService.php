<?php

namespace App\Services;

use App\Jobs\CrawlSellerDomain;
use App\Models\Category;
use App\Models\SellerDomainImport;
use App\Models\SellerImportCandidate;
use App\Models\SellerImportJob;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\DomCrawler\Crawler;

class SellerDomainImportService
{
    public function register(Vendor $vendor, string $input): SellerDomainImport
    {
        $domain = $this->publicDomain($input);
        $token = 'sushako-'.Str::lower(Str::random(32));

        return SellerDomainImport::query()->updateOrCreate(
            ['vendor_id' => $vendor->id, 'domain' => $domain],
            [
                'verification_token' => $token,
                'verification_method' => 'well_known_file',
                'status' => 'pending',
                'page_limit' => 100,
                'crawl_depth' => 2,
            ],
        );
    }

    public function publicDomain(string $input): string
    {
        $value = trim($input);
        if (! str_contains($value, '://')) {
            $value = 'https://'.$value;
        }

        $parts = parse_url($value);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (($parts['scheme'] ?? null) !== 'https'
            || $host === ''
            || isset($parts['user'], $parts['pass'], $parts['port'], $parts['query'], $parts['fragment'])
            || filter_var($host, FILTER_VALIDATE_IP)
            || ! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z]{2,63}$/', $host)) {
            throw ValidationException::withMessages(['domain' => 'Enter a public HTTPS domain you control.']);
        }

        $records = array_merge(
            dns_get_record($host, DNS_A) ?: [],
            dns_get_record($host, DNS_AAAA) ?: [],
        );

        if ($records === []) {
            throw ValidationException::withMessages(['domain' => 'The domain does not resolve to a public address.']);
        }

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw ValidationException::withMessages(['domain' => 'The domain resolves to a restricted network address.']);
            }
        }

        return $host;
    }

    public function verifyDnsTxt(SellerDomainImport $import): SellerDomainImport
    {
        $records = dns_get_record('_sushako-verification.'.$import->domain, DNS_TXT) ?: [];
        $verified = collect($records)->contains(function (array $record) use ($import): bool {
            $values = $record['txt'] ?? $record['entries'] ?? [];
            $values = is_array($values) ? $values : [$values];

            return in_array($import->verification_token, $values, true);
        });

        if (! $verified) {
            throw ValidationException::withMessages([
                'domain' => 'The DNS TXT verification record was not found yet.',
            ]);
        }

        $import->forceFill([
            'status' => 'verified',
            'verified_at' => now(),
        ])->save();

        return $import;
    }

    public function start(Vendor $vendor, SellerDomainImport $import): SellerImportJob
    {
        abort_unless($import->vendor_id === $vendor->id && $import->status === 'verified', 404);

        $key = hash('sha256', $vendor->id.'|'.$import->id.'|'.now()->format('Y-m-d-H'));
        $job = SellerImportJob::query()->firstOrCreate(
            ['idempotency_key' => $key],
            ['vendor_id' => $vendor->id, 'seller_domain_import_id' => $import->id, 'status' => 'queued'],
        );

        if ($job->wasRecentlyCreated || in_array($job->status, ['failed', 'cancelled'], true)) {
            $job->forceFill(['status' => 'queued', 'error_message' => null, 'finished_at' => null])->save();
            CrawlSellerDomain::dispatch($job->id);
        }

        return $job;
    }

    public function crawl(SellerImportJob $job): void
    {
        $import = $job->domainImport;
        $job->forceFill(['status' => 'running', 'started_at' => now(), 'error_message' => null])->save();

        try {
            $robots = $this->robots($import->domain);
            $queue = [['url' => 'https://'.$import->domain.'/', 'depth' => 0]];
            $seen = [];
            $pages = 0;

            while ($queue !== [] && $pages < min((int) $import->page_limit, 100)) {
                $current = array_shift($queue);
                $url = $this->safeUrl($current['url'], $import->domain);
                if (! $this->robotsAllows($robots, parse_url($url, PHP_URL_PATH) ?: '/')) continue;
                if (isset($seen[$url])) continue;
                $seen[$url] = true;
                $html = $this->fetch($url);
                $pages++;
                $job->increment('pages_found');

                foreach ($this->extractCandidates($html, $url, $import->domain) as $payload) {
                    $hash = hash('sha256', $payload['source_url']);
                    $duplicate = $this->duplicateProduct($job->vendor_id, $payload);
                    SellerImportCandidate::query()->updateOrCreate(
                        ['seller_import_job_id' => $job->id, 'source_hash' => $hash],
                        [
                            'source_url' => $payload['source_url'],
                            'payload' => $payload,
                            'category_suggestion' => $this->suggestCategory($payload),
                            'duplicate_product_id' => $duplicate?->id,
                            'status' => $duplicate ? 'duplicate' : ($this->suggestCategory($payload)['confidence'] >= 0.75 ? 'ready_for_review' : 'needs_review'),
                        ],
                    );
                }

                if ($current['depth'] < (int) $import->crawl_depth) {
                    foreach ($this->links($html, $url, $import->domain) as $link) {
                        if (count($queue) + $pages >= (int) $import->page_limit) break;
                        if ($this->robotsAllows($robots, parse_url($link, PHP_URL_PATH) ?: '/')) $queue[] = ['url' => $link, 'depth' => $current['depth'] + 1];
                    }
                }
            }

            $counts = $job->candidates()->selectRaw("count(*) as total, sum(status = 'ready_for_review') as ready, sum(status = 'needs_review') as review")->first();
            $job->forceFill([
                'status' => 'completed', 'products_found' => (int) $counts->total,
                'products_ready' => (int) $counts->ready, 'products_needing_review' => (int) $counts->review,
                'finished_at' => now(),
            ])->save();
        } catch (\Throwable $exception) {
            $job->forceFill(['status' => 'failed', 'error_message' => Str::limit($exception->getMessage(), 1000), 'finished_at' => now()])->save();
            throw $exception;
        }
    }

    public function approve(Vendor $vendor, SellerImportCandidate $candidate, array $data): SellerImportCandidate
    {
        abort_unless($candidate->job->vendor_id === $vendor->id, 404);
        $candidate->forceFill([
            'status' => 'approved',
            'review_notes' => $data['review_notes'] ?? $candidate->review_notes,
            'category_suggestion' => array_merge($candidate->category_suggestion ?: [], array_filter(['category_id' => $data['category_id'] ?? null])),
        ])->save();
        return $candidate->fresh();
    }

    public function publish(Vendor $vendor, SellerImportCandidate $candidate): \App\Models\Product
    {
        abort_unless($candidate->job->vendor_id === $vendor->id && $candidate->status === 'approved', 404);
        if ($candidate->duplicate_product_id) throw ValidationException::withMessages(['candidate' => 'This candidate matches an existing product.']);
        $payload = $candidate->payload ?: [];
        $categoryId = (int) data_get($candidate->category_suggestion, 'category_id', 0);
        abort_unless($categoryId && Category::query()->whereKey($categoryId)->where('is_master', true)->where('is_active', true)->exists(), 422);

        return DB::transaction(function () use ($candidate, $payload, $categoryId): \App\Models\Product {
            $product = \App\Models\Product::query()->create([
                'vendor_id' => $candidate->job->vendor_id, 'category_id' => $categoryId,
                'name' => Str::limit(strip_tags((string) ($payload['name'] ?? 'Imported product')), 180, ''),
                'slug' => Str::slug($payload['name'] ?? 'imported-product').'-'.Str::lower(Str::random(6)),
                'brand' => $payload['brand'] ?? null, 'short_description' => Str::limit(strip_tags((string) ($payload['description'] ?? '')), 500),
                'full_description' => strip_tags((string) ($payload['description'] ?? '')), 'mrp' => (int) ($payload['mrp'] ?? $payload['price'] ?? 0),
                'selling_price' => (int) ($payload['sale_price'] ?? $payload['price'] ?? 0), 'seller_status' => 'draft', 'is_published' => false,
                'product_condition' => 'new', 'package_contents' => 'Imported from seller website', 'needs_category_review' => false,
            ]);
            foreach (array_slice((array) ($payload['images'] ?? []), 0, 8) as $index => $imageUrl) {
                try {
                    $image = $this->fetchImage($this->safeUrl((string) $imageUrl, $candidate->job->domainImport->domain));
                    if ($image) $product->images()->create(['path' => $image['path'], 'label' => $index === 0 ? 'Primary View' : 'Imported Product View', 'sort_order' => $index]);
                } catch (\Throwable) {
                    // Invalid remote media is skipped; imported products never hotlink it.
                }
            }
            $candidate->forceFill(['status' => 'published', 'review_notes' => trim(($candidate->review_notes ?: '').' Imported as product #'.$product->id)])->save();
            return $product;
        });
    }

    private function safeUrl(string $url, string $domain): string
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (($parts['scheme'] ?? '') !== 'https' || ! hash_equals($domain, $host) || isset($parts['user'], $parts['pass'], $parts['port'])) {
            throw ValidationException::withMessages(['domain' => 'The importer blocked an unsafe URL.']);
        }
        $ips = array_merge(dns_get_record($host, DNS_A) ?: [], dns_get_record($host, DNS_AAAA) ?: []);
        foreach ($ips as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) throw ValidationException::withMessages(['domain' => 'The importer blocked a restricted network address.']);
        }
        return $url;
    }

    private function fetch(string $url, int $redirects = 0): string
    {
        if ($redirects > 3) throw ValidationException::withMessages(['domain' => 'The importer stopped after too many redirects.']);
        $response = Http::timeout(10)->connectTimeout(5)->withOptions(['allow_redirects' => false])->withHeaders(['User-Agent' => 'SushakoCatalogImporter/1.0'])->get($url);
        if ($response->redirect()) {
            $location = (string) $response->header('Location');
            if (str_starts_with($location, '/')) $location = 'https://'.parse_url($url, PHP_URL_HOST).$location;
            return $this->fetch($this->safeUrl($location, parse_url($url, PHP_URL_HOST)), $redirects + 1);
        }
        if (! $response->successful() || ! str_contains(strtolower((string) $response->header('Content-Type')), 'text/html')) return '';
        if (strlen($response->body()) > 2_000_000) throw ValidationException::withMessages(['domain' => 'The importer rejected an oversized page.']);
        return $response->body();
    }

    private function fetchImage(string $url): ?array
    {
        $response = Http::timeout(10)->connectTimeout(5)->withOptions(['allow_redirects' => false])->withHeaders(['User-Agent' => 'SushakoCatalogImporter/1.0'])->get($url);
        if (! $response->successful() || strlen($response->body()) > 5_000_000 || ! str_starts_with(strtolower((string) $response->header('Content-Type')), 'image/')) return null;
        $size = @getimagesizefromstring($response->body());
        if (! $size || ($size[0] ?? 0) > 8000 || ($size[1] ?? 0) > 8000) return null;
        $extension = match ($size['mime'] ?? '') { 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', default => 'jpg' };
        $path = 'seller-imports/'.Str::lower(Str::random(20)).'.'.$extension;
        Storage::disk('public')->put($path, $response->body());
        return ['path' => $path];
    }

    private function robots(string $domain): array
    {
        $response = Http::timeout(5)->connectTimeout(3)->withOptions(['allow_redirects' => false])->withHeaders(['User-Agent' => 'SushakoCatalogImporter/1.0'])->get('https://'.$domain.'/robots.txt');
        if (! $response->successful() || strlen($response->body()) > 200_000) return [];
        $rules = [];
        $active = false;
        foreach (preg_split('/\R/', $response->body()) as $line) {
            $line = trim(Str::before($line, '#'));
            if (stripos($line, 'user-agent:') === 0) $active = trim(strtolower(Str::after($line, ':'))) === '*' || trim(strtolower(Str::after($line, ':'))) === 'sushakocatalogimporter';
            elseif ($active && stripos($line, 'disallow:') === 0) { $path = trim(Str::after($line, ':')); if ($path !== '') $rules[] = $path; }
        }
        return $rules;
    }

    private function robotsAllows(array $rules, string $path): bool
    {
        foreach ($rules as $rule) if (str_starts_with($path, $rule)) return false;
        return true;
    }

    private function extractCandidates(string $html, string $url, string $domain): array
    {
        if ($html === '') return [];
        $crawler = new Crawler($html, $url);
        $items = [];
        foreach ($crawler->filter('script[type="application/ld+json"]') as $script) {
            $decoded = json_decode($script->textContent, true);
            $objects = is_array($decoded) && array_is_list($decoded) ? $decoded : [$decoded];
            foreach ($objects as $object) {
                if (! is_array($object) || ! in_array($object['@type'] ?? null, ['Product', 'product'], true)) continue;
                $offers = $object['offers'] ?? [];
                if (isset($offers[0])) $offers = $offers[0];
                $images = $object['image'] ?? [];
                $images = is_array($images) ? $images : [$images];
                $items[] = ['source_url' => $object['url'] ?? $url, 'name' => $object['name'] ?? null, 'description' => $object['description'] ?? '', 'brand' => is_array($object['brand'] ?? null) ? ($object['brand']['name'] ?? null) : ($object['brand'] ?? null), 'sku' => $object['sku'] ?? null, 'price' => $offers['price'] ?? null, 'availability' => $offers['availability'] ?? null, 'images' => array_values(array_filter($images, 'is_string'))];
            }
        }
        return $items;
    }

    private function links(string $html, string $base, string $domain): array
    {
        $crawler = new Crawler($html, $base);
        $links = [];
        foreach ($crawler->filter('a[href]') as $node) {
            $href = trim((string) $node->getAttribute('href'));
            $lowerHref = strtolower($href);
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($lowerHref, 'mailto:') || str_starts_with($lowerHref, 'tel:') || str_starts_with($lowerHref, 'javascript:')) continue;
            try {
                $absolute = (new Crawler('', $base))->link()->getUri();
            } catch (\Throwable) {
                $absolute = $href;
            }
            if (str_starts_with($href, '/')) $absolute = 'https://'.$domain.$href;
            elseif (! str_starts_with($href, 'http')) $absolute = 'https://'.$domain.'/'.ltrim($href, '/');
            $parts = parse_url($absolute);
            if (($parts['scheme'] ?? '') === 'https' && strtolower((string) ($parts['host'] ?? '')) === $domain && ! isset($links[$absolute])) $links[$absolute] = $absolute;
        }
        return array_values($links);
    }

    private function duplicateProduct(int $vendorId, array $payload): ?\App\Models\Product
    {
        return \App\Models\Product::query()->where('vendor_id', $vendorId)->where(function ($query) use ($payload): void {
            $query->where('name', $payload['name'] ?? '')->orWhere('slug', Str::slug($payload['name'] ?? ''));
        })->first();
    }

    private function suggestCategory(array $payload): array
    {
        $name = Str::lower(($payload['name'] ?? '').' '.($payload['description'] ?? ''));
        $category = Category::query()->where('is_master', true)->where('is_active', true)->where(function ($query) use ($name): void {
            foreach (preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) as $word) if (strlen($word) > 3) $query->orWhere('name', 'like', '%'.$word.'%');
        })->first();
        return ['category_id' => $category?->id, 'category_name' => $category?->name, 'confidence' => $category ? 0.8 : 0.1, 'reason' => $category ? 'Matched product text to an active master category.' : 'No confident master-category match; seller review required.'];
    }
}
