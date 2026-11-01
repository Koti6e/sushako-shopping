<?php

namespace App\Services\Catalogue\Adapters;

use App\Services\Catalogue\PlatformAdapter;

class ShopifyAdapter implements PlatformAdapter
{
    public function name(): string { return 'shopify'; }

    public function confidence(string $html): float
    {
        $html = strtolower($html);
        return min(1, (str_contains($html, 'cdn.shopify.com') ? 0.65 : 0) + (str_contains($html, 'shopify') ? 0.2 : 0) + (str_contains($html, 'shopify-section') ? 0.15 : 0));
    }
}
