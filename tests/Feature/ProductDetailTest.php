<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_product_detail_uses_premium_carousel_gallery(): void
    {
        $this->get(route('products.show', 'lenovo-100e-celeron-laptop'))
            ->assertOk()
            ->assertSee('data-product-gallery', false)
            ->assertSee('data-product-gallery-next', false)
            ->assertSee('data-product-gallery-thumb', false)
            ->assertSee('product-gallery__stage')
            ->assertSee('assets/payments/razorpay.svg')
            ->assertSee('WhatsApp support')
            ->assertSee('Track order')
            ->assertDontSee('<legend>Size</legend>', false)
            ->assertDontSee('<legend>Colour</legend>', false);
    }

    public function test_staging_product_is_not_publicly_visible(): void
    {
        $this->get(route('products.show', 'sushako-razorpay-test-product'))->assertNotFound();
    }

    public function test_laptop_product_uses_storage_options_with_variant_pricing(): void
    {
        $this->get(route('products.show', 'lenovo-100e-celeron-laptop'))
            ->assertOk()
            ->assertSee('Lenovo 100e Celeron Laptop')
            ->assertSee('Budgeted Laptop')
            ->assertSee('<legend>Storage</legend>', false)
            ->assertSee('500GB HDD')
            ->assertSee('data-option-price="9000"', false)
            ->assertSee('500GB SSD')
            ->assertSee('data-option-price="11000"', false)
            ->assertSee('assets/products/lenovo-100e/lenovo-100e-05.jpg')
            ->assertDontSee('<legend>Size</legend>', false);
    }

    public function test_zero_stock_product_remains_visible_but_cannot_be_purchased(): void
    {
        $product = Product::query()->where('slug', 'lenovo-100e-celeron-laptop')->firstOrFail();
        ProductVariant::query()->where('product_id', $product->id)->update(['stock' => 0]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Out of Stock')
            ->assertSee('purchasing will reopen')
            ->assertSee('<button class="button button--primary" type="button" disabled>Out of Stock</button>', false)
            ->assertSee('Wishlist')
            ->assertSee('Share');

        $this->post(route('cart.store'), [
            'slug' => $product->slug,
            'colour' => 'Standard',
            'size' => '500GB HDD',
            'quantity' => 1,
        ])->assertSessionHasErrors('quantity');
    }
}
