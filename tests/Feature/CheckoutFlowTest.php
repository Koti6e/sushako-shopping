<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCheckoutProduct();
    }

    public function test_guest_users_are_protected_from_unowned_order_routes(): void
    {
        $order = $this->orderFor($this->customer(), [
            'status' => 'placed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        $this->post(route('checkout.place'))->assertRedirect(route('cart.empty'));
        $this->get(route('order.payment', $order->order_number))->assertNotFound();
        $this->get(route('order.success', $order->order_number))->assertNotFound();
        $this->get(route('order.invoice', $order->order_number))->assertNotFound();
        $this->get(route('orders.track'))->assertOk()->assertSee('Track your Sushako order');
    }

    public function test_authenticated_customer_can_checkout_and_place_own_order(): void
    {
        config(['services.razorpay.key' => 'YOUR_TEST_KEY_ID']);
        $customer = $this->customer([
            'name' => 'Koti Customer',
            'email' => 'koti@example.test',
            'phone' => '9876543210',
        ]);

        $this->actingAs($customer)->post(route('cart.store'), [
            'slug' => 'sushako-checkout-product',
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $this->actingAs($customer)->get(route('checkout'))
            ->assertOk()
            ->assertSee('Guest checkout in a few steps')
            ->assertSee('Mobile Number')
            ->assertSee('Address')
            ->assertSee('assets/payments/razorpay.svg')
            ->assertSee('WhatsApp support')
            ->assertSee('Dispatch and delivery tracking');

        $checkoutResponse = $this->actingAs($customer)->post(route('checkout.place'), [
            'customer_name' => 'Koti Customer',
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'address_line_1' => 'Door 12, First Street',
            'address_line_2' => 'Apartment 4B',
            'city' => 'Chennai',
            'pincode' => '600001',
            'landmark' => 'Near Central',
            'delivery_location_url' => 'https://www.google.com/maps?q=13.0827,80.2707',
            'terms' => '1',
        ]);

        $order = Order::query()->with('items')->firstOrFail();
        $checkoutResponse->assertRedirect(route('order.payment', $order->order_number));

        $this->assertMatchesRegularExpression('/^SS'.now()->format('Ym').'\d{5}$/', $order->order_number);
        $this->assertSame('SS'.now()->format('Ym').'00001', $order->order_number);
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Koti Customer', $order->customer_name);
        $this->assertSame($customer->phone, $order->customer_phone);
        $this->assertSame('https://www.google.com/maps?q=13.0827,80.2707', $order->delivery_location_url);
        $this->assertSame('payment_pending', $order->status);
        $this->assertSame('unselected', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->placed_at);
        $this->assertCount(1, $order->items);
        $this->assertEmpty(session('cart', []));

        $this->actingAs($customer)->get(route('order.payment', $order->order_number))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Choose payment to place order')
            ->assertSee('Scan QR first')
            ->assertSee('Cash On Delivery')
            ->assertSee('Place Order With COD')
            ->assertSee('Pay Online & Place Order', false)
            ->assertSee('id="rzp-button1"', false)
            ->assertSee('Online payment is not configured')
            ->assertSee('checkout.razorpay.com/v1/checkout.js')
            ->assertSee('amount: "100"', false)
            ->assertSee('contact: "9876543210"', false);

        $this->actingAs($customer)->get(route('order.success', $order->order_number))->assertNotFound();

        $this->actingAs($customer)->post(route('order.payment.cod', $order->order_number))
            ->assertRedirect(route('order.success', $order->order_number));

        $order->refresh();
        $variantStock = ProductVariant::query()->where('sku', 'SS-CHECKOUT-001')->value('stock');

        $this->assertSame('placed', $order->status);
        $this->assertSame('cod', $order->payment_method);
        $this->assertNotNull($order->placed_at);
        $this->assertSame(98, $variantStock);

        $this->actingAs($customer)->get(route('order.success', $order->order_number))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Hey Koti Customer, your order placed successfully')
            ->assertSee('Order ID')
            ->assertSee('Thank you for shopping with Sushako')
            ->assertSee('Order Details')
            ->assertSee('Download Invoice')
            ->assertSee('COD - customer has to pay at delivery')
            ->assertSee('Track Your Order')
            ->assertSee('View shared delivery location')
            ->assertDontSee('id="rzp-button1"', false);
    }

    public function test_guest_can_checkout_with_mobile_number_and_place_order(): void
    {
        config(['services.razorpay.key' => 'YOUR_TEST_KEY_ID']);

        $checkoutResponse = $this->withSession(['cart' => [$this->cartLine()]])
            ->post(route('checkout.place'), [
                'customer_name' => 'Guest Buyer',
                'customer_phone' => '9876543210',
                'customer_email' => 'guest@example.test',
                'address_line_1' => 'Door 12, First Street',
                'address_line_2' => 'Apartment 4B',
                'pincode' => '600001',
                'delivery_location_url' => 'https://www.google.com/maps?q=13.0827,80.2707',
                'terms' => '1',
            ]);

        $order = Order::query()->with('items')->firstOrFail();
        $customer = User::query()->where('role', User::ROLE_CUSTOMER)->where('phone', '9876543210')->firstOrFail();

        $checkoutResponse->assertRedirect(route('order.payment', $order->order_number));
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Guest Buyer', $order->customer_name);
        $this->assertSame('9876543210', $order->customer_phone);
        $this->assertSame('guest@example.test', $order->customer_email);
        $this->assertSame('Not provided', $order->city);
        $this->assertSame('payment_pending', $order->status);
        $this->assertEmpty(session('cart', []));
        $this->assertSame('9876543210', session('checkout_orders.'.$order->order_number.'.phone'));

        $this->get(route('order.payment', $order->order_number))
            ->assertOk()
            ->assertSee('Choose payment to place order');

        $this->post(route('order.payment.cod', $order->order_number))
            ->assertRedirect(route('order.success', $order->order_number));

        $this->get(route('order.success', $order->order_number))
            ->assertOk()
            ->assertSee('Hey Guest Buyer, your order placed successfully')
            ->assertSee('Download Invoice');
    }

    public function test_customers_cannot_access_another_customers_orders(): void
    {
        $owner = $this->customer(['phone' => '9876543210']);
        $otherCustomer = $this->customer(['email' => 'other@example.test', 'phone' => '9876543211']);
        $order = $this->orderFor($owner, [
            'status' => 'placed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        $this->actingAs($otherCustomer)->get(route('order.payment', $order->order_number))->assertForbidden();
        $this->actingAs($otherCustomer)->post(route('order.payment.cod', $order->order_number))->assertForbidden();
        $this->actingAs($otherCustomer)->get(route('order.success', $order->order_number))->assertForbidden();
        $this->actingAs($otherCustomer)->get(route('order.invoice', $order->order_number))->assertForbidden();
        $this->actingAs($otherCustomer)->postJson(route('order.payment.razorpay-test', $order->order_number))->assertForbidden();
    }

    public function test_legacy_guest_orders_are_not_disclosed_to_authenticated_customers(): void
    {
        $customer = $this->customer();
        $order = Order::query()->create([
            'order_number' => 'SS'.now()->format('Ym').'00009',
            'customer_name' => 'Legacy Guest',
            'customer_phone' => '9876543299',
            'address_line_1' => 'Door 99',
            'city' => 'Chennai',
            'pincode' => '600001',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => 'placed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        $this->actingAs($customer)->get(route('order.success', $order->order_number))->assertForbidden();
        $this->actingAs($customer)->get(route('order.invoice', $order->order_number))->assertForbidden();
    }

    public function test_checkout_cart_items_can_be_updated_and_removed(): void
    {
        $key = 'sushako-checkout-product-Standard-Standard';

        $this->withSession(['cart' => [$key => $this->cartLine()]])
            ->patchJson(route('cart.update', $key), [
                'quantity' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('cart.0.quantity', 3);

        $this->assertSame(3, session('cart')[$key]['quantity']);

        $this->deleteJson(route('cart.destroy', $key))
            ->assertOk()
            ->assertJsonPath('cart_count', 0);

        $this->assertSame([], session('cart'));
    }

    public function test_buy_now_adds_product_and_goes_directly_to_checkout(): void
    {
        $this->get(route('products.show', 'sushako-checkout-product'))
            ->assertOk()
            ->assertSee(route('cart.buy-now'), false)
            ->assertSee('Buy Now');

        $customer = $this->customer();

        $this->actingAs($customer)->post(route('cart.buy-now'), [
            'slug' => 'sushako-checkout-product',
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 2,
        ])->assertRedirect(route('checkout'));

        $this->assertNotEmpty(session('cart', []));

        $this->actingAs($customer)
            ->followingRedirects()
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('Guest checkout in a few steps')
            ->assertDontSee('Your cart is empty');
    }

    public function test_local_razorpay_test_confirmation_marks_order_paid(): void
    {
        config(['services.razorpay.test_mode' => true]);
        PaymentSetting::query()->where('provider', 'razorpay')->update(['enabled' => true]);
        $customer = $this->customer();

        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'customer_name' => 'Paid Buyer',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => 'payment_pending',
            'payment_method' => 'unselected',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($customer)->postJson(route('order.payment.razorpay-test', $order->order_number), [
            'razorpay_payment_id' => 'pay_test_123',
            'razorpay_order_id' => 'order_test_123',
            'razorpay_signature' => 'test_signature',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('redirect_url', route('order.success', $order->order_number));

        $order->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('placed', $order->status);
        $this->assertSame('razorpay', $order->payment_method);
        $this->assertNotNull($order->placed_at);
        $this->assertSame('pay_test_123', $order->razorpay_payment_id);
        $this->assertSame('order_test_123', $order->razorpay_order_id);

        $this->actingAs($customer)->get(route('order.success', $order->order_number))
            ->assertOk()
            ->assertSee('Paid online via Razorpay')
            ->assertSee('Invoice is marked as paid')
            ->assertSee('Amount Paid')
            ->assertSee('Download Invoice');
    }

    public function test_invoice_pdf_downloads_for_placed_orders(): void
    {
        $customer = $this->customer();

        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'customer_name' => 'Invoice Buyer',
            'customer_email' => 'invoice@example.test',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => 'placed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_name' => 'Invoice Product',
            'product_slug' => 'invoice-product',
            'colour' => 'Black',
            'size' => 'One Size',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
        ]);

        $response = $this->actingAs($customer)->get(route('order.invoice', $order->order_number));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_invoice_pdf_is_not_available_until_order_is_placed(): void
    {
        $customer = $this->customer();

        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'customer_name' => 'Draft Buyer',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => 'payment_pending',
            'payment_method' => 'unselected',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($customer)->get(route('order.invoice', $order->order_number))->assertNotFound();
    }

    public function test_customer_can_choose_cash_on_delivery_from_payment_page(): void
    {
        $customer = $this->customer();

        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'customer_name' => 'COD Buyer',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => 'payment_pending',
            'payment_method' => 'unselected',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($customer)->post(route('order.payment.cod', $order->order_number))
            ->assertRedirect(route('order.success', $order->order_number));

        $order->refresh();

        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('placed', $order->status);
        $this->assertNotNull($order->placed_at);
    }

    public function test_cart_page_is_luxury_editable_and_trusted(): void
    {
        $key = 'sushako-checkout-product-Standard-Standard';

        $this->withSession(['cart' => [$key => $this->cartLine()]])
            ->get(route('cart.empty'))
            ->assertOk()
            ->assertSee('Shopping Bag')
            ->assertSee('data-cart-update', false)
            ->assertSee('data-cart-remove', false)
            ->assertSee('assets/payments/razorpay.svg')
            ->assertSee('WhatsApp Support')
            ->assertSee('Track Order');
    }

    public function test_checkout_requires_delivery_details(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->withSession(['cart' => [$this->cartLine()]])
            ->post(route('checkout.place'), [
                'customer_name' => 'Missing Address',
                'customer_phone' => '12345',
                'terms' => '1',
            ])->assertSessionHasErrors(['customer_phone', 'address_line_1', 'pincode']);
    }

    public function test_customer_can_save_address_and_use_it_at_checkout(): void
    {
        $customer = User::factory()->create([
            'name' => 'Koti Customer',
            'phone' => '9876543210',
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), [
                'label' => 'Home',
                'recipient_name' => 'Koti Customer',
                'phone' => '9876543210',
                'address_line_1' => 'Door 10, Sushako Street',
                'address_line_2' => 'Near Store',
                'city' => 'Chengalpattu',
                'pincode' => '603001',
                'landmark' => 'Blue gate',
                'delivery_location_url' => 'https://www.google.com/maps?q=12.6819,79.9888',
                'is_default' => '1',
            ])
            ->assertRedirect();

        $address = $customer->addresses()->firstOrFail();

        $this->assertTrue($address->is_default);
        $this->assertSame('Door 10, Sushako Street', $address->address_line_1);

        $this->actingAs($customer)
            ->withSession(['cart' => [$this->cartLine()]])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('data-checkout-address', false)
            ->assertSee('Door 10, Sushako Street')
            ->assertSee('Chengalpattu - 603001')
            ->assertSee('checked', false);
    }

    public function test_order_tracking_shows_only_authenticated_customers_orders(): void
    {
        $customer = $this->customer(['phone' => '9876543210']);
        $otherCustomer = $this->customer(['email' => 'other-track@example.test', 'phone' => '9876543211']);
        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'customer_name' => 'Track Buyer',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'placed_at' => now(),
        ]);
        $otherOrder = $this->orderFor($otherCustomer, [
            'order_number' => 'SS'.now()->format('Ym').'00002',
            'placed_at' => now(),
        ]);

        $this->actingAs($customer)->get(route('orders.track'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertDontSee($otherOrder->order_number);

        $this->actingAs($customer)->post(route('orders.track.lookup'), [
            'query' => strtolower($order->order_number),
        ])->assertOk()->assertSee($order->order_number);

        $this->actingAs($customer)->post(route('orders.track.lookup'), [
            'query' => $otherOrder->order_number,
        ])->assertOk()->assertSee('No matching order found');
    }

    public function test_public_order_tracking_requires_matching_contact_details(): void
    {
        $customer = $this->customer(['phone' => '9876543210']);
        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00003',
            'customer_phone' => '9876543210',
            'customer_email' => 'track@example.test',
            'customer_name' => 'Public Track Buyer',
            'placed_at' => now(),
        ]);

        $this->post(route('orders.track.lookup'), [
            'query' => $order->order_number,
            'contact' => '0000000000',
        ])->assertOk()
            ->assertSee('No matching order found')
            ->assertDontSee('Public Track Buyer');

        $this->post(route('orders.track.lookup'), [
            'query' => $order->order_number,
            'contact' => '9876543210',
        ])->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Public Track Buyer')
            ->assertSee('Download Invoice');
    }

    public function test_public_tracking_unlocks_guest_payment_for_pending_orders(): void
    {
        $customer = $this->customer(['phone' => '9876543210']);
        $order = $this->orderFor($customer, [
            'order_number' => 'SS'.now()->format('Ym').'00004',
            'customer_phone' => '9876543210',
            'customer_email' => 'pending@example.test',
            'customer_name' => 'Pending Guest Buyer',
            'status' => 'payment_pending',
            'payment_method' => 'unselected',
            'payment_status' => 'pending',
            'placed_at' => null,
        ]);

        $this->get(route('order.payment', $order->order_number))->assertNotFound();

        $this->post(route('orders.track.lookup'), [
            'query' => $order->order_number,
            'contact' => '9876543210',
        ])->assertOk()
            ->assertSee('Complete Payment');

        $this->get(route('order.payment', $order->order_number))
            ->assertOk()
            ->assertSee('Choose payment to place order');
    }

    public function test_customer_login_and_register_screens_are_retired(): void
    {
        $this->get(route('login'))
            ->assertRedirect(route('orders.track'))
            ->assertSessionHas('status');

        $this->get(route('register'))
            ->assertRedirect(route('shop'))
            ->assertSessionHas('status');
    }

    public function test_customer_account_history_matches_orders_by_mobile(): void
    {
        $customer = User::factory()->create([
            'phone' => '9876543210',
        ]);

        Order::query()->create([
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'user_id' => $customer->id,
            'customer_name' => 'Account Buyer',
            'customer_phone' => '9876543210',
            'address_line_1' => 'Door 12',
            'city' => 'Chennai',
            'pincode' => '600001',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'placed_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('SS'.now()->format('Ym').'00001');
    }

    private function cartLine(): array
    {
        $product = Product::query()->where('slug', 'sushako-checkout-product')->firstOrFail();
        $variant = $product->variants()->where('colour', 'Standard')->where('size', 'Standard')->firstOrFail();

        return [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'product' => 'Sushako Checkout Product',
            'slug' => 'sushako-checkout-product',
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
            'price' => 1,
            'image' => '/assets/banners/home-made-health-mix.jpg',
        ];
    }

    private function customer(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'phone' => '9876543210',
        ], $overrides));
    }

    private function orderFor(User $user, array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone,
            'customer_email' => $user->email,
            'address_line_1' => 'Door 12',
            'city' => 'Chennai',
            'pincode' => '600001',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => 'placed',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ], $overrides));
    }

    private function createCheckoutProduct(): void
    {
        $category = Category::query()->where('slug', 'home-made-health-mix')->firstOrFail();
        $officialStore = Vendor::query()->where('slug', 'sushako-official-store')->firstOrFail();
        $product = Product::query()->updateOrCreate([
            'slug' => 'sushako-checkout-product',
        ], [
            'vendor_id' => $officialStore->id,
            'category_id' => $category->id,
            'name' => 'Sushako Checkout Product',
            'collection' => 'Sushako Essentials',
            'subcategory' => 'Checkout Essentials',
            'brand' => 'Sushako',
            'badge' => 'Featured',
            'short_description' => 'A low value Sushako product for verifying secure checkout behavior.',
            'full_description' => 'A focused Sushako product experience built around secure checkout.',
            'mrp' => 1,
            'selling_price' => 1,
            'rating' => 5.0,
            'reviews' => 1,
            'is_published' => true,
            'seller_status' => Product::SELLER_STATUS_APPROVED,
            'local_delivery' => true,
            'fulfillment_scope' => 'Sushako Fulfillment',
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
            'sku' => 'SS-CHECKOUT-001',
        ], [
            'colour' => 'Standard',
            'colour_hex' => '#d8ccb9',
            'size' => 'Standard',
            'stock' => 99,
        ]);
    }
}
