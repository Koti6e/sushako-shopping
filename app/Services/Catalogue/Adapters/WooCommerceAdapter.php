<?php

namespace App\Services\Catalogue\Adapters;

use App\Services\Catalogue\PlatformAdapter;

class WooCommerceAdapter implements PlatformAdapter
{
    public function name(): string { return 'woocommerce'; }

    public function confidence(string $html): float
    {
        $html = strtolower($html);
        $signals = ['woocommerce' => 0.55, 'wp-content' => 0.2, 'wc-block' => 0.2, 'wc/store' => 0.15];
        return min(1, array_sum(array_map(fn ($signal, $weight) => str_contains($html, $signal) ? $weight : 0, array_keys($signals), $signals)));
    }
}
