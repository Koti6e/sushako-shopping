<?php

namespace Tests\Unit;

use App\Services\Catalogue\PlatformAdapterResolver;
use Tests\TestCase;

class CataloguePlatformAdapterTest extends TestCase
{
    public function test_platform_resolution_uses_multiple_signals_and_falls_back_to_generic(): void
    {
        $resolver = app(PlatformAdapterResolver::class);

        $this->assertSame('woocommerce', $resolver->resolve('<link href="/wp-content/plugins/woocommerce/assets/wc-block.css"><body>woocommerce</body>')->name());
        $this->assertSame('shopify', $resolver->resolve('<script src="https://cdn.shopify.com/shopifycloud/shop.js"></script><div class="shopify-section"></div>')->name());
        $this->assertSame('generic', $resolver->resolve('<html><body><h1>Store</h1></body></html>')->name());
    }
}
