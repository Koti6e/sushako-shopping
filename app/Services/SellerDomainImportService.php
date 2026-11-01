<?php

namespace App\Services;

use App\Jobs\CrawlSellerDomain;
use App\Models\Category;
use App\Models\CatalogueDiscoveredCategory;
use App\Models\CatalogueDiscoverySession;
use App\Models\CatalogueIntelligenceProfile;
use App\Models\CatalogueExtractedProduct;
use App\Models\CatalogueDiscoveredUrl;
use App\Models\CatalogueProductMatch;
use App\Models\CatalogueImportBatch;
use App\Models\CatalogueImportItem;
use App\Models\CatalogueImageAsset;
use App\Models\CatalogueEnrichmentSuggestion;
use App\Models\CatalogueMonitoringRun;
use App\Models\SellerDomainImport;
use App\Models\SellerImportCandidate;
use App\Models\SellerImportJob;
use App\Models\Vendor;
use App\Services\Catalogue\PlatformAdapterResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\DomCrawler\Crawler;

class SellerDomainImportService
{
    public function selectCategories(Vendor $vendor, CatalogueDiscoverySession $session, array $selected, array $excluded = []): void
    {
        abort_unless($session->vendor_id === $vendor->id, 404);
        if (array_intersect($selected, $excluded) !== []) throw ValidationException::withMessages(['categories' => 'A category cannot be selected and excluded at the same time.']);
        $allowed = $session->categories()->whereIn('id', array_unique([...$selected, ...$excluded]))->pluck('id')->all();
        abort_unless(count($allowed) === count(array_unique([...$selected, ...$excluded])), 422);
        $session->categories()->update(['selected' => false, 'excluded' => false]);
        $session->categories()->whereIn('id', $selected)->update(['selected' => true, 'excluded' => false]);
        $session->categories()->whereIn('id', $excluded)->update(['selected' => false, 'excluded' => true]);
        $session->forceFill(['options' => [...($session->options ?: []), 'selection_saved_at' => now()->toIso8601String()]])->save();
    }

    public function decideMatch(Vendor $vendor, CatalogueProductMatch $match, string $decision, ?string $notes = null): CatalogueProductMatch
    {
        abort_unless($match->vendor_id === $vendor->id, 404);
        abort_unless(in_array($decision, ['approved', 'rejected', 'held', 'pending'], true), 422);
        return tap($match)->forceFill(['decision' => $decision, 'review_notes' => $notes ?: $match->review_notes])->save();
    }

    public function importMatch(Vendor $vendor, CatalogueProductMatch $match, string $action, ?int $categoryId = null): \App\Models\Product
    {
        abort_unless($match->vendor_id === $vendor->id, 404);
        abort_unless($match->decision === 'approved', 422);
        if (! in_array($action, ['import_new', 'update_price'], true)) throw ValidationException::withMessages(['action' => 'This import action is not available yet.']);
        $extracted = $match->extractedProduct;
        $payload = $extracted->payload ?: [];
        if ($action === 'update_price') {
            abort_unless($match->product_id, 422);
            if ($extracted->price === null) throw ValidationException::withMessages(['price' => 'The source did not provide a valid price.']);
            $product = DB::transaction(function () use ($match, $extracted): \App\Models\Product {
                $product = $match->product()->lockForUpdate()->firstOrFail();
                $product->forceFill(['selling_price' => (int) round((float) $extracted->price)])->save();
                return $product;
            });
        } else {
            abort_unless($categoryId && Category::query()->whereKey($categoryId)->where('is_master', true)->where('is_active', true)->exists(), 422);
            $existing = \App\Models\Product::query()->where('vendor_id', $vendor->id)->where('name', $extracted->name)->first();
            if ($existing) throw ValidationException::withMessages(['product' => 'A seller product with this name already exists. Review the possible match instead.']);
            $product = DB::transaction(fn (): \App\Models\Product => \App\Models\Product::query()->create([
                'vendor_id' => $vendor->id,
                'category_id' => $categoryId,
                'name' => Str::limit(strip_tags((string) $extracted->name), 180, ''),
                'slug' => Str::slug($extracted->name ?: 'imported-product').'-'.Str::lower(Str::random(6)),
                'brand' => $extracted->brand,
                'short_description' => Str::limit(strip_tags((string) data_get($payload, 'description', '')), 500),
                'full_description' => strip_tags((string) data_get($payload, 'description', '')),
                'mrp' => (int) round((float) ($extracted->price ?? 0)),
                'selling_price' => (int) round((float) ($extracted->price ?? 0)),
                'seller_status' => \App\Models\Product::SELLER_STATUS_DRAFT,
                'is_published' => false,
                'product_condition' => 'new',
                'package_contents' => 'Imported from seller website',
            ]));
        }
        $batch = CatalogueImportBatch::query()->create(['vendor_id' => $vendor->id, 'catalogue_discovery_session_id' => $extracted->catalogue_discovery_session_id, 'status' => 'completed', 'selected_count' => 1, 'imported_count' => 1]);
        CatalogueImportItem::query()->create(['catalogue_import_batch_id' => $batch->id, 'catalogue_product_match_id' => $match->id, 'action' => $action, 'status' => 'imported', 'decided_at' => now()]);
        $match->forceFill(['product_id' => $product->id, 'decision' => 'imported'])->save();
        return $product;
    }

    public function suggestEnrichment(Vendor $vendor, CatalogueExtractedProduct $product): CatalogueEnrichmentSuggestion
    {
        abort_unless($product->session?->vendor_id === $vendor->id, 404);
        $payload = $product->payload ?: [];
        $fields = array_filter([
            'brand' => $product->brand ?: null,
            'seo_title' => $product->name ? Str::limit($product->name.' | Sushako Shopping', 60, '') : null,
            'seo_description' => data_get($payload, 'description') ? Str::limit(strip_tags((string) data_get($payload, 'description')), 160) : null,
            'keywords' => $product->name ? preg_split('/\s+/', Str::lower($product->name), -1, PREG_SPLIT_NO_EMPTY) : [],
        ], fn ($value) => $value !== null && $value !== []);
        return CatalogueEnrichmentSuggestion::query()->updateOrCreate(['catalogue_extracted_product_id' => $product->id], ['fields' => $fields, 'generated_by' => 'rules', 'status' => 'pending', 'approved_at' => null]);
    }

    public function approveEnrichment(Vendor $vendor, CatalogueEnrichmentSuggestion $suggestion): CatalogueEnrichmentSuggestion
    {
        abort_unless($suggestion->product?->session?->vendor_id === $vendor->id, 404);
        $suggestion->forceFill(['status' => 'approved', 'approved_at' => now()])->save();
        return $suggestion->fresh();
    }

    public function processImage(Vendor $vendor, CatalogueExtractedProduct $product, int $index = 0, array $settings = []): CatalogueImageAsset
    {
        abort_unless($product->session?->vendor_id === $vendor->id, 404);
        $source = data_get($product->images, $index);
        if (! is_string($source)) throw ValidationException::withMessages(['image' => 'No source image was found.']);
        $domain = $product->session->source->domain;
        $url = $this->safeUrl($source, $domain);
        $response = Http::timeout(10)->connectTimeout(5)->withOptions(['allow_redirects' => false])->get($url);
        $body = $response->body();
        if (! $response->successful() || strlen($body) > 5_000_000 || ! str_starts_with(strtolower((string) $response->header('Content-Type')), 'image/')) throw ValidationException::withMessages(['image' => 'The source image failed validation.']);
        $original = @getimagesizefromstring($body);
        if (! $original || ($original[0] ?? 0) > 8000 || ($original[1] ?? 0) > 8000 || ! function_exists('imagecreatefromstring')) throw ValidationException::withMessages(['image' => 'The source image is invalid or image processing is unavailable.']);
        $originalPath = 'catalogue-originals/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($originalPath, $body);
        $image = @imagecreatefromstring($body);
        $max = min(2400, max(400, (int) ($settings['max_dimension'] ?? 1200)));
        $scale = min(1, $max / max(imagesx($image), imagesy($image)));
        $canvas = imagecreatetruecolor(max(1, (int) round(imagesx($image) * $scale)), max(1, (int) round(imagesy($image) * $scale)));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, imagesx($canvas), imagesy($canvas), imagesx($image), imagesy($image));
        ob_start(); imagejpeg($canvas, null, min(95, max(40, (int) ($settings['quality'] ?? 82)))); $processedBody = ob_get_clean();
        imagedestroy($canvas); imagedestroy($image);
        $processedPath = 'catalogue-processed/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($processedPath, $processedBody);
        return CatalogueImageAsset::query()->updateOrCreate(['catalogue_extracted_product_id' => $product->id, 'sort_order' => $index], ['original_path' => $originalPath, 'processed_path' => $processedPath, 'original_hash' => hash('sha256', $body), 'processed_hash' => hash('sha256', $processedBody), 'settings' => $settings, 'status' => 'processed']);
    }

    public function saveMonitoring(Vendor $vendor, SellerDomainImport $source, array $data): \App\Models\CatalogueMonitoringSetting
    {
        abort_unless($source->vendor_id === $vendor->id, 404);
        return \App\Models\CatalogueMonitoringSetting::query()->updateOrCreate(['vendor_id' => $vendor->id, 'seller_domain_import_id' => $source->id], array_merge($data, ['auto_publish' => false]));
    }
    public function analyse(Vendor $vendor, SellerDomainImport $import): CatalogueIntelligenceProfile
    {
        abort_unless($import->vendor_id === $vendor->id, 404);
        if ($import->status !== 'verified') {
            throw ValidationException::withMessages(['domain' => 'Verify ownership of this source before running website analysis.']);
        }

        $profile = CatalogueIntelligenceProfile::query()->firstOrCreate(
            ['seller_domain_import_id' => $import->id],
            ['profile_version' => '2.0'],
        );

        try {
            $html = $this->fetch('https://'.$import->domain.'/');
            if ($html === '') {
                throw ValidationException::withMessages(['domain' => 'The website did not return a readable HTML page.']);
            }

            $sitemaps = $this->discoverSitemaps($import->domain, $html);
            $platform = $this->detectPlatform($html);
            $currency = $this->detectCurrency($html);
            $categories = $this->discoverCategoryLinks($html, $import->domain);
            $productUrls = $this->discoverProductUrlsFromSitemaps($sitemaps, $import->domain);
            $capabilities = [
                'sitemap' => $productUrls !== [],
                'category_navigation' => $categories !== [],
                'schema_product' => str_contains($html, 'application/ld+json'),
                'opengraph' => str_contains($html, 'property="og:'),
                'images' => true,
                'variants' => $platform !== 'generic',
            ];

            $session = CatalogueDiscoverySession::query()->create([
                'vendor_id' => $vendor->id,
                'seller_domain_import_id' => $import->id,
                'status' => 'analysed',
                'urls_discovered' => count($productUrls),
                'unique_urls' => count(array_unique($productUrls)),
                'categories_discovered' => count($categories),
                'options' => ['sitemaps' => $sitemaps],
                'started_at' => now(),
                'finished_at' => now(),
            ]);

            foreach ($categories as $category) {
                CatalogueDiscoveredCategory::query()->create([
                    'catalogue_discovery_session_id' => $session->id,
                    'name' => $category['name'],
                    'canonical_url' => $category['url'],
                ]);
            }

            $profile->forceFill([
                'platform' => $platform,
                'status' => 'ready',
                'is_accessible' => true,
                'currency' => $currency,
                'category_count' => count($categories),
                'product_url_count' => count(array_unique($productUrls)),
                'capabilities' => $capabilities,
                'strategy' => ['priority' => ['schema.org', 'platform_feed', 'opengraph', 'html'], 'sitemaps' => $sitemaps],
                'limitations' => $productUrls === [] ? ['No public product sitemap or product URLs were identified on the landing page.'] : [],
                'analysed_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ])->save();

            return $profile->fresh();
        } catch (\Throwable $exception) {
            $profile->forceFill([
                'status' => 'failed',
                'is_accessible' => false,
                'failed_at' => now(),
                'error_message' => Str::limit($exception->getMessage(), 1000),
            ])->save();

            throw $exception;
        }
    }

    public function matchProduct(Vendor $vendor, CatalogueExtractedProduct $extracted): CatalogueProductMatch
    {
        abort_unless($extracted->session?->vendor_id === $vendor->id, 404);

        $query = \App\Models\Product::query()->where('vendor_id', $vendor->id);
        $evidence = [];
        $product = null;
        if ($extracted->sku) {
            // Product SKU is not a universal field in the legacy catalogue, so
            // only use it when the source also supplies a strong name signal.
            $product = $query->where('name', $extracted->name)->first();
            if ($product) $evidence[] = 'sku_and_name';
        }
        if (! $product && $extracted->name && $extracted->brand) {
            $product = (clone $query)->where('name', $extracted->name)->where('brand', $extracted->brand)->first();
            if ($product) $evidence[] = 'brand_and_name';
        }

        $changes = [];
        if ($product) {
            if ($extracted->price !== null && (float) $product->selling_price !== (float) $extracted->price) $changes['price'] = ['old' => (float) $product->selling_price, 'new' => (float) $extracted->price];
            if ($extracted->brand && (string) $product->brand !== (string) $extracted->brand) $changes['brand'] = ['old' => $product->brand, 'new' => $extracted->brand];
        }
        $outcome = $product ? (count($evidence) > 0 && $extracted->sku ? 'exact_match' : 'possible_match') : ($extracted->name ? 'new_product' : 'insufficient_information');
        $confidence = $outcome === 'exact_match' ? 0.92 : ($outcome === 'possible_match' ? 0.72 : 0);
        $decision = $product && $changes === [] ? 'skipped_unchanged' : 'pending';

        return CatalogueProductMatch::query()->updateOrCreate(
            ['vendor_id' => $vendor->id, 'catalogue_extracted_product_id' => $extracted->id],
            ['product_id' => $product?->id, 'outcome' => $outcome, 'confidence' => $confidence, 'evidence' => $evidence, 'changes' => $changes, 'decision' => $decision],
        );
    }

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
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || isset($parts['query']) || isset($parts['fragment'])
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
        if ($import->intelligenceProfile?->status !== 'ready') {
            throw ValidationException::withMessages(['domain' => 'Run and review the website intelligence report before starting discovery.']);
        }

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
        $session = CatalogueDiscoverySession::query()->create([
            'vendor_id' => $job->vendor_id,
            'seller_domain_import_id' => $import->id,
            'status' => 'running',
            'started_at' => now(),
        ]);
        $job->forceFill(['status' => 'running', 'started_at' => now(), 'error_message' => null])->save();

        try {
            $robots = $this->robots($import->domain);
            $queue = [['url' => 'https://'.$import->domain.'/', 'depth' => 0]];
            $seen = [];
            $pages = 0;

            $pageLimit = max(1, (int) $import->page_limit);
            while ($queue !== [] && $pages < $pageLimit) {
                $current = array_shift($queue);
                $url = $this->safeUrl($current['url'], $import->domain);
                if (! $this->robotsAllows($robots, parse_url($url, PHP_URL_PATH) ?: '/')) continue;
                if (isset($seen[$url])) continue;
                $seen[$url] = true;
                $html = $this->fetch($url);
                $pages++;
                $job->increment('pages_found');
                $session->forceFill([
                    'pages_scanned' => $pages,
                    'progress' => ['pages_scanned' => $pages, 'queue_remaining' => count($queue), 'last_url' => $url],
                ])->save();

                foreach ($this->extractCandidates($html, $url, $import->domain) as $payload) {
                    try {
                        $payload['source_url'] = $this->safeUrl((string) ($payload['source_url'] ?? $url), $import->domain);
                    } catch (\Throwable) {
                        continue;
                    }
                    $hash = hash('sha256', $payload['source_url']);
                    $discoveredUrl = CatalogueDiscoveredUrl::query()->updateOrCreate(
                        ['catalogue_discovery_session_id' => $session->id, 'source_hash' => $hash],
                        ['canonical_url' => $payload['source_url'], 'status' => 'extracted'],
                    );
                    $extracted = CatalogueExtractedProduct::query()->updateOrCreate(
                        ['catalogue_discovery_session_id' => $session->id, 'identity_hash' => hash('sha256', Str::lower(($payload['sku'] ?? '').'|'.($payload['name'] ?? '').'|'.$payload['source_url']))],
                        [
                            'catalogue_discovered_url_id' => $discoveredUrl->id,
                            'canonical_url' => $payload['source_url'],
                            'name' => $payload['name'] ?? null,
                            'sku' => $payload['sku'] ?? null,
                            'gtin' => $payload['gtin'] ?? null,
                            'mpn' => $payload['mpn'] ?? null,
                            'brand' => $payload['brand'] ?? null,
                            'price' => is_numeric($payload['price'] ?? null) ? $payload['price'] : null,
                            'currency' => $payload['currency'] ?? null,
                            'availability' => $payload['availability'] ?? null,
                            'payload' => $payload,
                            'images' => $payload['images'] ?? [],
                            'extracted_at' => now(),
                            'status' => 'extracted',
                        ],
                    );
                    $this->matchProduct($job->vendor, $extracted);
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
                        if (count($queue) + $pages >= $pageLimit) break;
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
            $session->forceFill([
                'status' => 'completed',
                'pages_scanned' => $pages,
                'urls_discovered' => $session->urls()->count(),
                'unique_urls' => $session->urls()->distinct('canonical_url')->count('canonical_url'),
                'categories_discovered' => $session->categories()->count(),
                'eligible_products' => $session->products()->whereIn('status', ['extracted', 'matched'])->count(),
                'finished_at' => now(),
            ])->save();
        } catch (\Throwable $exception) {
            $job->forceFill(['status' => 'failed', 'error_message' => Str::limit($exception->getMessage(), 1000), 'finished_at' => now()])->save();
            $session->forceFill(['status' => 'failed', 'error_message' => Str::limit($exception->getMessage(), 1000), 'finished_at' => now()])->save();
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
            $candidate->forceFill(['status' => 'imported_draft', 'review_notes' => trim(($candidate->review_notes ?: '').' Imported as seller draft #'.$product->id)])->save();
            return $product;
        });
    }

    private function safeUrl(string $url, string $domain): string
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (($parts['scheme'] ?? '') !== 'https' || ! hash_equals($domain, $host) || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            throw ValidationException::withMessages(['domain' => 'The importer blocked an unsafe URL.']);
        }
        $ips = array_merge(dns_get_record($host, DNS_A) ?: [], dns_get_record($host, DNS_AAAA) ?: []);
        foreach ($ips as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) throw ValidationException::withMessages(['domain' => 'The importer blocked a restricted network address.']);
        }
        return $url;
    }

    private function detectPlatform(string $html): string
    {
        return app(PlatformAdapterResolver::class)->resolve($html)->name();
    }

    private function detectCurrency(string $html): ?string
    {
        if (preg_match('/(?:currency|priceCurrency)[^A-Z]{0,20}([A-Z]{3})/i', $html, $matches)) return strtoupper($matches[1]);
        if (str_contains($html, '₹') || str_contains(Str::lower($html), 'inr')) return 'INR';
        if (str_contains($html, '$')) return 'USD';
        return null;
    }

    private function discoverSitemaps(string $domain, string $html): array
    {
        $sitemaps = ['https://'.$domain.'/sitemap.xml', 'https://'.$domain.'/sitemap_index.xml'];
        foreach (['sitemap', 'sitemap_index', 'product-sitemap'] as $token) {
            if (str_contains(Str::lower($html), $token)) $sitemaps[] = 'https://'.$domain.'/'.$token.'.xml';
        }
        return array_values(array_unique(array_filter($sitemaps)));
    }

    private function discoverProductUrlsFromSitemaps(array $sitemaps, string $domain): array
    {
        $urls = [];
        foreach ($sitemaps as $sitemap) {
            try {
                $xml = $this->fetchXml($this->safeUrl($sitemap, $domain));
                foreach ($xml as $node) {
                    $loc = trim((string) ($node->loc ?? ''));
                    if ($loc !== '' && preg_match('/product|item|shop|catalog|p\//i', $loc)) $urls[] = $loc;
                }
            } catch (\Throwable) {
                continue;
            }
        }
        return array_values(array_unique($urls));
    }

    private function discoverCategoryLinks(string $html, string $domain): array
    {
        $crawler = new Crawler($html, 'https://'.$domain.'/');
        $categories = [];
        foreach ($crawler->filter('a[href]') as $node) {
            $name = trim(preg_replace('/\s+/', ' ', $node->textContent));
            $href = trim($node->getAttribute('href'));
            if ($name === '' || strlen($name) > 180 || ! preg_match('/category|collection|shop|catalog|product/i', $href.' '.$name)) continue;
            $url = str_starts_with($href, '/') ? 'https://'.$domain.$href : $href;
            try { $url = $this->safeUrl($url, $domain); } catch (\Throwable) { continue; }
            $categories[$url] = ['name' => $name, 'url' => $url];
        }
        return array_values($categories);
    }

    private function fetchXml(string $url): \SimpleXMLElement
    {
        $response = Http::timeout(10)->connectTimeout(5)->withOptions(['allow_redirects' => false])->get($url);
        if (! $response->successful() || strlen($response->body()) > 5_000_000) throw new \RuntimeException('Sitemap unavailable or oversized.');
        $xml = @simplexml_load_string($response->body());
        if (! $xml) throw new \RuntimeException('Malformed sitemap.');
        return $xml;
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
            $objects = $this->jsonLdObjects($decoded);
            foreach ($objects as $object) {
                if (! is_array($object) || ! $this->isProductSchema($object)) continue;
                $offers = $object['offers'] ?? [];
                if (isset($offers[0])) $offers = $offers[0];
                $images = $object['image'] ?? [];
                $images = is_array($images) ? $images : [$images];
                $items[] = $this->normalisePayload([
                    'source_url' => $object['url'] ?? $url,
                    'name' => $object['name'] ?? null,
                    'description' => $object['description'] ?? '',
                    'brand' => is_array($object['brand'] ?? null) ? ($object['brand']['name'] ?? null) : ($object['brand'] ?? null),
                    'sku' => $object['sku'] ?? null,
                    'gtin' => $object['gtin13'] ?? $object['gtin'] ?? null,
                    'mpn' => $object['mpn'] ?? null,
                    'price' => $offers['price'] ?? null,
                    'currency' => $offers['priceCurrency'] ?? null,
                    'availability' => $offers['availability'] ?? null,
                    'images' => array_values(array_filter($images, 'is_string')),
                ]);
            }
        }
        if ($items !== []) return $items;

        $meta = [];
        foreach ($crawler->filter('meta[property], meta[name]') as $node) {
            $key = strtolower(trim($node->getAttribute('property') ?: $node->getAttribute('name')));
            $meta[$key] = trim($node->getAttribute('content'));
        }
        $name = $meta['og:title'] ?? $meta['twitter:title'] ?? ($crawler->filter('h1')->count() ? trim($crawler->filter('h1')->first()->text()) : null);
        if (! $name) return [];

        $image = $meta['og:image'] ?? null;
        $price = $meta['product:price:amount'] ?? $meta['og:price:amount'] ?? null;
        return [$this->normalisePayload([
            'source_url' => $meta['og:url'] ?? $url,
            'name' => $name,
            'description' => $meta['og:description'] ?? ($crawler->filter('meta[name="description"]')->count() ? $crawler->filter('meta[name="description"]')->attr('content') : ''),
            'brand' => $meta['product:brand'] ?? null,
            'sku' => $meta['product:sku'] ?? null,
            'price' => $price,
            'currency' => $meta['product:price:currency'] ?? null,
            'images' => $image ? [$image] : [],
        ])];
    }

    private function jsonLdObjects(mixed $decoded): array
    {
        if (! is_array($decoded)) return [];
        $objects = array_is_list($decoded) ? $decoded : [$decoded];
        $flattened = [];
        foreach ($objects as $object) {
            if (! is_array($object)) continue;
            if (isset($object['@graph']) && is_array($object['@graph'])) $flattened = [...$flattened, ...$object['@graph']];
            else $flattened[] = $object;
        }
        return $flattened;
    }

    private function isProductSchema(array $object): bool
    {
        $types = is_array($object['@type'] ?? null) ? $object['@type'] : [$object['@type'] ?? null];
        return (bool) array_filter($types, fn ($type) => strtolower((string) $type) === 'product');
    }

    private function normalisePayload(array $payload): array
    {
        $payload['name'] = trim(strip_tags((string) ($payload['name'] ?? '')));
        $payload['description'] = trim(strip_tags((string) ($payload['description'] ?? '')));
        $payload['price'] = $this->normalisePrice($payload['price'] ?? null);
        $payload['images'] = array_values(array_unique(array_filter(array_map('strval', (array) ($payload['images'] ?? [])), fn ($image) => filter_var($image, FILTER_VALIDATE_URL))));
        $payload['currency'] = $payload['currency'] ? strtoupper(trim((string) $payload['currency'])) : null;
        return $payload;
    }

    private function normalisePrice(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;
        $value = preg_replace('/[^0-9.,-]/', '', (string) $value);
        if ($value === '') return null;
        if (str_contains($value, ',') && str_contains($value, '.')) $value = str_replace(',', '', $value);
        elseif (str_contains($value, ',')) $value = str_replace(',', '.', $value);
        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    private function links(string $html, string $base, string $domain): array
    {
        $crawler = new Crawler($html, $base);
        $links = [];
        foreach ($crawler->filter('a[href]') as $node) {
            $href = trim((string) $node->getAttribute('href'));
            $lowerHref = strtolower($href);
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($lowerHref, 'mailto:') || str_starts_with($lowerHref, 'tel:') || str_starts_with($lowerHref, 'javascript:')) continue;
            if (str_starts_with($lowerHref, 'https://')) $absolute = $href;
            elseif (str_starts_with($href, '//')) $absolute = 'https:'.$href;
            elseif (str_starts_with($href, '/')) $absolute = 'https://'.$domain.$href;
            else $absolute = rtrim(dirname($base), '/').'/'.ltrim($href, '/');
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
