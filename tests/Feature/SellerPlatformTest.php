<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerStorefrontCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use App\Services\SellerCommissionService;
use App\Services\SettlementBatchService;
use App\Services\SettlementCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SellerPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_storefront_exposes_compact_become_a_seller_paths(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Become a Seller')
            ->assertSee(route('seller.login'))
            ->assertSee('Start Selling with Zero Upfront Cost');
    }

    public function test_seller_dashboard_redirects_incomplete_seller_to_onboarding(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('seller.onboarding'));

        $this->assertDatabaseHas('vendors', [
            'user_id' => $seller->id,
            'status' => Vendor::STATUS_ACTIVE,
            'store_status' => Vendor::STORE_SETUP_REQUIRED,
        ]);
    }

    public function test_onboarding_persists_business_identity_and_policy_plan(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);

        $this->actingAs($seller)
            ->post(route('seller.onboarding.store'), [
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
            ])
            ->assertRedirect(route('seller.dashboard'));

        $vendor = $seller->fresh()->vendor;
        $this->assertSame('Koti Seller Co', $vendor->business_name);
        $this->assertSame('complete', $vendor->onboarding_status);
        $this->assertSame(Vendor::STORE_LIVE, $vendor->store_status);
    }

    public function test_seller_product_rejects_used_condition_and_keeps_form_simple(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'onboarding_step' => 8,
            'current_plan' => Vendor::PLAN_FREE,
            'selected_plan' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
        ])->save();
        $category = Category::query()->firstOrFail();
        $storefrontCategory = $vendor->storefrontCategories()->create([
            'category_id' => $category->id,
            'name' => 'Seller Electronics',
            'slug' => 'seller-electronics',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.products.create'))
            ->assertOk()
            ->assertSee('Price and Stock')
            ->assertSee('Offer Price')
            ->assertDontSee('Pricing and Earnings')
            ->assertDontSee('Estimated Seller Earnings')
            ->assertSee('Seller Electronics');

        $this->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'seller_storefront_category_id' => $storefrontCategory->id,
                'name' => 'Used Bad Product',
                'brand' => 'Seller Brand',
                'short_description' => 'Should not pass condition rules.',
                'mrp' => 1000,
                'selling_price' => 900,
                'buying_price' => 500,
                'seller_status' => 'approved',
                'go_live_mode' => 'publish_now',
                'product_condition' => 'used',
                'package_contents' => 'Box',
                'stock' => 5,
                'low_stock_threshold' => 2,
                'images' => [UploadedFile::fake()->image('product.jpg')],
            ])
            ->assertSessionHasErrors('product_condition');

        $this->assertSame(Vendor::PLAN_FREE, $vendor->current_plan);
    }

    public function test_commission_priority_and_paid_plan_zero_commission(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'onboarding_step' => 8,
            'current_plan' => Vendor::PLAN_FREE,
            'selected_plan' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
        ])->save();
        $category = Category::query()->firstOrFail();
        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Commission Product',
            'slug' => 'commission-product',
            'brand' => 'Seller',
            'short_description' => 'Commission product',
            'full_description' => 'Commission product',
            'mrp' => 1200,
            'selling_price' => 1000,
            'seller_status' => 'active',
            'product_condition' => 'new',
            'package_contents' => 'Box',
            'low_stock_threshold' => 2,
        ]);

        CommissionRule::query()->create(['category_id' => $category->id, 'type' => 'percentage', 'rate' => 12, 'is_active' => true]);
        CommissionRule::query()->create(['product_id' => $product->id, 'type' => 'percentage', 'rate' => 8, 'is_active' => true]);

        $preview = app(SellerCommissionService::class)->preview($vendor, $product, 1000, 2);
        $this->assertSame(2, $preview['commission']);
        $this->assertSame('free_plan_unit_commission', $preview['source']);

        app(SellerAccountService::class)->activatePlan($vendor, Vendor::PLAN_GROWTH, 'manual-test');
        $paidPreview = app(SellerCommissionService::class)->preview($vendor->fresh(), $product, 1000, 2);
        $this->assertSame(0, $paidPreview['commission']);
    }

    public function test_platform_fee_is_one_rupee_per_eligible_product_quantity(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill(['current_plan' => Vendor::PLAN_FREE, 'plan_status' => Vendor::PLAN_ACTIVE])->save();

        $preview = app(SellerCommissionService::class)->preview($vendor, null, 500, 5);

        $this->assertSame(5, $preview['eligible_quantity']);
        $this->assertSame(1, $preview['platform_fee_per_unit']);
        $this->assertSame(5, $preview['platform_fee_total']);
        $this->assertSame(2495, $preview['seller_earning']);
    }

    public function test_paid_plan_zero_fee_and_expired_paid_plan_returns_to_free_fee(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $service = app(SellerCommissionService::class);

        $vendor->forceFill(['current_plan' => Vendor::PLAN_GROWTH, 'plan_expires_at' => now()->addDay()])->save();
        $this->assertSame(0, $service->preview($vendor->fresh(), null, 1000, 3)['platform_fee_total']);

        $vendor->forceFill(['current_plan' => Vendor::PLAN_GROWTH, 'plan_expires_at' => now()->subDay(), 'grace_ends_at' => null])->save();
        $this->assertSame(3, $service->preview($vendor->fresh(), null, 1000, 3)['platform_fee_total']);
    }

    public function test_settlement_generation_uses_snapshots_and_prevents_duplicate_items(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $seller = User::factory()->create(['role' => User::ROLE_SELLER]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill(['current_plan' => Vendor::PLAN_FREE, 'plan_status' => Vendor::PLAN_ACTIVE])->save();
        $category = Category::query()->firstOrFail();
        $product = Product::query()->create(['vendor_id' => $vendor->id, 'category_id' => $category->id, 'name' => 'Settlement Product', 'slug' => 'settlement-product', 'brand' => 'Sushako', 'short_description' => 'Test', 'full_description' => 'Test', 'mrp' => 1000, 'selling_price' => 1000, 'seller_status' => 'approved', 'product_condition' => 'new', 'package_contents' => 'Box', 'low_stock_threshold' => 1]);
        $order = Order::query()->create(['order_number' => 'SSSETTLE001', 'user_id' => $customer->id, 'customer_name' => $customer->name, 'customer_phone' => '9876543210', 'customer_email' => $customer->email, 'address_line_1' => 'Street', 'city' => 'Chennai', 'pincode' => '600001', 'subtotal' => 3000, 'shipping_amount' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total_amount' => 3000, 'status' => 'delivered', 'payment_method' => 'razorpay', 'payment_status' => 'paid']);
        $item = $order->items()->create(['vendor_id' => $vendor->id, 'product_id' => $product->id, 'product_name' => 'Settlement Product', 'product_slug' => 'settlement-product', 'colour' => 'Standard', 'size' => 'Standard', 'quantity' => 3, 'unit_price' => 1000, 'line_total' => 3000, 'gross_line_amount' => 3000, 'eligible_quantity' => 3, 'retained_quantity' => 3, 'platform_fee_per_unit' => 1, 'platform_fee_total' => 3, 'seller_earning' => 2997, 'settlement_status' => 'upcoming']);

        $settlement = app(SettlementBatchService::class)->create($vendor, null, null, $admin->id);

        $this->assertSame(3000.0, (float) $settlement->gross_amount);
        $this->assertSame(3.0, (float) $settlement->platform_fee_amount);
        $this->assertSame(2997.0, (float) $settlement->final_settlement_amount);
        $this->assertSame('pending_approval', $item->fresh()->settlement_status);
        $this->assertSame(0, app(SettlementCalculationService::class)->eligibleItemsQuery($vendor)->count());
    }

    public function test_seller_customer_search_stays_scoped_to_current_vendor(): void
    {
        $sellerA = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $sellerB = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $customerA = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'name' => 'Seller A Buyer']);
        $customerB = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'name' => 'Seller B Buyer']);
        $vendorA = app(SellerAccountService::class)->ensureVendor($sellerA);
        $vendorB = app(SellerAccountService::class)->ensureVendor($sellerB);
        $vendorA->forceFill(['onboarding_status' => 'complete', 'dashboard_access_enabled' => true])->save();
        $vendorB->forceFill(['onboarding_status' => 'complete', 'dashboard_access_enabled' => true])->save();
        $category = Category::query()->firstOrFail();
        $productA = Product::query()->create(['vendor_id' => $vendorA->id, 'category_id' => $category->id, 'name' => 'Seller A Product', 'slug' => 'seller-a-product', 'brand' => 'Seller', 'short_description' => 'Test', 'full_description' => 'Test', 'mrp' => 1000, 'selling_price' => 1000, 'seller_status' => 'approved', 'product_condition' => 'new', 'package_contents' => 'Box', 'low_stock_threshold' => 1]);
        $productB = Product::query()->create(['vendor_id' => $vendorB->id, 'category_id' => $category->id, 'name' => 'Seller B Product', 'slug' => 'seller-b-product', 'brand' => 'Seller', 'short_description' => 'Test', 'full_description' => 'Test', 'mrp' => 1000, 'selling_price' => 1000, 'seller_status' => 'approved', 'product_condition' => 'new', 'package_contents' => 'Box', 'low_stock_threshold' => 1]);
        $orderA = Order::query()->create(['order_number' => 'SSAONLY001', 'user_id' => $customerA->id, 'customer_name' => 'Shared Search Buyer', 'customer_phone' => '9000000001', 'customer_email' => 'a@example.test', 'address_line_1' => 'Street', 'city' => 'Chennai', 'pincode' => '600001', 'subtotal' => 1000, 'shipping_amount' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total_amount' => 1000, 'status' => 'delivered', 'payment_method' => 'cod', 'payment_status' => 'pending']);
        $orderB = Order::query()->create(['order_number' => 'SSBONLY001', 'user_id' => $customerB->id, 'customer_name' => 'Shared Search Buyer', 'customer_phone' => '9000000002', 'customer_email' => 'b@example.test', 'address_line_1' => 'Street', 'city' => 'Chennai', 'pincode' => '600001', 'subtotal' => 1000, 'shipping_amount' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total_amount' => 1000, 'status' => 'delivered', 'payment_method' => 'cod', 'payment_status' => 'pending']);
        $orderA->items()->create(['vendor_id' => $vendorA->id, 'product_id' => $productA->id, 'product_name' => $productA->name, 'product_slug' => $productA->slug, 'colour' => 'Standard', 'size' => 'Standard', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000, 'gross_line_amount' => 1000, 'eligible_quantity' => 1, 'retained_quantity' => 1, 'platform_fee_per_unit' => 1, 'platform_fee_total' => 1, 'seller_earning' => 999, 'settlement_status' => 'upcoming']);
        $orderB->items()->create(['vendor_id' => $vendorB->id, 'product_id' => $productB->id, 'product_name' => $productB->name, 'product_slug' => $productB->slug, 'colour' => 'Standard', 'size' => 'Standard', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000, 'gross_line_amount' => 1000, 'eligible_quantity' => 1, 'retained_quantity' => 1, 'platform_fee_per_unit' => 1, 'platform_fee_total' => 1, 'seller_earning' => 999, 'settlement_status' => 'upcoming']);

        $this->actingAs($sellerA)
            ->get(route('seller.customers.index', ['q' => 'Shared Search Buyer']))
            ->assertOk()
            ->assertSee('9000000001')
            ->assertDontSee('9000000002')
            ->assertSee(route('seller.customers.show', 'user-'.$customerA->id))
            ->assertSee('Search by customer name or mobile number...');

        $this->actingAs($sellerA)
            ->get(route('seller.customers.show', 'user-'.$customerA->id))
            ->assertOk()
            ->assertSee('My Store Customer')
            ->assertSee('href="tel:+919000000001"', false)
            ->assertSee('Orders with Your Store')
            ->assertSee('SSAONLY001')
            ->assertDontSee('SSBONLY001')
            ->assertDontSee('9000000002');

        $this->actingAs($sellerA)
            ->get(route('seller.customers.show', 'user-'.$customerB->id))
            ->assertNotFound();
    }

    public function test_seller_order_isolation(): void
    {
        [$sellerA, $sellerB] = [User::factory()->create(['role' => User::ROLE_SELLER]), User::factory()->create(['role' => User::ROLE_SELLER])];
        [$vendorA, $vendorB] = [app(SellerAccountService::class)->ensureVendor($sellerA), app(SellerAccountService::class)->ensureVendor($sellerB)];
        foreach ([$vendorA, $vendorB] as $vendor) {
            $vendor->forceFill([
                'onboarding_status' => 'complete',
                'onboarding_step' => 8,
                'current_plan' => Vendor::PLAN_FREE,
                'selected_plan' => Vendor::PLAN_FREE,
                'plan_status' => Vendor::PLAN_ACTIVE,
                'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
                'dashboard_access_enabled' => true,
            ])->save();
        }
        $category = Category::query()->firstOrFail();
        $productA = Product::query()->create(['vendor_id' => $vendorA->id, 'category_id' => $category->id, 'name' => 'Seller A Product', 'slug' => 'seller-a-product', 'brand' => 'A', 'short_description' => 'A', 'full_description' => 'A', 'mrp' => 1000, 'selling_price' => 900, 'seller_status' => 'active', 'product_condition' => 'new', 'package_contents' => 'Box', 'low_stock_threshold' => 1]);
        $productB = Product::query()->create(['vendor_id' => $vendorB->id, 'category_id' => $category->id, 'name' => 'Seller B Product', 'slug' => 'seller-b-product', 'brand' => 'B', 'short_description' => 'B', 'full_description' => 'B', 'mrp' => 1000, 'selling_price' => 900, 'seller_status' => 'active', 'product_condition' => 'new', 'package_contents' => 'Box', 'low_stock_threshold' => 1]);
        $order = Order::query()->create(['order_number' => 'SSSELLER001', 'user_id' => null, 'customer_name' => 'Customer', 'customer_phone' => '9876543210', 'customer_email' => 'customer@example.test', 'address_line_1' => 'Street', 'city' => 'Chennai', 'pincode' => '600001', 'subtotal' => 1800, 'shipping_amount' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total_amount' => 1800, 'status' => 'placed', 'payment_method' => 'cod', 'payment_status' => 'pending']);
        $order->items()->create(['vendor_id' => $vendorA->id, 'product_id' => $productA->id, 'product_name' => 'Seller A Product', 'product_slug' => 'seller-a-product', 'colour' => 'Standard', 'size' => 'Standard', 'quantity' => 1, 'unit_price' => 900, 'line_total' => 900, 'gross_line_amount' => 900, 'seller_earning' => 810]);
        $order->items()->create(['vendor_id' => $vendorB->id, 'product_id' => $productB->id, 'product_name' => 'Seller B Product', 'product_slug' => 'seller-b-product', 'colour' => 'Standard', 'size' => 'Standard', 'quantity' => 1, 'unit_price' => 900, 'line_total' => 900, 'gross_line_amount' => 900, 'seller_earning' => 810]);

        $this->actingAs($sellerA)->get(route('seller.orders.show', $order->order_number))->assertOk()->assertSee('Seller A Product')->assertDontSee('Seller B Product');
    }

    public function test_seller_directly_manages_own_storefront_categories(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'current_plan' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
        ])->save();
        $category = Category::query()->firstOrFail();
        $vendor->categories()->sync([$category->id]);

        $this->actingAs($seller)
            ->get(route('seller.categories.index'))
            ->assertOk()
            ->assertSee('Storefront Categories')
            ->assertSee('Create Category')
            ->assertSee('Marketplace Categories')
            ->assertDontSee('Request New Marketplace Category');

        $this->post(route('seller.categories.subcategories.store'), [
            'category_id' => '',
            'name' => 'Budget Laptops',
            'slug' => 'budget-laptops',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
            'display_order' => 1,
            'description' => 'Seller laptop deals.',
            'image' => UploadedFile::fake()->image('category.jpg'),
        ])->assertRedirect();

        $storefrontCategory = SellerStorefrontCategory::query()->where('vendor_id', $vendor->id)->firstOrFail();
        Storage::disk('public')->assertExists($storefrontCategory->image_path);
        $this->assertNull($storefrontCategory->category_id);
        $this->assertSame('budget-laptops', $storefrontCategory->slug);

        $this->put(route('seller.categories.subcategories.update', $storefrontCategory), [
            'category_id' => $category->id,
            'name' => 'Budget Laptop Deals',
            'slug' => 'budget-laptop-deals',
            'status' => SellerStorefrontCategory::STATUS_HIDDEN,
            'display_order' => 2,
            'description' => 'Updated seller laptop deals.',
        ])->assertRedirect();

        $this->assertDatabaseHas('seller_storefront_categories', [
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Budget Laptop Deals',
            'slug' => 'budget-laptop-deals',
            'status' => SellerStorefrontCategory::STATUS_HIDDEN,
        ]);
    }

    public function test_seller_store_profile_logo_upload_validation(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'current_plan' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
        ])->save();

        $payload = [
            'business_name' => 'Logo Seller Co',
            'store_display_name' => 'Logo Seller',
            'owner_name' => 'Logo Owner',
            'business_category' => 'Electronics',
            'email' => 'logo-seller@example.test',
            'phone' => '9123456780',
            'store_description' => 'A seller profile with validated logo uploads.',
            'store_tagline' => 'Validated uploads',
            'website' => 'https://seller.example.test',
            'gst_status' => 'not_registered',
            'gstin' => null,
            'pan_number' => null,
            'legal_name' => 'Logo Seller Legal',
        ];

        $this->actingAs($seller)
            ->from(route('seller.store-profile'))
            ->put(route('seller.store-profile.update'), $payload + [
                'logo' => UploadedFile::fake()->create('not-image.pdf', 12, 'application/pdf'),
            ])
            ->assertRedirect(route('seller.store-profile'))
            ->assertSessionHasErrors('logo');

        $this->put(route('seller.store-profile.update'), $payload + [
            'logo' => UploadedFile::fake()->image('seller-logo.png'),
        ])->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->assertNotNull($vendor->fresh()->business_logo_path);
        Storage::disk('public')->assertExists($vendor->fresh()->business_logo_path);
    }

    public function test_seller_cannot_edit_another_sellers_category(): void
    {
        [$sellerA, $sellerB] = [
            User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]),
            User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]),
        ];
        $vendorA = app(SellerAccountService::class)->ensureVendor($sellerA);
        $vendorB = app(SellerAccountService::class)->ensureVendor($sellerB);
        foreach ([$vendorA, $vendorB] as $vendor) {
            $vendor->forceFill([
                'onboarding_status' => 'complete',
                'current_plan' => Vendor::PLAN_FREE,
                'plan_status' => Vendor::PLAN_ACTIVE,
                'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
                'dashboard_access_enabled' => true,
            ])->save();
        }
        $category = Category::query()->firstOrFail();
        $vendorA->categories()->sync([$category->id]);
        $vendorB->categories()->sync([$category->id]);
        $storefrontCategory = $vendorA->storefrontCategories()->create([
            'category_id' => $category->id,
            'name' => 'Seller A Category',
            'slug' => 'seller-a-category',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ]);

        $this->actingAs($sellerB)
            ->put(route('seller.categories.subcategories.update', $storefrontCategory), [
                'category_id' => $category->id,
                'name' => 'Hijacked Category',
                'status' => SellerStorefrontCategory::STATUS_ACTIVE,
                'display_order' => 1,
            ])
            ->assertNotFound();
    }

    public function test_seller_product_allows_optional_subcategory_but_rejects_other_sellers_category(): void
    {
        Storage::fake('public');
        [$sellerA, $sellerB] = [
            User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]),
            User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]),
        ];
        $vendorA = app(SellerAccountService::class)->ensureVendor($sellerA);
        $vendorB = app(SellerAccountService::class)->ensureVendor($sellerB);
        foreach ([$vendorA, $vendorB] as $vendor) {
            $vendor->forceFill([
                'onboarding_status' => 'complete',
                'current_plan' => Vendor::PLAN_FREE,
                'plan_status' => Vendor::PLAN_ACTIVE,
                'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
                'dashboard_access_enabled' => true,
            ])->save();
        }
        $category = Category::query()->firstOrFail();
        $vendorA->categories()->sync([$category->id]);
        $vendorB->categories()->sync([$category->id]);
        $ownCategory = $vendorA->storefrontCategories()->create([
            'category_id' => $category->id,
            'name' => 'Own Storefront Category',
            'slug' => 'own-storefront-category',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ]);
        $otherCategory = $vendorB->storefrontCategories()->create([
            'category_id' => $category->id,
            'name' => 'Other Storefront Category',
            'slug' => 'other-storefront-category',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ]);

        $this->actingAs($sellerA)
            ->get(route('seller.products.create'))
            ->assertOk()
            ->assertSee('Own Storefront Category')
            ->assertDontSee('Other Storefront Category');

        $payload = [
            'category_id' => $category->id,
            'name' => 'Seller Scoped Product',
            'brand' => 'Seller Brand',
            'short_description' => 'Scoped category assignment.',
            'mrp' => 1000,
            'selling_price' => 900,
            'buying_price' => 500,
            'seller_status' => 'draft',
            'go_live_mode' => 'publish_now',
            'product_condition' => 'new',
            'package_contents' => 'Box',
            'stock' => 5,
            'low_stock_threshold' => 2,
            'images' => [UploadedFile::fake()->image('product.jpg')],
        ];

        $this->post(route('seller.products.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendorA->id,
            'seller_storefront_category_id' => null,
            'name' => 'Seller Scoped Product',
        ]);

        $payload['name'] = 'Seller Scoped Product With Other Category';

        $this->post(route('seller.products.store'), $payload + [
            'seller_storefront_category_id' => $otherCategory->id,
        ])->assertSessionHasErrors('seller_storefront_category_id');

        $payload['name'] = 'Seller Scoped Product With Own Category';

        $this->post(route('seller.products.store'), $payload + [
            'seller_storefront_category_id' => $ownCategory->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendorA->id,
            'seller_storefront_category_id' => $ownCategory->id,
            'name' => 'Seller Scoped Product With Own Category',
        ]);
    }

    public function test_seller_cannot_delete_category_with_attached_products(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->ensureVendor($seller);
        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'current_plan' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
        ])->save();
        $category = Category::query()->firstOrFail();
        $vendor->categories()->sync([$category->id]);
        $storefrontCategory = $vendor->storefrontCategories()->create([
            'category_id' => $category->id,
            'name' => 'Laptop Deals',
            'slug' => 'laptop-deals',
            'status' => SellerStorefrontCategory::STATUS_ACTIVE,
        ]);
        Product::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'seller_storefront_category_id' => $storefrontCategory->id,
            'name' => 'Lenovo Test Laptop',
            'slug' => 'lenovo-test-laptop',
            'brand' => 'Lenovo',
            'short_description' => 'Test laptop',
            'full_description' => 'Test laptop',
            'mrp' => 11000,
            'selling_price' => 9000,
            'seller_status' => 'active',
            'product_condition' => 'new',
            'package_contents' => 'Laptop',
            'low_stock_threshold' => 1,
        ]);

        $this->actingAs($seller)
            ->from(route('seller.categories.index'))
            ->delete(route('seller.categories.subcategories.destroy', $storefrontCategory))
            ->assertRedirect(route('seller.categories.index'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('seller_storefront_categories', ['id' => $storefrontCategory->id]);
    }

    public function test_simplified_onboarding_completes_and_sends_seller_to_dashboard(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE, 'phone' => null]);

        $this->actingAs($seller)
            ->post(route('seller.onboarding.store'), [
                'business_name' => 'Local Koti Store',
                'business_address' => '10 Market Street',
                'postal_code' => '600001',
                'phone' => '9123456789',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'gst_registered' => 'no',
                'pan_number' => 'ABCDE1234F',
                'pickup_same_as_business' => '1',
                'delivery_radius' => 10,
                'flat_shipping_charge' => 50,
                'free_shipping_threshold' => 999,
                'returns_accepted' => 'no',
            ])
            ->assertRedirect(route('seller.dashboard'))
            ->assertSessionDoesntHaveErrors();

        $vendor = $seller->fresh()->vendor;
        $this->assertSame('complete', $vendor->onboarding_status);
        $this->assertSame('not_registered', $vendor->gst_status);
        $this->assertSame('ABCDE1234F', $vendor->pan_number);
        $this->assertSame(Vendor::STORE_LIVE, $vendor->store_status);
        $this->assertSame(Vendor::VISIBILITY_PUBLISHED, $vendor->store_visibility);
        $this->assertSame(10, $vendor->delivery_radius);
        $this->assertSame('No Returns', $vendor->return_policy_summary);
    }

    public function test_first_product_can_use_marketplace_category_without_storefront_category(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->activateStarter(app(SellerAccountService::class)->ensureVendor($seller));
        $category = Category::query()->firstOrFail();

        $this->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Local Test Product',
                'brand' => 'Local Brand',
                'short_description' => 'Simple seller product.',
                'mrp' => 1000,
                'selling_price' => 900,
                'buying_price' => 500,
                'seller_status' => 'approved',
                'go_live_mode' => 'publish_now',
                'product_condition' => 'new',
                'package_contents' => 'Box',
                'stock' => 5,
                'low_stock_threshold' => 2,
                'sku' => 'LOCAL-TEST-001',
                'images' => [UploadedFile::fake()->image('product.jpg')],
            ])
            ->assertRedirect(route('seller.dashboard'))
            ->assertSessionHas('status', 'Your first product is ready.');

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'seller_storefront_category_id' => null,
            'name' => 'Local Test Product',
            'seller_status' => Product::SELLER_STATUS_ACTIVE,
            'is_published' => true,
        ]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'LOCAL-TEST-001', 'stock' => 5]);
    }

    public function test_seller_can_request_new_marketplace_category(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->activateStarter(app(SellerAccountService::class)->ensureVendor($seller));

        $this->actingAs($seller)
            ->post(route('seller.category-requests.store'), [
                'requested_category_name' => 'Temple Supplies',
                'description' => 'Pooja items and brass lamps.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Category request sent to Sushako for review.');

        $this->assertDatabaseHas('seller_category_requests', [
            'vendor_id' => $vendor->id,
            'requested_category_name' => 'Temple Supplies',
            'status' => 'pending_approval',
        ]);
    }

    public function test_seller_subcategory_duplicate_check_normalizes_case_and_spaces(): void
    {
        $seller = User::factory()->create(['role' => User::ROLE_SELLER, 'status' => User::STATUS_ACTIVE]);
        $vendor = app(SellerAccountService::class)->activateStarter(app(SellerAccountService::class)->ensureVendor($seller));
        $category = Category::query()->firstOrFail();

        $this->actingAs($seller)
            ->post(route('seller.categories.subcategories.store'), [
                'category_id' => $category->id,
                'name' => 'Mobile Cover',
                'status' => SellerStorefrontCategory::STATUS_ACTIVE,
            ])
            ->assertRedirect();

        $this->withoutExceptionHandling();
        try {
            $this->post(route('seller.categories.subcategories.store'), [
                'category_id' => $category->id,
                'name' => ' mobile   cover ',
                'status' => SellerStorefrontCategory::STATUS_ACTIVE,
            ]);

            $this->fail('Duplicate seller subcategory was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }

        $this->assertSame(1, $vendor->storefrontCategories()->whereRaw('LOWER(TRIM(name)) = ?', ['mobile cover'])->count());
    }
}
