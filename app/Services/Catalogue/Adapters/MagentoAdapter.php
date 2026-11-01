<?php

namespace App\Services\Catalogue\Adapters;

use App\Services\Catalogue\PlatformAdapter;

class MagentoAdapter implements PlatformAdapter
{
    public function name(): string { return 'magento'; }

    public function confidence(string $html): float
    {
        $html = strtolower($html);
        return min(1, (str_contains($html, 'mage-cache') ? 0.55 : 0) + (str_contains($html, 'magento') ? 0.35 : 0) + (str_contains($html, 'requirejs-config') ? 0.1 : 0));
    }
}
