<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_homepage_uses_marketplace_hero_and_live_products(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Shop Local. Shop Smart. Shop Sushako.')
            ->assertSee('Shop by Category')
            ->assertDontSee('Sushako Storefront Test Product')
            ->assertDontSee('Test Products')
            ->assertDontSee('Marketplace content is being prepared.');
    }

    public function test_deals_and_stores_pages_use_public_marketplace_data(): void
    {
        $this->get(route('deals'))
            ->assertOk()
            ->assertSee('Deals')
            ->assertSee('Lenovo 100e Celeron Laptop');

        $this->get(route('stores.index'))
            ->assertOk()
            ->assertSee('Marketplace Stores');
    }

    public function test_products_listing_has_sidebar_filters_sorting_and_buy_now_actions(): void
    {
        $this->get(route('products.index', [
            'sort' => 'name-a-z',
            'stock' => 'in-stock',
        ]))
            ->assertOk()
            ->assertSee('Shop by Category')
            ->assertSee('Price: Low to High')
            ->assertSee('Name: A to Z')
            ->assertSee('In Stock')
            ->assertSee('Buy Now')
            ->assertSee(route('cart.buy-now'), false)
            ->assertSee('data-buy-now-form', false)
            ->assertSee('product-action-icon--bag', false)
            ->assertSee('data-add-to-cart-label', false)
            ->assertSee('data-buy-now-label', false)
            ->assertDontSee('fa-solid fa-plus', false)
            ->assertDontSee('Discover Next');
    }

    public function test_product_card_add_to_bag_uses_real_cart_endpoint(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->postJson(route('cart.store'), [
                'slug' => 'lenovo-100e-celeron-laptop',
                'colour' => 'Standard',
                'size' => '500GB HDD',
                'quantity' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Added to Cart')
            ->assertJsonPath('cart_count', 1);

        $this->assertNotEmpty(session('cart', []));
    }
}
