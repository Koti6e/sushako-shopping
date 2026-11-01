<?php

namespace App\Services\Catalogue;

use App\Services\Catalogue\Adapters\GenericAdapter;
use App\Services\Catalogue\Adapters\LaravelAdapter;
use App\Services\Catalogue\Adapters\MagentoAdapter;
use App\Services\Catalogue\Adapters\ShopifyAdapter;
use App\Services\Catalogue\Adapters\WooCommerceAdapter;

class PlatformAdapterResolver
{
    /** @return list<PlatformAdapter> */
    public function adapters(): array
    {
        return [new WooCommerceAdapter, new ShopifyAdapter, new MagentoAdapter, new LaravelAdapter, new GenericAdapter];
    }

    public function resolve(string $html): PlatformAdapter
    {
        return collect($this->adapters())->sortByDesc(fn (PlatformAdapter $adapter) => $adapter->confidence($html))->first();
    }
}
