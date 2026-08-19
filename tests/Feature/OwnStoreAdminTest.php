<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OfficialStoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnStoreAdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_super_admin_can_login_and_open_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@example.test',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $superAdmin->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Operations Center')
            ->assertSee('Approval Center')
            ->assertSee('Logout');
    }

    public function test_admin_entry_point_handles_guests_and_existing_customer_sessions(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@example.test',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->get(route('admin'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk()->assertSee('Super Admin Login');

        $this->actingAs($customer)
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Super Admin Login');

        $this->post(route('admin.login.store'), [
            'email' => $superAdmin->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
        $this->get(route('admin'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_non_super_admin_cannot_use_admin_login_or_admin_routes(): void
    {
        $customerOnly = User::factory()->create([
            'email' => 'customer-only@example.test',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->post(route('admin.login.store'), [
            'email' => $customerOnly->email,
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_seller_routes_are_available_but_seller_dashboard_is_protected(): void
    {
        $this->get('/seller/login')->assertOk()->assertSee('Continue with Google');
        $this->get('/seller/dashboard')->assertRedirect(route('seller.login'));
        $this->get('/sell')->assertNotFound();
    }

    public function test_admin_own_store_operation_pages_are_protected_and_render(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $urls = [
            route('admin.categories.index'),
            route('admin.approvals.index'),
            route('admin.categories.create'),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.inventory.index'),
            route('admin.orders.index'),
            route('admin.customers.index'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }

        foreach ($urls as $url) {
            $this->actingAs($superAdmin)->get($url)->assertOk();
        }
    }

    public function test_super_admin_can_create_edit_and_delete_database_product_with_strict_images(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $category = Category::query()->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Launch Ready Product',
                'brand' => 'Sushako',
                'short_description' => 'A real saved product for launch testing.',
                'mrp' => 1999,
                'selling_price' => 1499,
                'status' => 'published',
                'colour' => 'Black',
                'colour_hex' => '#111111',
                'size' => 'One Size',
                'stock' => 12,
                'images' => [
                    UploadedFile::fake()->image('launch-product.jpg', 900, 1200),
                ],
            ])
            ->assertRedirect();

        $product = Product::query()->where('slug', 'launch-ready-product')->firstOrFail();

        $this->assertSame(OfficialStoreService::SLUG, $product->vendor()->firstOrFail()->slug);
        $this->assertSame(12, $product->variants()->firstOrFail()->stock);
        Storage::disk('public')->assertExists($product->images()->firstOrFail()->path);

        $this->actingAs($superAdmin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Bad Upload Product',
                'brand' => 'Sushako',
                'short_description' => 'Should reject non-image upload.',
                'mrp' => 999,
                'selling_price' => 799,
                'status' => 'published',
                'colour' => 'Black',
                'size' => 'One Size',
                'stock' => 3,
                'images' => [
                    UploadedFile::fake()->create('not-an-image.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertSessionHasErrors('images.0');

        $variant = $product->variants()->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('admin.products.update', $product->slug), [
                'category_id' => $category->id,
                'name' => 'Launch Ready Product Updated',
                'slug' => $product->slug,
                'brand' => 'Sushako',
                'short_description' => 'Updated saved product.',
                'mrp' => 1999,
                'selling_price' => 1299,
                'status' => 'published',
                'colour' => 'Black',
                'size' => 'One Size',
                'stock' => 12,
                'variants' => [
                    $variant->id => [
                        'colour' => 'Black',
                        'colour_hex' => '#111111',
                        'size' => 'One Size',
                        'stock' => 7,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(7, $variant->refresh()->stock);
        $this->assertSame(1299, $product->refresh()->selling_price);

        $this->actingAs($superAdmin)
            ->delete(route('admin.products.destroy', $product->slug))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('products', ['slug' => 'launch-ready-product']);
    }

    public function test_super_admin_can_update_manual_order_shipping_workflow(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $order = Order::query()->create([
            'order_number' => 'SS'.now()->format('Ym').'00001',
            'customer_name' => 'Admin Buyer',
            'customer_phone' => '9876543210',
            'address_line_1' => 'Door 12',
            'city' => 'Chennai',
            'pincode' => '600001',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'placed_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('New Orders')
            ->assertSee('Packing')
            ->assertSee('In Transit')
            ->assertSee('Delivered')
            ->assertSee($order->order_number)
            ->assertSee('Immediate Action')
            ->assertSee('Open')
            ->assertDontSee('Manual shipping workflow')
            ->assertDontSee('WhatsApp Customer');

        $this->actingAs($superAdmin)
            ->get(route('admin.orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Manual shipping workflow')
            ->assertSee('WhatsApp Customer')
            ->assertSee('DTDC');

        $this->actingAs($superAdmin)
            ->put(route('admin.orders.workflow.update', $order->order_number), [
                'status' => 'shipped',
                'shipping_provider' => 'dtdc',
                'tracking_number' => 'DTDC12345',
            ])->assertRedirect();

        $order->refresh();

        $this->assertSame('shipped', $order->status);
        $this->assertSame('dtdc', $order->shipping_provider);
        $this->assertSame('DTDC12345', $order->tracking_number);
        $this->assertNotNull($order->packed_at);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_customer_account_still_works(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.test',
            'phone' => '9876500000',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->post(route('login.store'), [
            'email' => $customer->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('account.show'));

        $this->get(route('account.show'))->assertOk()->assertSee('Customer Account');
    }

    public function test_customer_account_completion_needs_photo_whatsapp_and_default_address(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create([
            'email' => 'complete@example.test',
            'phone' => '9876500000',
            'phone_is_whatsapp' => false,
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($customer)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('Tickets')
            ->assertSee('Logout')
            ->assertDontSee('100% complete');

        $this->actingAs($customer)
            ->post(route('account.profile.update'), [
                '_method' => 'PUT',
                'name' => 'Complete Customer',
                'phone' => '9876500000',
                'phone_is_whatsapp' => '1',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
            ])
            ->assertRedirect();

        $customer->addresses()->create([
            'label' => 'Home',
            'recipient_name' => 'Complete Customer',
            'phone' => '9876500000',
            'address_line_1' => 'Door 10',
            'city' => 'Chengalpattu',
            'pincode' => '603001',
            'is_default' => true,
        ]);

        $this->actingAs($customer->refresh())
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('100% complete')
            ->assertSee('Mobile confirmed as WhatsApp number')
            ->assertSee('Profile picture')
            ->assertSee('Default delivery address');

        Storage::disk('public')->assertExists($customer->refresh()->avatar_path);
        $this->assertTrue($customer->phone_is_whatsapp);
    }
}
