<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::query()->where('slug', 'home-made-health-mix')->firstOrFail();
        $product = Product::query()->updateOrCreate([
            'slug' => 'sushako-razorpay-test-product',
        ], [
            'category_id' => $category->id,
            'name' => 'Sushako Staging Sample Product',
            'collection' => 'Payment Testing',
            'subcategory' => 'Staging Sample',
            'brand' => 'Sushako',
            'badge' => 'Featured',
            'short_description' => 'A one rupee staging product for checking cart, order creation and payment gateway flow before launch.',
            'full_description' => 'A focused Sushako product experience built around secure checkout.',
            'mrp' => 1,
            'selling_price' => 1,
            'rating' => 5.0,
            'reviews' => 1,
            'is_published' => true,
        ]);

        foreach ([
            ['path' => 'assets/banners/home-made-health-mix.jpg', 'label' => 'Primary View'],
            ['path' => 'assets/banners/masala-powders.jpg', 'label' => 'Ingredient View'],
            ['path' => 'assets/banners/womens-clothing.jpg', 'label' => 'Store View'],
        ] as $index => $image) {
            $product->images()->updateOrCreate([
                'path' => $image['path'],
            ], [
                'label' => $image['label'],
                'sort_order' => $index + 1,
            ]);
        }

        $product->variants()->updateOrCreate([
            'sku' => 'SS-STAGE-001',
        ], [
            'colour' => 'Standard',
            'colour_hex' => '#d8ccb9',
            'size' => 'Standard',
            'stock' => 99,
        ]);
    }

    public function test_product_detail_uses_premium_carousel_gallery(): void
    {
        $this->get(route('products.show', 'sushako-razorpay-test-product'))
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
}
