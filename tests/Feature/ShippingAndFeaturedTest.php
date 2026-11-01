<?php

namespace Tests\Feature;

use App\Models\FeaturedCampaign;
use App\Models\Order;
use App\Models\Product;
use App\Services\FeaturedCampaignService;
use App\Services\SellerShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShippingAndFeaturedTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_shipping_templates_calculate_and_snapshot_by_seller(): void
    {
        $product = Product::query()->where('is_published', true)->firstOrFail();
        $vendor = $product->vendor;
        $service = app(SellerShippingService::class);
        $profile = $service->profile($vendor);
        $profile->zones()->where('coverage_type', 'nationwide')->update(['base_charge' => 120]);

        $result = $service->calculate([['vendor_id' => $vendor->id, 'product_id' => $product->id, 'price' => 100, 'quantity' => 1]], '600001', []);

        $this->assertSame(40, $result['amount']);
        $this->assertSame('available', $result['status']);
        $order = Order::query()->create(['order_number' => 'SSTESTSHIP001', 'customer_name' => 'Shipping Test', 'customer_phone' => '9876543210', 'address_line_1' => 'Street', 'city' => 'Chennai', 'pincode' => '600001', 'subtotal' => 100, 'shipping_amount' => 40, 'tax_amount' => 0, 'total_amount' => 140, 'status' => 'payment_pending', 'payment_method' => 'unselected', 'payment_status' => 'pending']);
        $service->snapshot($order, $result, ['address_line_1' => 'Street', 'pincode' => '600001']);
        $this->assertDatabaseHas('order_shipping_snapshots', ['order_id' => $order->id, 'vendor_id' => $vendor->id, 'shipping_charge' => 40]);
    }

    public function test_featured_campaign_verifies_exact_razorpay_payment_and_blocks_second_weekly_campaign(): void
    {
        $product = Product::query()->where('is_published', true)->firstOrFail();
        $vendor = $product->vendor;
        config(['services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'rzp_test_secret']);
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response(['id' => 'order_featured_1'], 200),
            'https://api.razorpay.com/v1/payments/*' => Http::response(['amount' => 9900, 'currency' => 'INR', 'status' => 'captured'], 200),
        ]);
        $service = app(FeaturedCampaignService::class);
        $campaign = $service->createPayment($vendor, $product);
        $paymentId = 'pay_featured_1';
        $signature = hash_hmac('sha256', $campaign->razorpay_order_id.'|'.$paymentId, 'rzp_test_secret');
        $service->verify($vendor, $campaign, ['razorpay_payment_id' => $paymentId, 'razorpay_order_id' => 'order_featured_1', 'razorpay_signature' => $signature]);
        $this->assertDatabaseHas('featured_campaigns', ['id' => $campaign->id, 'status' => 'active', 'payment_status' => 'paid']);
        $this->expectException(ValidationException::class);
        $service->createPayment($vendor, $product);
    }
}
