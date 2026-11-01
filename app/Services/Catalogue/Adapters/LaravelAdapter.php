<?php

namespace App\Services\Catalogue\Adapters;

use App\Services\Catalogue\PlatformAdapter;

class LaravelAdapter implements PlatformAdapter
{
    public function name(): string { return 'laravel'; }

    public function confidence(string $html): float
    {
        $html = strtolower($html);
        return min(1, (str_contains($html, 'laravel_session') ? 0.7 : 0) + (str_contains($html, 'laravel') ? 0.2 : 0) + (str_contains($html, 'csrf-token') ? 0.1 : 0));
    }
}
