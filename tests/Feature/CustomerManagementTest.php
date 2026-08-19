<?php

namespace Tests\Feature;

use App\Models\CustomerCart;
use App\Models\CustomerCommunication;
use App\Models\CustomerExportAudit;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AbandonedCartService;
use App\Services\WhatsAppIntentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_customer_management_is_admin_only_and_renders_real_metrics(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Metric Customer',
            'phone' => '9876543210',
            'whatsapp_marketing_consent' => true,
            'whatsapp_marketing_consent_at' => now(),
        ]);

        Order::query()->create([
            'order_number' => 'SSMETRIC001',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'address_line_1' => 'One Street',
            'city' => 'Chennai',
            'pincode' => '600001',
            'subtotal' => 1200,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1200,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        $this->get(route('admin.customers.index'))->assertRedirect(route('admin.login'));

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['name' => 'Metric']))
            ->assertOk()
            ->assertSee('Customers')
            ->assertSee('Search customers by name, phone, email, customer ID or order number...')
            ->assertSee('Metric Customer')
            ->assertSee('1 customer')
            ->assertSee('Rs 1,200');
    }

    public function test_customer_management_uses_one_search_across_customer_and_order_fields(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $matched = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Nagarajan A',
            'phone' => '9003213624',
            'email' => 'nagarajan@example.test',
        ]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'name' => 'Other Customer']);

        Order::query()->create([
            'order_number' => 'SSMATCH026',
            'user_id' => $matched->id,
            'customer_name' => $matched->name,
            'customer_phone' => $matched->phone,
            'customer_email' => $matched->email,
            'address_line_1' => 'One Street',
            'city' => 'Chennai',
            'pincode' => '600001',
            'subtotal' => 1200,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1200,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['q' => 'SSMATCH026']))
            ->assertOk()
            ->assertSee('Nagarajan A')
            ->assertDontSee($other->name);

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['q' => 'CUS-'.str_pad((string) $matched->id, 6, '0', STR_PAD_LEFT)]))
            ->assertOk()
            ->assertSee('Nagarajan A')
            ->assertDontSee($other->name);
    }

    public function test_customer_profile_updates_status_with_audit_activity(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->put(route('admin.customers.status.update', $customer), [
                'status' => User::STATUS_INACTIVE,
                'reason' => 'Customer requested temporary account pause.',
            ])
            ->assertRedirect();

        $this->assertSame(User::STATUS_INACTIVE, $customer->fresh()->status);
        $this->assertDatabaseHas('customer_activities', [
            'user_id' => $customer->id,
            'type' => 'status_changed',
            'source' => 'admin',
        ]);
    }

    public function test_customer_profile_is_actionable_and_uses_customer_status_language(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $vendor = Vendor::query()->create([
            'user_id' => $seller->id,
            'business_name' => 'Action Seller',
            'store_display_name' => 'Action Store',
            'slug' => 'action-store',
            'email' => 'seller@example.test',
            'phone' => '9876543210',
            'status' => Vendor::STATUS_ACTIVE,
        ]);
        $product = Product::query()->where('is_published', true)->firstOrFail();
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Nagarajan A',
            'phone' => '9003213624',
            'email' => 'nagarajan@example.test',
            'whatsapp_order_updates' => true,
        ]);
        $order = Order::query()->create([
            'order_number' => 'SSPROFILE001',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'address_line_1' => '10 Market Street',
            'address_line_2' => 'Flat 2',
            'city' => 'Chennai',
            'pincode' => '600001',
            'delivery_location_url' => 'https://www.google.com/maps?q=13.0827,80.2707',
            'subtotal' => 1200,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1200,
            'status' => 'delivered',
            'payment_method' => 'razorpay',
            'payment_status' => 'paid',
            'placed_at' => now(),
        ]);
        $order->items()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'colour' => 'Standard',
            'size' => 'Standard',
            'quantity' => 1,
            'unit_price' => 1200,
            'line_total' => 1200,
            'gross_line_amount' => 1200,
            'seller_earning' => 1199,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('Marketplace Customer 360')
            ->assertSee('href="tel:+919003213624"', false)
            ->assertSee('href="mailto:nagarajan@example.test"', false)
            ->assertSee('href="https://wa.me/919003213624"', false)
            ->assertSee('Open in Google Maps')
            ->assertSee('Customer Status')
            ->assertDontSee('Account Status')
            ->assertSee('Order History')
            ->assertSee('Purchase Across Sushako')
            ->assertSee('Action Seller');
    }

    public function test_whatsapp_intent_requires_marketing_consent_and_normalizes_phone(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'phone' => '+91 98765 43210',
            'whatsapp_marketing_consent' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.customers.whatsapp.prepare', $customer), [
                'purpose' => 'thank_you',
                'message' => 'Hi Customer, thanks for shopping with Sushako Shopping.',
            ])
            ->assertSessionHasErrors('purpose');

        $customer->forceFill([
            'whatsapp_marketing_consent' => true,
            'whatsapp_marketing_consent_at' => now(),
            'whatsapp_marketing_consent_source' => 'Test consent',
        ])->save();

        $this->actingAs($admin)
            ->post(route('admin.customers.whatsapp.prepare', $customer), [
                'purpose' => 'thank_you',
                'message' => 'Hi Customer, thanks for shopping with Sushako Shopping.',
            ])
            ->assertOk()
            ->assertSee('Prepared Intent')
            ->assertSee('Manual Send Only');

        $service = app(WhatsAppIntentService::class);
        $this->assertStringStartsWith('https://wa.me/919876543210?text=', $service->intentUrl($customer, 'Hello'));
    }

    public function test_customer_export_logs_audit_and_excludes_sensitive_fields(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        User::factory()->create(['role' => User::ROLE_CUSTOMER, 'name' => 'Export Customer']);

        $response = $this->actingAs($admin)
            ->get(route('admin.customers.export', ['export' => 'customer_list']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSee('Export Customer');
        $response->assertDontSee('password');
        $this->assertSame(1, CustomerExportAudit::query()->where('export_type', 'customer_list')->count());
    }

    public function test_abandoned_cart_detection_and_secure_recovery(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = Product::query()->with(['variants', 'images'])->where('is_published', true)->firstOrFail();
        $variant = $product->variants->firstOrFail();

        $cart = CustomerCart::query()->create([
            'user_id' => $customer->id,
            'session_id' => 'test-session',
            'status' => CustomerCart::STATUS_ACTIVE,
            'customer_type' => 'registered',
            'original_value' => 1000,
            'recovery_token' => str_repeat('a', 48),
            'recovery_token_expires_at' => now()->addDay(),
            'last_activity_at' => now()->subHours(3),
        ]);

        $cart->items()->create([
            'cart_key' => 'recover-item',
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'colour' => $variant->colour,
            'size' => $variant->size,
            'quantity' => 1,
            'unit_price' => (int) ($variant->price ?: $product->selling_price),
            'line_total' => (int) ($variant->price ?: $product->selling_price),
        ]);

        $this->assertSame(1, app(AbandonedCartService::class)->markAbandonedCarts());
        $this->assertSame(CustomerCart::STATUS_ABANDONED, $cart->fresh()->status);

        $unsignedUrl = route('cart.recover', $cart->recovery_token);
        $this->get($unsignedUrl)->assertForbidden();

        $signedUrl = URL::temporarySignedRoute('cart.recover', now()->addHour(), ['token' => $cart->recovery_token]);
        $this->actingAs($customer)->get($signedUrl)->assertRedirect(route('cart.empty'));
        $this->assertNotEmpty(session('cart'));
    }

    public function test_marking_whatsapp_opened_and_sent_does_not_claim_delivery(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'phone' => '9876543210',
            'whatsapp_marketing_consent' => true,
        ]);
        $communication = CustomerCommunication::query()->create([
            'user_id' => $customer->id,
            'admin_user_id' => $admin->id,
            'channel' => 'whatsapp',
            'category' => 'thank_you',
            'status' => 'prepared',
            'message_content' => 'Hi Customer, thank you.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.customer-communications.open-whatsapp', $communication))
            ->assertRedirectContains('https://wa.me/919876543210');

        $this->assertSame('opened', $communication->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.customer-communications.mark-sent', $communication))
            ->assertRedirect();

        $this->assertSame('marked_sent', $communication->fresh()->status);
    }
}
