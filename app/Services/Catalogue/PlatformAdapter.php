<?php

namespace App\Services\Catalogue;

interface PlatformAdapter
{
    public function name(): string;

    public function confidence(string $html): float;
}
