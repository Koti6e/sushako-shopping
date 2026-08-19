<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Vendor;
use App\Support\ProductCatalog;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        return response("User-agent: *\nAllow: /\nSitemap: ".route('sitemap')."\n", 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('shop'), 'priority' => '0.9'],
        ]);

        Category::query()->where('is_active', true)->orderBy('updated_at')->get()->each(function (Category $category) use ($urls): void {
            $urls->push(['loc' => route('department.show', $category->slug), 'updated' => $category->updated_at, 'priority' => '0.8']);
        });

        ProductCatalog::products()->each(function (array $product) use ($urls): void {
            $urls->push(['loc' => route('products.show', $product['slug']), 'priority' => '0.8']);
        });

        Vendor::query()
            ->where('store_visibility', Vendor::VISIBILITY_PUBLISHED)
            ->where('store_status', Vendor::STORE_LIVE)
            ->orderByDesc('updated_at')
            ->get()
            ->each(function (Vendor $vendor) use ($urls): void {
                $urls->push(['loc' => route('stores.show', $vendor->slug), 'updated' => $vendor->updated_at, 'priority' => '0.7']);
            });

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.$this->xml((string) $url['loc'])."</loc>\n";

            if (! empty($url['updated'])) {
                $xml .= '        <lastmod>'.$url['updated']->toAtomString()."</lastmod>\n";
            }

            $xml .= '        <priority>'.$this->xml((string) $url['priority'])."</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
