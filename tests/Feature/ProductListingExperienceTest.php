<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

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
        $this->postJson(route('cart.store'), [
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
