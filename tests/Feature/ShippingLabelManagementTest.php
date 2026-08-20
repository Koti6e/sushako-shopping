<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\Models\ShippingLabelEvent;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ShippingLabelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingLabelManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_super_admin_can_open_warehouse_label_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $order = $this->placedOrder([
            'delivery_latitude' => '13.0827000',
            'delivery_longitude' => '80.2707000',
            'location_confirmed' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.shipping-labels.generate', $order->order_number), [
            'brand_mode' => 'sushako',
            'fulfillment_type' => 'fulfilled_by_sushako',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.shipping-labels.index'))
            ->assertOk()
            ->assertSee('Labels')
            ->assertDontSee('Warehouse Dispatch')
            ->assertSee('Pending Generation')
            ->assertSee('Ready To Print')
            ->assertSee('Location Missing')
            ->assertSee($order->order_number)
            ->assertSee('Label Preview')
            ->assertSee('Warehouse Efficiency');
    }

    public function test_super_admin_can_generate_view_and_print_shipping_label(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $order = $this->placedOrder([
            'delivery_latitude' => '13.0827000',
            'delivery_longitude' => '80.2707000',
            'location_confirmed' => true,
            'location_capture_method' => 'gps',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.shipping-labels.generate', $order->order_number), [
            'brand_mode' => 'sushako',
            'fulfillment_type' => 'fulfilled_by_sushako',
            'print_format' => 'a6_thermal',
            'package_count' => 2,
            'package_index' => 1,
            'weight_grams' => 850,
        ]);

        $label = ShippingLabel::query()->firstOrFail();
        $response->assertRedirect(route('admin.shipping-labels.show', $label));

        $this->assertSame($order->id, $label->order_id);
        $this->assertSame('generated', $label->status);
        $this->assertSame('https://www.google.com/maps/search/?api=1&query=13.0827000,80.2707000', $label->location_qr_url);
        $this->assertDatabaseHas('shipping_label_events', ['shipping_label_id' => $label->id, 'event' => 'generated']);

        $this->actingAs($admin)
            ->get(route('admin.shipping-labels.show', $label))
            ->assertOk()
            ->assertSee('SCAN FOR DELIVERY LOCATION')
            ->assertSee('WAREHOUSE LOOKUP')
            ->assertSee('PREPAID');

        $this->actingAs($admin)
            ->post(route('admin.shipping-labels.print', $label), ['copies' => 2, 'printer' => 'Thermal A6'])
            ->assertOk()
            ->assertSee('SUSHAKO SHOPPING');

        $label->refresh();
        $this->assertSame('printed', $label->status);
        $this->assertSame(2, $label->print_count);
        $this->assertNotNull($label->printed_at);
        $this->assertDatabaseHas('shipping_label_events', ['shipping_label_id' => $label->id, 'event' => 'printed']);
    }

    public function test_label_hides_location_qr_when_location_is_not_available(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $order = $this->placedOrder();

        $this->actingAs($admin)->post(route('admin.shipping-labels.generate', $order->order_number), [
            'brand_mode' => 'courier_neutral',
            'fulfillment_type' => 'warehouse',
        ]);

        $label = ShippingLabel::query()->firstOrFail();

        $this->assertNull($label->location_qr_url);

        $this->actingAs($admin)
            ->get(route('admin.shipping-labels.show', $label))
            ->assertOk()
            ->assertSee('Location Not Available');
    }

    public function test_admin_can_update_location_and_regenerate_label_qr_payload(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $order = $this->placedOrder();

        $this->actingAs($admin)->post(route('admin.shipping-labels.generate', $order->order_number), [
            'brand_mode' => 'sushako',
            'fulfillment_type' => 'fulfilled_by_sushako',
        ]);

        $this->actingAs($admin)->put(route('admin.shipping-labels.location.update', $order->order_number), [
            'delivery_latitude' => '12.9716000',
            'delivery_longitude' => '77.5946000',
            'location_confirmed' => '1',
            'location_capture_method' => 'admin_updated',
        ])->assertRedirect();

        $order->refresh();
        $label = ShippingLabel::query()->firstOrFail();

        $this->assertTrue($order->location_confirmed);
        $this->assertSame('admin_updated', $order->location_capture_method);
        $this->assertSame('https://www.google.com/maps/search/?api=1&query=12.9716000,77.5946000', $label->refresh()->location_qr_url);
        $this->assertDatabaseHas('shipping_label_events', ['shipping_label_id' => $label->id, 'event' => 'location_updated']);
    }

    public function test_bulk_generate_labels_returns_json_without_page_refresh(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $first = $this->placedOrder(['order_number' => 'SS'.now()->format('Ym').'00001']);
        $second = $this->placedOrder(['order_number' => 'SS'.now()->format('Ym').'00002']);

        $this->actingAs($admin)
            ->postJson(route('admin.shipping-labels.bulk'), [
                'action' => 'generate',
                'order_ids' => [$first->id, $second->id],
            ])
            ->assertOk()
            ->assertJsonPath('count', 2);

        $this->assertSame(2, ShippingLabel::query()->count());
        $this->assertSame(2, ShippingLabelEvent::query()->where('event', 'generated')->count());
    }

    public function test_shipping_label_admin_routes_are_protected(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->get(route('admin.shipping-labels.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($customer)->get(route('admin.shipping-labels.index'))->assertForbidden();
    }

    public function test_label_branding_is_derived_from_seller_plan_and_blocks_escalation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $service = app(ShippingLabelService::class);

        $freeOrder = $this->placedOrderForPlan(Vendor::PLAN_FREE, 'Free Seller');
        $growthOrder = $this->placedOrderForPlan(Vendor::PLAN_GROWTH, 'Growth Seller');
        $enterpriseOrder = $this->placedOrderForPlan(Vendor::PLAN_ENTERPRISE, 'Enterprise Seller');

        $freeLabel = $service->generate($freeOrder, $admin, ['brand_mode' => ShippingLabel::BRAND_SELLER]);
        $growthLabel = $service->generate($growthOrder, $admin, ['brand_mode' => ShippingLabel::BRAND_SELLER]);
        $enterpriseLabel = $service->generate($enterpriseOrder, $admin, ['brand_mode' => ShippingLabel::BRAND_SUSHAKO]);

        $this->assertSame(ShippingLabel::BRAND_SUSHAKO, $freeLabel->brand_mode);
        $this->assertSame('Sushako Branding', $freeLabel->metadata['branding_rule']);
        $this->assertSame(ShippingLabel::BRAND_SUSHAKO, $growthLabel->brand_mode);
        $this->assertSame('Sushako Labelling', $growthLabel->metadata['branding_rule']);
        $this->assertSame(ShippingLabel::BRAND_SELLER, $enterpriseLabel->brand_mode);
        $this->assertSame('Own Branding Label', $enterpriseLabel->metadata['branding_rule']);
        $this->assertSame('Enterprise Seller', $enterpriseLabel->seller_name);
    }

    private function placedOrder(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_number' => 'SS'.now()->format('Ym').'99999',
            'customer_name' => 'Label Buyer',
            'customer_phone' => '9876543210',
            'customer_email' => 'label@example.test',
            'address_line_1' => 'Door 21',
            'address_line_2' => 'Market Street',
            'city' => 'Chennai',
            'pincode' => '600001',
            'landmark' => 'Near Metro',
            'subtotal' => 1200,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1200,
            'status' => 'placed',
            'payment_method' => 'razorpay',
            'payment_status' => 'paid',
            'placed_at' => now(),
        ], $overrides));
    }

    private function placedOrderForPlan(string $plan, string $sellerName): Order
    {
        $seller = User::factory()->create([
            'role' => User::ROLE_SELLER,
            'name' => $sellerName,
            'email' => str($sellerName)->slug('.').'@example.test',
        ]);
        $vendor = Vendor::query()->create([
            'user_id' => $seller->id,
            'business_name' => $sellerName,
            'store_display_name' => $sellerName,
            'slug' => str($sellerName)->slug('-'),
            'email' => $seller->email,
            'phone' => '9123456789',
            'status' => Vendor::STATUS_ACTIVE,
            'onboarding_status' => 'complete',
            'store_status' => Vendor::STORE_LIVE,
            'store_visibility' => Vendor::VISIBILITY_PUBLISHED,
            'current_plan' => $plan,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => $plan === Vendor::PLAN_FREE ? Vendor::PAYMENT_NOT_REQUIRED : Vendor::PAYMENT_PAID,
            'plan_expires_at' => $plan === Vendor::PLAN_FREE ? null : now()->addMonth(),
            'pickup_address' => $sellerName.' Pickup',
            'return_address' => $sellerName.' Returns',
            'business_logo_path' => 'seller-logos/'.$plan.'.png',
        ]);
        $order = $this->placedOrder(['order_number' => 'SS'.now()->format('Ym').str_pad((string) $vendor->id, 5, '0', STR_PAD_LEFT)]);
        $order->items()->create([
            'vendor_id' => $vendor->id,
            'product_name' => $sellerName.' Product',
            'product_slug' => str($sellerName)->slug('-').'-product',
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        return $order->fresh('items.vendor');
    }
}
