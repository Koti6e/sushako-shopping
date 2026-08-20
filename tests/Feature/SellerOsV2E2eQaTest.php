<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerOnboardingPayment;
use App\Models\SellerPlanPayment;
use App\Models\SellerSettlement;
use App\Models\SellerStorefrontCategory;
use App\Models\ShippingLabel;
use App\Models\User;
use App\Models\Vendor;
use App\Services\SellerCommissionService;
use App\Services\ShippingLabelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerOsV2E2eQaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_realistic_seller_os_v2_e2e_workflow_and_cleanup(): void
    {
        Storage::fake('public');
        $stamp = now()->format('YmdHis');
        $email = "seller.qa.{$stamp}@sushako.test";
        $password = 'Password!234';

        $baseline = $this->counts();

        $this->post(route('seller.register'), [
            'name' => 'Seller QA',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => '1',
        ])->assertRedirect(route('seller.onboarding'));

        $seller = User::query()->where('email', $email)->firstOrFail();
        $vendor = $seller->vendor()->firstOrFail();
        $this->assertSame('draft', $vendor->onboarding_status);

        foreach ([
            route('seller.dashboard'),
            route('seller.products.index'),
            route('seller.orders.index'),
            route('seller.earnings.index'),
            route('seller.settings.index'),
            route('seller.plans.index'),
            route('seller.onboarding.payment'),
        ] as $url) {
            $this->actingAs($seller)->get($url)->assertRedirect(route('seller.onboarding'));
        }

        $this->actingAs($seller)->get(route('seller.onboarding'))
            ->assertOk()
            ->assertSee('Your shop is almost ready.')
            ->assertDontSee('seller-mobile-bottom-nav')
            ->assertDontSee('Quick Action');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'business_name' => '',
        ]))->assertSessionHasErrors('business_name');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'city' => '',
        ]))->assertSessionHasErrors('city');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'postal_code' => '60300A',
        ]))->assertSessionHasErrors('postal_code');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'gst_registered' => 'yes',
            'gstin' => '',
            'pan_number' => null,
        ]))->assertSessionHasErrors('gstin');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'pan_number' => 'BADPAN',
        ]))->assertSessionHasErrors('pan_number');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'delivery_radius' => -1,
        ]))->assertSessionHasErrors('delivery_radius');

        $this->post(route('seller.onboarding.store'), $this->onboardingPayload([
            'pickup_same_as_business' => '0',
            'pickup_address' => 'Warehouse 2, Mahindra World City',
            'pickup_postal_code' => '603002',
            'pickup_city' => 'Chengalpattu',
            'pickup_state' => 'Tamil Nadu',
            'returns_accepted' => 'yes',
            'return_window_days' => 7,
        ]))->assertRedirect(route('seller.dashboard'))->assertSessionHasNoErrors();

        $vendor = $seller->fresh()->vendor;
        $this->assertSame('complete', $vendor->onboarding_status);
        $this->assertTrue($vendor->dashboard_access_enabled, 'dashboard_access_enabled should be true after onboarding');
        $this->assertSame(Vendor::PLAN_FREE, $vendor->current_plan);
        $this->assertSame('Warehouse 2, Mahindra World City', $vendor->pickup_address_line_1);
        $this->assertTrue($vendor->returns_accepted, 'returns_accepted should reflect onboarding choice');
        $this->assertSame(7, (int) $vendor->return_window_days);
        Storage::disk('public')->assertExists($vendor->business_logo_path);

        $this->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Your shop is ready')
            ->assertSee('Welcome to Sushako QA Test Store')
            ->assertSee('Add Your First Product')
            ->assertSee('Dashboard')
            ->assertSee('Orders')
            ->assertSee('Products')
            ->assertSee('Customers')
            ->assertSee('Earnings / Settlements')
            ->assertSee('Delivery')
            ->assertSee('My Shop')
            ->assertSee('Upgrade Plan')
            ->assertSee('Settings')
            ->assertSee('Logout')
            ->assertSee('Rs 0')
            ->assertDontSee('undefined')
            ->assertDontSee('null');

        $this->get(route('admin.dashboard'))->assertForbidden();

        $marketplaceCategory = Category::query()->where('available_to_sellers', true)->firstOrFail();
        $this->post(route('seller.categories.subcategories.store'), [
            'category_id' => $marketplaceCategory->id,
            'name' => 'QA Electronics',
            'description' => 'Temporary QA seller category',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ])->assertRedirect();
        $storeCategory = $vendor->fresh()->storefrontCategories()->where('name', 'QA Electronics')->firstOrFail();

        $otherSeller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $otherVendor = app(\App\Services\SellerAccountService::class)->activateStarter(app(\App\Services\SellerAccountService::class)->ensureVendor($otherSeller));
        $this->actingAs($otherSeller)->put(route('seller.categories.subcategories.update', $storeCategory), [
            'category_id' => $marketplaceCategory->id,
            'name' => 'Stolen QA Electronics',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ])->assertNotFound();

        $this->actingAs($seller)->get(route('seller.products.create'))
            ->assertOk()
            ->assertSee('QA Electronics')
            ->assertSee('Buying Price')
            ->assertSee('Seller Economics')
            ->assertSee('Publish Now')
            ->assertSee('Schedule');

        $this->post(route('seller.products.store'), $this->productPayload($marketplaceCategory, $storeCategory, [
            'name' => '',
        ]))->assertSessionHasErrors('name');

        $this->post(route('seller.products.store'), $this->productPayload($marketplaceCategory, $storeCategory, [
            'selling_price' => 0,
        ]))->assertSessionHasErrors('selling_price');

        $this->post(route('seller.products.store'), $this->productPayload($marketplaceCategory, $storeCategory, [
            'buying_price' => -1,
        ]))->assertSessionHasErrors('buying_price');

        $this->post(route('seller.products.store'), $this->productPayload($marketplaceCategory, $storeCategory, [
            'images' => [
                UploadedFile::fake()->image('1.jpg'),
                UploadedFile::fake()->image('2.jpg'),
                UploadedFile::fake()->image('3.jpg'),
                UploadedFile::fake()->image('4.jpg'),
                UploadedFile::fake()->image('5.jpg'),
            ],
        ]))->assertSessionHasErrors('images');

        $this->post(route('seller.products.store'), $this->productPayload($marketplaceCategory, $storeCategory))
            ->assertRedirect(route('seller.dashboard'));
        $product = $vendor->fresh()->products()->where('name', 'QA Wireless Mouse')->firstOrFail();
        $this->assertSame(500, (int) $product->buying_price);
        $this->assertSame(Product::SELLER_STATUS_ACTIVE, $product->seller_status);
        $this->assertTrue($product->is_published, 'published-now product should be published');
        $this->assertSame(2, $product->images()->count());
        $preview = app(SellerCommissionService::class)->preview($vendor->fresh(), $product, 750, 1);
        $this->assertSame(1, $preview['commission']);
        $this->assertSame(749, $preview['seller_earning']);

        $future = now()->addDay()->seconds(0);
        $this->post(route('seller.products.store'), $this->productPayload($marketplaceCategory, $storeCategory, [
            'name' => 'QA Scheduled Product',
            'sku' => 'QA-SCHEDULED-'.$stamp,
            'go_live_mode' => 'schedule',
            'go_live_at' => $future->format('Y-m-d H:i:s'),
        ]))->assertRedirect(route('seller.products.edit', $vendor->fresh()->products()->latest('id')->first()));
        $scheduled = $vendor->fresh()->products()->where('name', 'QA Scheduled Product')->firstOrFail();
        $this->assertSame(Product::SELLER_STATUS_SCHEDULED, $scheduled->seller_status);
        $this->assertSame($future->format('Y-m-d H:i:s'), $scheduled->scheduled_go_live_at->format('Y-m-d H:i:s'));

        $this->get(route('seller.products.index'))
            ->assertOk()
            ->assertSee('QA Electronics')
            ->assertSee('QA Wireless Mouse')
            ->assertSee('QA Scheduled Product')
            ->assertSee('views');

        $this->get(route('stores.show', $vendor->slug))
            ->assertOk()
            ->assertSee('Sushako QA Test Store')
            ->assertSee('QA Wireless Mouse')
            ->assertSee('QA Scheduled Product');

        $this->get(route('products.show', $scheduled->slug))
            ->assertOk()
            ->assertSee('Coming Soon')
            ->assertSee('Purchasing opens automatically');
        $this->post(route('cart.store'), [
            'slug' => $scheduled->slug,
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
        ])->assertSessionHasErrors('product');
        $scheduled->forceFill(['scheduled_go_live_at' => now()->subMinute()])->save();
        $this->post(route('cart.store'), [
            'slug' => $scheduled->slug,
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
        ])->assertRedirect();
        session()->forget('cart');

        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'name' => 'QA Customer',
            'email' => "customer.qa.{$stamp}@sushako.test",
            'phone' => '9000000999',
        ]);

        $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Returns accepted within 7 days from delivery');
        $this->assertSame(0, (int) $product->fresh()->seller_product_views);
        $this->actingAs($customer)->get(route('products.show', $product->slug))->assertOk();
        $this->assertSame(1, (int) $product->fresh()->seller_product_views);
        $this->get(route('products.show', $product->slug))->assertOk();
        $this->assertSame(1, (int) $product->fresh()->seller_product_views);
        $this->actingAs($seller)->get(route('products.show', $product->slug))->assertOk();
        $this->assertSame(1, (int) $product->fresh()->seller_product_views);

        $this->actingAs($customer)->post(route('cart.store'), [
            'slug' => $product->slug,
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
        ])->assertRedirect();
        $this->post(route('checkout.place'), [
            'customer_name' => 'QA Customer',
            'customer_phone' => '9000000999',
            'customer_email' => $customer->email,
            'address_line_1' => 'QA Door 1',
            'address_line_2' => 'QA Street',
            'city' => 'Chengalpattu',
            'pincode' => '603001',
            'delivery_location_url' => 'https://www.google.com/maps?q=12.6819,79.9888',
            'terms' => '1',
        ])->assertRedirect();
        $order = Order::query()->where('customer_email', $customer->email)->firstOrFail();
        $this->post(route('order.payment.cod', $order->order_number))->assertRedirect(route('order.success', $order->order_number));
        $order = $order->fresh();
        $this->assertSame('new', $order->seller_order_status);
        $this->assertTrue($order->seller_acceptance_due_at->isFuture(), 'new order acceptance due time should be in the future');
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'platform_fee_total' => 1,
            'seller_earning' => 749,
        ]);

        $this->get(route('orders.track', ['query' => $order->order_number, 'contact' => '9000000999']))
            ->assertOk()
            ->assertDontSee('Wait for Seller')
            ->assertDontSee('Request Refund');
        $order->forceFill(['seller_acceptance_due_at' => now()->subMinute()])->save();
        $this->get(route('orders.track', ['query' => $order->order_number, 'contact' => '9000000999']))
            ->assertOk()
            ->assertSee('Wait for Seller')
            ->assertSee('Request Refund')
            ->assertSee('payment gateway charges may be deducted');
        $this->post(route('order.wait-for-seller', $order->order_number))->assertRedirect();
        $this->assertSame('wait', $order->fresh()->customer_overdue_choice);

        $this->actingAs($seller)->get(route('seller.orders.index'))
            ->assertOk()
            ->assertSee('All Orders')
            ->assertSee('New')
            ->assertSee('Accepted')
            ->assertSee('Packed')
            ->assertSee('Shipped')
            ->assertSee('Delivered')
            ->assertSee('Cancelled')
            ->assertSee($order->order_number);
        $this->put(route('seller.orders.update', $order->order_number), ['seller_order_status' => 'shipped'])
            ->assertStatus(422);
        foreach (['accepted', 'packed', 'shipped', 'delivered'] as $status) {
            $this->put(route('seller.orders.update', $order->order_number), [
                'seller_order_status' => $status,
                'shipping_provider' => $status === 'shipped' ? 'dtdc' : null,
                'tracking_number' => $status === 'shipped' ? 'QA123' : null,
                'tracking_url' => $status === 'shipped' ? 'https://track.example.test/QA123' : null,
            ])->assertRedirect();
        }
        $this->assertSame('delivered', $order->fresh()->seller_order_status);
        $this->actingAs($customer)->post(route('order.cancel', $order->order_number))->assertStatus(422);
        $this->assertGreaterThanOrEqual(5, $order->statusEvents()->count());

        $refundOrder = Order::query()->create([
            'order_number' => 'QA-REFUND-'.$stamp,
            'user_id' => $customer->id,
            'customer_name' => 'QA Customer',
            'customer_phone' => '9000000999',
            'customer_email' => $customer->email,
            'address_line_1' => 'QA Door 1',
            'city' => 'Chengalpattu',
            'pincode' => '603001',
            'subtotal' => 750,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 750,
            'status' => 'placed',
            'seller_order_status' => 'new',
            'seller_acceptance_due_at' => now()->subMinute(),
            'payment_method' => 'razorpay',
            'payment_status' => 'paid',
            'placed_at' => now()->subDay(),
        ]);
        $refundOrder->items()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'product_variant_id' => $product->variants()->first()->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
            'unit_price' => 750,
            'line_total' => 750,
            'gross_line_amount' => 750,
            'platform_fee_total' => 1,
            'seller_earning' => 749,
            'settlement_status' => 'upcoming',
        ]);
        $this->actingAs($customer)->post(route('order.request-refund', $refundOrder->order_number))->assertRedirect();
        $this->assertSame('refund', $refundOrder->fresh()->customer_overdue_choice);
        $this->assertSame('refund_initiated', $refundOrder->fresh()->status);

        $this->actingAs($seller)->get(route('seller.customers.index'))
            ->assertOk()
            ->assertSee('QA Customer')
            ->assertSee('9000000999');
        $this->get(route('seller.earnings.index'))->assertOk()->assertSee('Gross Sales');
        $this->get(route('seller.settlements.index'))->assertOk()->assertSee('QA Wireless Mouse');

        $this->put(route('seller.shipping.update'), [
            'address_line_1' => 'No. 10, GST Road',
            'address_line_2' => null,
            'city' => 'Chengalpattu',
            'district' => 'Chengalpattu',
            'state' => 'Tamil Nadu',
            'postal_code' => '603001',
            'pickup_address' => 'Warehouse 2, Mahindra World City',
            'return_address' => 'Warehouse 2, Mahindra World City',
            'shipping_commitment' => 'ships_within_1_business_day',
            'flat_shipping_charge' => 40,
            'free_shipping_threshold' => 999,
            'delivery_radius' => 10,
            'working_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'opens_at' => '09:00',
            'closes_at' => '18:00',
            'manual_tracking_enabled' => '1',
        ])->assertRedirect();
        $this->assertSame(['mon', 'tue', 'wed', 'thu', 'fri'], $vendor->fresh()->working_days);
        $this->get(route('seller.shipping.index'))
            ->assertOk()
            ->assertSee('Monday')
            ->assertSee('Closed')
            ->assertSee('09:00 AM -&gt; 06:00 PM', false)
            ->assertSee('Save Shipping Details');
        $this->put(route('seller.shipping.update'), [
            'address_line_1' => 'No. 10, GST Road',
            'city' => 'Chengalpattu',
            'state' => 'Tamil Nadu',
            'postal_code' => 'BAD',
            'pickup_address' => 'Warehouse 2, Mahindra World City',
            'return_address' => 'Warehouse 2, Mahindra World City',
            'shipping_commitment' => 'ships_within_1_business_day',
        ])->assertSessionHasErrors('postal_code');

        $this->get(route('seller.plans.index'))
            ->assertOk()
            ->assertSee('Current Free Plan')
            ->assertSee('Razorpay checkout')
            ->assertSee('Payment gateway charges (Razorpay) apply separately to online transactions on all plans.')
            ->assertSee('Sushako Branding')
            ->assertSee('Sushako Labelling')
            ->assertSee('Own Branding Label');
        $this->post(route('seller.plans.renew'), ['plan' => Vendor::PLAN_FREE])->assertRedirect();
        $this->assertSame(Vendor::PLAN_FREE, $vendor->fresh()->current_plan);

        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'status' => User::STATUS_ACTIVE]);
        $label = app(ShippingLabelService::class)->generate($order->fresh(), $superAdmin, [
            'seller_name' => $vendor->business_name,
            'seller_address' => $vendor->pickup_address,
            'seller_contact' => $vendor->phone,
            'seller_return_address' => $vendor->return_address,
        ]);
        $this->assertStringContainsString('google.com/maps', (string) $label->location_qr_url);

        $this->post(route('seller.logout'))->assertRedirect(route('seller.login'));
        $this->post(route('seller.login.store'), ['email' => $email, 'password' => $password])
            ->assertRedirect(route('seller.dashboard'));
        $this->get(route('seller.dashboard'))->assertOk()->assertDontSee('Your shop is almost ready.');

        $otherProduct = Product::query()->create([
            'vendor_id' => $otherVendor->id,
            'category_id' => $marketplaceCategory->id,
            'name' => 'Other Seller Product',
            'slug' => 'other-seller-product-'.$stamp,
            'short_description' => 'Other',
            'full_description' => 'Other',
            'mrp' => 100,
            'selling_price' => 100,
            'buying_price' => 50,
            'seller_status' => Product::SELLER_STATUS_ACTIVE,
            'product_condition' => 'new',
            'package_contents' => 'Box',
            'low_stock_threshold' => 1,
        ]);
        $this->actingAs($seller)->get(route('seller.products.edit', $otherProduct))->assertNotFound();

        $qaProductIds = $vendor->fresh()->products()->pluck('id');
        $qaOrderIds = Order::query()->where('customer_email', $customer->email)->orWhere('order_number', 'like', 'QA-%')->pluck('id');
        ShippingLabel::query()->whereIn('order_id', $qaOrderIds)->delete();
        $vendor->fresh()->products()->each(function (Product $product): void {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->delete();
        });
        Order::query()->whereIn('id', $qaOrderIds)->delete();
        SellerSettlement::query()->where('vendor_id', $vendor->id)->delete();
        SellerPlanPayment::query()->where('vendor_id', $vendor->id)->delete();
        SellerOnboardingPayment::query()->where('vendor_id', $vendor->id)->delete();
        $vendor->fresh()->storefrontCategories()->delete();
        $vendor->fresh()->audits()->delete();
        if ($vendor->business_logo_path) {
            Storage::disk('public')->delete($vendor->business_logo_path);
        }
        $vendor->delete();
        $seller->delete();
        $customer->delete();
        $otherProduct->delete();
        $otherVendor->delete();
        $otherSeller->delete();

        $this->assertDatabaseMissing('users', ['email' => $email]);
        $this->assertDatabaseMissing('vendors', ['business_name' => 'Sushako QA Test Store']);
        foreach ($qaProductIds as $productId) {
            $this->assertDatabaseMissing('products', ['id' => $productId]);
        }
        foreach ($qaOrderIds as $orderId) {
            $this->assertDatabaseMissing('orders', ['id' => $orderId]);
        }

        $this->assertSame($baseline['qa_sellers'], User::query()->where('email', 'like', 'seller.qa.%@sushako.test')->count());
    }

    private function onboardingPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Sushako QA Test Store',
            'business_logo' => UploadedFile::fake()->image('qa-logo.png', 120, 120),
            'business_address' => 'No. 10, GST Road',
            'phone' => '9123456789',
            'city' => 'Chengalpattu',
            'state' => 'Tamil Nadu',
            'postal_code' => '603001',
            'pickup_same_as_business' => '1',
            'gst_registered' => 'no',
            'pan_number' => 'ABCDE1234F',
            'delivery_radius' => 10,
            'flat_shipping_charge' => 40,
            'free_shipping_threshold' => 999,
            'returns_accepted' => 'no',
        ], $overrides);
    }

    private function productPayload(Category $category, SellerStorefrontCategory $storeCategory, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $category->id,
            'seller_storefront_category_id' => $storeCategory->id,
            'name' => 'QA Wireless Mouse',
            'brand' => 'Sushako QA',
            'short_description' => 'Temporary Seller OS V2 QA product',
            'full_description' => 'Temporary Seller OS V2 QA product',
            'mrp' => 900,
            'buying_price' => 500,
            'selling_price' => 750,
            'seller_status' => 'approved',
            'go_live_mode' => 'publish_now',
            'product_condition' => 'new',
            'stock' => 5,
            'low_stock_threshold' => 2,
            'sku' => 'QA-MOUSE-'.now()->format('His'),
            'images' => [
                UploadedFile::fake()->image('qa-mouse-1.jpg', 800, 800),
                UploadedFile::fake()->image('qa-mouse-2.jpg', 800, 800),
            ],
        ], $overrides);
    }

    private function counts(): array
    {
        return [
            'users' => User::count(),
            'sellers' => User::where('role', User::ROLE_SELLER)->count(),
            'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'orders' => Order::count(),
            'qa_sellers' => User::query()->where('email', 'like', 'seller.qa.%@sushako.test')->count(),
        ];
    }
}
