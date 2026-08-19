<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerOnboardingPayment;
use App\Models\SellerPlan;
use App\Models\SellerPlanPayment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SellerOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_incomplete_seller_is_redirected_to_onboarding(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($seller)->get(route('seller.dashboard'))->assertRedirect(route('seller.onboarding'));
    }

    public function test_onboarding_uses_one_simple_business_setup_page(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($seller)->get(route('seller.onboarding'))
            ->assertOk()
            ->assertSee('Business Details')
            ->assertSee('Yes, I have GST')
            ->assertSee('No GST')
            ->assertSee('Pickup address is same as business address')
            ->assertSee('Delivery Setup')
            ->assertSee('Delivery Radius')
            ->assertSee('Return Policy')
            ->assertSee("Let's Go to My Shop", false)
            ->assertDontSee('Choose Plan')
            ->assertDontSee('Quick Action')
            ->assertDontSee('seller-mobile-bottom-nav');
    }

    public function test_google_seller_can_complete_business_details(): void
    {
        $seller = User::factory()->create([
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_ACTIVE,
            'google_id' => 'google-seller-123',
            'phone' => null,
        ]);

        $this->actingAs($seller)
            ->post(route('seller.onboarding.store'), [
                'business_name' => 'Google Seller Co',
                'business_address' => '24 Google Street',
                'phone' => '9123456789',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'postal_code' => '600042',
                'gst_registered' => 'no',
                'pan_number' => 'ABCDE1234F',
                'pickup_same_as_business' => '1',
                'delivery_radius' => 10,
                'flat_shipping_charge' => 40,
                'free_shipping_threshold' => 999,
                'returns_accepted' => 'no',
            ])
            ->assertRedirect(route('seller.dashboard'))
            ->assertSessionDoesntHaveErrors();

        $this->assertSame('9123456789', $seller->fresh()->phone);
        $this->assertSame('Google Seller Co', $seller->fresh()->vendor->business_name);
        $this->assertSame('No Returns', $seller->fresh()->vendor->return_policy_summary);
    }

    public function test_business_setup_activates_starter_access_and_goes_to_first_product(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $this->completeBusinessAndDelivery($seller);

        $vendor = $seller->fresh()->vendor;
        $this->assertSame('complete', $vendor->onboarding_status);
        $this->assertSame(Vendor::PLAN_FREE, $vendor->current_plan);
        $this->assertSame(Vendor::PAYMENT_NOT_REQUIRED, $vendor->payment_status);
        $this->assertSame(Vendor::VISIBILITY_PUBLISHED, $vendor->store_visibility);
        $this->assertSame(Vendor::APPROVAL_ONBOARDING_COMPLETED, $vendor->approval_status);
        $this->assertSame(10, (int) $vendor->delivery_radius);
        $this->assertSame('10 Market Street', $vendor->pickup_address_line_1);
        $this->assertFalse($vendor->returns_accepted);
        $this->assertNull($vendor->plan_expires_at);

        $this->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Welcome to Koti Seller Co')
            ->assertSee('Add Your First Product')
            ->assertSee('View My Store')
            ->assertSee('Add your bank details to receive settlements.')
            ->assertSee('Daily Work')
            ->assertSee('Settlement Summary');
    }

    public function test_old_onboarding_step_urls_return_to_simple_setup(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($seller)->get(route('seller.onboarding', ['step' => 3]))
            ->assertOk()
            ->assertSee('Your shop is almost ready.')
            ->assertDontSee('seller-plan-card-production');
    }

    public function test_onboarding_plan_selection_routes_are_retired(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($seller)->get(route('seller.onboarding.plan'))->assertRedirect(route('seller.onboarding'));
        $this->post(route('seller.onboarding.plan.select'))->assertRedirect(route('seller.onboarding'));
        $this->get(route('seller.onboarding.review'))->assertRedirect(route('seller.onboarding'));
    }

    public function test_paid_renewal_extends_validity_by_another_30_days(): void
    {
        config(['services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'rzp_test_secret']);
        Http::fake(['api.razorpay.com/*' => Http::response(['id' => 'order_renew_123'], 200)]);

        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'dashboard_access_enabled' => true,
            'current_plan' => Vendor::PLAN_GROWTH,
            'selected_plan' => Vendor::PLAN_GROWTH,
            'selected_plan_slug' => Vendor::PLAN_GROWTH,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_PAID,
            'plan_activated_at' => now(),
            'plan_expires_at' => now()->addDays(10),
        ])->save();

        $this->actingAs($seller)->post(route('seller.plans.renew'))->assertRedirect(route('seller.onboarding.payment'));
        $payment = SellerOnboardingPayment::query()->where('vendor_id', $vendor->id)->firstOrFail();
        $signature = hash_hmac('sha256', 'order_renew_123|pay_renew', 'rzp_test_secret');

        $this->postJson(route('seller.onboarding.payment.confirm'), [
            'payment_id' => $payment->id,
            'razorpay_payment_id' => 'pay_renew',
            'razorpay_order_id' => 'order_renew_123',
            'razorpay_signature' => $signature,
        ])->assertOk();

        $this->assertSame(40, (int) now()->startOfDay()->diffInDays($vendor->fresh()->plan_expires_at->copy()->startOfDay(), false));
    }

    public function test_seller_plans_page_uses_vertical_payment_cards(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $this->completeBusinessAndDelivery($seller);

        $this->get(route('seller.plans.index'))
            ->assertOk()
            ->assertSee('seller-subscription-page')
            ->assertSee('seller-plan-option-card')
            ->assertSee('Current Free Plan')
            ->assertSee('Select &amp; Pay', false)
            ->assertSee('name="plan" value="growth"', false)
            ->assertSee('name="plan" value="enterprise"', false)
            ->assertSee('Razorpay checkout');
    }

    public function test_seller_can_select_a_paid_plan_from_plans_page_and_continue_payment(): void
    {
        config(['services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'rzp_test_secret']);
        Http::fake(['api.razorpay.com/*' => Http::response(['id' => 'order_enterprise_from_plans'], 200)]);

        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'dashboard_access_enabled' => true,
            'current_plan' => Vendor::PLAN_FREE,
            'selected_plan' => Vendor::PLAN_FREE,
            'selected_plan_slug' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'plan_activated_at' => now(),
            'plan_expires_at' => null,
        ])->save();

        $this->actingAs($seller)
            ->post(route('seller.plans.renew'), ['plan' => Vendor::PLAN_ENTERPRISE])
            ->assertRedirect(route('seller.onboarding.payment'));

        Http::assertSent(fn ($request) => $request['amount'] === 499900 && $request['notes']['plan'] === Vendor::PLAN_ENTERPRISE);

        $this->assertDatabaseHas('seller_onboarding_payments', [
            'vendor_id' => $vendor->id,
            'plan' => Vendor::PLAN_ENTERPRISE,
            'amount' => 4999,
            'provider_order_id' => 'order_enterprise_from_plans',
            'status' => SellerOnboardingPayment::STATUS_PENDING,
        ]);

        $this->assertSame(Vendor::PLAN_ENTERPRISE, $vendor->fresh()->selected_plan_slug);
        $this->assertSame(Vendor::PLAN_PENDING_PAYMENT, $vendor->fresh()->plan_status);
    }

    public function test_dashboard_pending_items_link_to_editable_sections(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $this->completeBusinessAndDelivery($seller);

        $this->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee(route('seller.store-profile'))
            ->assertSee(route('seller.settlements.index'))
            ->assertSee(route('seller.settings.index'))
            ->assertDontSee(route('seller.categories.index'));
    }

    public function test_store_setup_sections_are_editable(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $this->completeBusinessAndDelivery($seller);
        $category = Category::query()->firstOrFail();

        $this->put(route('seller.categories.update'), ['categories' => [$category->id]])->assertRedirect();
        $this->assertSame(1, $seller->fresh()->vendor->categories()->count());

        $this->put(route('seller.settings.update'), [
            'policies' => array_keys(SellerAccountService::POLICIES),
            'tax_preference' => 'gst_included',
            'store_visibility' => Vendor::VISIBILITY_DRAFT,
            'working_days' => ['mon', 'tue'],
            'opens_at' => '09:00',
            'closes_at' => '18:00',
        ])->assertRedirect();

        $this->assertSame('gst_included', $seller->fresh()->vendor->tax_preference);
    }

    public function test_store_readiness_boundaries_control_publishing(): void
    {
        $accounts = app(SellerAccountService::class);
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = $accounts->ensureVendor($seller);
        $category = Category::query()->firstOrFail();

        $vendor->forceFill([
            'business_name' => 'Boundary Store',
            'store_display_name' => 'Boundary Store',
            'store_description' => 'Readiness boundary store.',
            'phone' => '9876543210',
            'email' => 'boundary@example.test',
            'address_line_1' => '10 Market Street',
            'city' => 'Chennai',
            'postal_code' => '600001',
            'pickup_address' => '10 Market Street, Chennai, 600001',
            'delivery_radius' => 8,
            'shipping_commitment' => 'self_shipping',
            'working_days' => ['mon'],
            'onboarding_status' => 'draft',
            'current_plan' => null,
            'plan_status' => Vendor::PLAN_PENDING_PAYMENT,
        ])->save();

        $vendor->storefrontCategories()->create(['category_id' => $category->id, 'name' => 'Boundary Category', 'slug' => 'boundary-category', 'status' => 'active']);
        foreach ([1, 2, 3] as $index) {
            Product::query()->create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'name' => 'Boundary Product '.$index,
                'slug' => 'boundary-product-'.$index,
                'brand' => 'Boundary',
                'short_description' => 'Boundary product.',
                'full_description' => 'Boundary product.',
                'mrp' => 100,
                'selling_price' => 100,
                'seller_status' => 'draft',
                'product_condition' => 'new',
                'package_contents' => 'Box',
                'low_stock_threshold' => 1,
            ]);
        }

        $this->assertSame(74, $accounts->storeSetupChecklist($vendor->fresh())['percentage']);
        $this->assertFalse($accounts->publish($vendor->fresh()));

        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'current_plan' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
        ])->save();

        $readiness = $accounts->storeSetupChecklist($vendor->fresh());
        $this->assertGreaterThanOrEqual(75, $readiness['percentage']);
        $this->assertTrue($readiness['publish_eligible']);
        $this->assertTrue($accounts->publish($vendor->fresh()));
        $this->assertSame(Vendor::STORE_LIVE, $vendor->fresh()->store_status);
        $this->assertSame(Vendor::VISIBILITY_PUBLISHED, $vendor->fresh()->store_visibility);
    }

    public function test_settings_page_renders_stable_policy_and_working_hours_sections(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $this->completeBusinessAndDelivery($seller);

        $this->actingAs($seller)
            ->get(route('seller.settings.index'))
            ->assertOk()
            ->assertSee('Legal Policies')
            ->assertSee('Working Hours')
            ->assertSee('seller-policy-list')
            ->assertSee('seller-working-hours-grid');
    }

    public function test_database_plans_are_seeded_as_active_records(): void
    {
        $this->assertDatabaseHas('seller_plans', ['slug' => 'free', 'price' => 0, 'commission_type' => 'flat', 'commission_value' => 1, 'status' => SellerPlan::STATUS_ACTIVE]);
        $this->assertDatabaseHas('seller_plans', ['slug' => 'growth', 'price' => 999, 'billing_period' => 'monthly', 'status' => SellerPlan::STATUS_ACTIVE]);
        $this->assertDatabaseHas('seller_plans', ['slug' => 'enterprise', 'price' => 4999, 'billing_period' => 'monthly', 'status' => SellerPlan::STATUS_ACTIVE]);
    }

    public function test_duplicate_paid_activation_does_not_duplicate_same_payment_record(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $payment = SellerOnboardingPayment::query()->create(['vendor_id' => $vendor->id, 'plan' => Vendor::PLAN_ENTERPRISE, 'plan_name_snapshot' => 'Enterprise', 'amount' => 4999, 'provider_order_id' => 'order_webhook', 'provider_payment_id' => 'pay_webhook', 'status' => 'paid', 'verified_at' => now()]);

        app(SellerAccountService::class)->activatePaidPlan($vendor, $payment);
        app(SellerAccountService::class)->activatePaidPlan($vendor->fresh(), $payment->fresh());

        $this->assertSame(1, SellerPlanPayment::query()->where('vendor_id', $vendor->id)->where('payment_reference', 'pay_webhook')->count());
    }

    private function completeBusinessAndDelivery(User $seller): void
    {
        $this->actingAs($seller);
        $this->post(route('seller.onboarding.store'), $this->simpleBusinessPayload())
            ->assertRedirect(route('seller.dashboard'));
    }

    private function simpleBusinessPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Koti Seller Co',
            'business_address' => '10 Market Street',
            'phone' => '9876543210',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'postal_code' => '600001',
            'gst_registered' => 'no',
            'pan_number' => 'ABCDE1234F',
            'pickup_same_as_business' => '1',
            'delivery_radius' => 10,
            'flat_shipping_charge' => 40,
            'free_shipping_threshold' => 999,
            'returns_accepted' => 'no',
        ], $overrides);
    }
}
