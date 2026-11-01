<?php

namespace App\Services\Catalogue\Adapters;

use App\Services\Catalogue\PlatformAdapter;

class GenericAdapter implements PlatformAdapter
{
    public function name(): string { return 'generic'; }

    public function confidence(string $html): float { return 0.1; }
}
