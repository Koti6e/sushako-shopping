<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InvoiceSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TaxSlab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_super_admin_can_manage_operational_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $slab = TaxSlab::query()->where('rate', 18)->firstOrFail();
        $category = Category::query()->firstOrFail();
        $product = Product::query()->firstOrFail();

        $this->actingAs($admin)->get(route('admin.settings.company'))
            ->assertOk()
            ->assertSee('Central business identity');

        $this->actingAs($admin)->put(route('admin.settings.company.update'), [
            'company_name' => 'Sushako Shopping',
            'legal_business_name' => 'Sushako Retail Private Limited',
            'gstin' => '33ABCDE1234F1Z5',
            'address_line_1' => 'Store Street',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600001',
            'country' => 'India',
            'support_email' => 'support@sushako.test',
            'support_phone' => '9000000000',
            'website' => 'https://sushako.test',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'business_hours' => 'Monday to Saturday, 10 AM to 7 PM',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.settings.invoice.update'), [
            'invoice_prefix' => 'SSINV',
            'next_invoice_number' => 10,
            'invoice_footer' => 'Thank you for shopping with Sushako.',
            'terms_conditions' => 'Standard Sushako terms apply.',
            'authorized_signatory_name' => 'Store Admin',
            'authorized_signatory_designation' => 'Owner',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.settings.shipping.update'), [
            'free_shipping_enabled' => '1',
            'free_shipping_threshold' => 1500,
            'shipping_policy_text' => 'Delivery charges applicable for lower value orders.',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.settings.payments.update'), [
            'providers' => [
                'cod' => ['enabled' => '1', 'environment' => 'sandbox'],
                'razorpay' => ['environment' => 'sandbox', 'key_placeholder' => 'RAZORPAY_KEY_ID', 'webhook_url_placeholder' => 'RAZORPAY_WEBHOOK_URL'],
                'future_provider' => ['environment' => 'sandbox'],
            ],
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.settings.tax.assignments.update'), [
            'categories' => [$category->id => ['tax_slab_id' => $slab->id]],
            'products' => [$product->id => ['tax_slab_id' => $slab->id]],
        ])->assertRedirect();

        $this->assertDatabaseHas('company_settings', ['legal_business_name' => 'Sushako Retail Private Limited']);
        $this->assertDatabaseHas('invoice_settings', ['invoice_prefix' => 'SSINV', 'next_invoice_number' => 10]);
        $this->assertDatabaseHas('shipping_settings', ['free_shipping_threshold' => 1500]);
        $this->assertDatabaseHas('payment_settings', ['provider' => 'cod', 'enabled' => true]);
        $this->assertSame($slab->id, $category->refresh()->tax_slab_id);
        $this->assertSame($slab->id, $product->refresh()->tax_slab_id);
    }

    public function test_checkout_uses_gst_inclusive_tax_and_configured_shipping_rules(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'phone' => '9888888888',
        ]);
        $slab = TaxSlab::query()->where('rate', 18)->firstOrFail();
        $category = Category::query()->where('slug', 'electronics')->firstOrFail();
        $category->forceFill(['tax_slab_id' => $slab->id])->save();

        Product::query()->where('slug', 'lenovo-100e-celeron-laptop')->firstOrFail()
            ->forceFill(['tax_slab_id' => null])
            ->save();

        $this->actingAs($customer)->post(route('cart.store'), [
            'slug' => 'lenovo-100e-celeron-laptop',
            'colour' => 'Standard',
            'size' => '500GB HDD',
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $this->actingAs($customer)->get(route('checkout'))
            ->assertOk()
            ->assertSee('GST:')
            ->assertSee('Free');

        $this->actingAs($customer)->post(route('checkout.place'), [
            'customer_name' => 'Tax Customer',
            'customer_phone' => '9888888888',
            'customer_email' => 'tax@example.test',
            'address_line_1' => 'Door 1',
            'city' => 'Chennai',
            'pincode' => '600001',
            'terms' => '1',
        ])->assertRedirect();

        $order = Order::query()->with('items')->firstOrFail();
        $item = $order->items->first();

        $this->assertSame(9000, $order->subtotal);
        $this->assertSame(0, $order->shipping_amount);
        $this->assertSame('free', $order->shipping_status);
        $this->assertSame(1373, $order->tax_amount);
        $this->assertEquals(18.0, (float) $item->gst_rate);
        $this->assertSame($order->tax_amount, $order->cgst_amount + $order->sgst_amount);
    }

    public function test_invoice_numbers_are_assigned_sequentially_from_settings(): void
    {
        InvoiceSetting::query()->first()->forceFill([
            'invoice_prefix' => 'SSINV',
            'next_invoice_number' => 25,
        ])->save();

        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'phone' => '9777777777',
        ]);
        $variant = ProductVariant::query()->where('sku', 'SS-STAGE-001')->firstOrFail();

        $orders = collect([1, 2])->map(function (int $index) use ($customer, $variant): Order {
            $order = Order::query()->create([
                'order_number' => 'SS'.now()->format('Ym').str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'address_line_1' => 'Door '.$index,
                'city' => 'Chennai',
                'pincode' => '600001',
                'subtotal' => 1,
                'shipping_amount' => null,
                'shipping_status' => 'delivery_charges_applicable',
                'total_amount' => 1,
                'status' => 'placed',
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'placed_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => 'Sushako Staging Sample Product',
                'product_slug' => 'sushako-razorpay-test-product',
                'colour' => 'Standard',
                'size' => 'Standard',
                'quantity' => 1,
                'unit_price' => 1,
                'line_total' => 1,
            ]);

            return $order;
        });

        $this->actingAs($customer)->get(route('order.invoice', $orders[0]->order_number))->assertOk();
        $this->actingAs($customer)->get(route('order.invoice', $orders[1]->order_number))->assertOk();

        $this->assertSame('SSINV-00025', $orders[0]->refresh()->invoice_number);
        $this->assertSame('SSINV-00026', $orders[1]->refresh()->invoice_number);
        $this->assertSame(27, InvoiceSetting::query()->first()->next_invoice_number);
    }
}
