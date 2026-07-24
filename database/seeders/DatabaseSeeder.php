<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\InvoiceSetting;
use App\Models\Product;
use App\Models\ShippingSetting;
use App\Models\TaxSlab;
use App\Models\User;
use App\Services\OperationalSettingsService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = env('SUSHAKO_DEV_PASSWORD', 'Password123!');

        $this->deactivateLegacyMarketplaceAccounts();
        $this->seedOperationalSettings();

        $this->seedUser(env('SUSHAKO_SUPER_ADMIN_EMAIL', 'superadmin@sushako.test'), 'Sushako Super Admin', '9000000001', User::ROLE_SUPER_ADMIN, $password);
        $this->seedUser(env('SUSHAKO_CUSTOMER_EMAIL', 'customer@sushako.test'), 'Sushako Customer', '9000000002', User::ROLE_CUSTOMER, $password);
        $this->seedCatalog();
    }

    private function deactivateLegacyMarketplaceAccounts(): void
    {
        User::query()
            ->whereIn('role', ['admin', 'vendor'])
            ->update(['status' => User::STATUS_INACTIVE]);

        if (Schema::hasTable('vendors')) {
            DB::table('vendors')->update([
                'status' => User::STATUS_INACTIVE,
                'onboarding_status' => 'inactive',
            ]);
        }
    }

    private function seedUser(string $email, string $name, string $phone, string $role, string $password): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $phoneOwner = User::query()
            ->where('phone', $phone)
            ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
            ->exists();

        $user->fill([
            'name' => $name,
            'phone' => $phoneOwner ? $user->phone : $phone,
            'password' => Hash::make($password),
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ])->save();
    }

    private function seedCatalog(): void
    {
        DB::table('products')->where('slug', 'sushako-razorpay-test-product')->delete();
        Category::query()
            ->where('slug', 'testing')
            ->whereDoesntHave('products')
            ->delete();

        foreach ($this->catalogCategories() as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->assignCategoryTaxSlabs();

        $this->seedStagingProduct();
        $this->seedLenovoLaptop();
    }

    private function seedOperationalSettings(): void
    {
        $settings = app(OperationalSettingsService::class);

        CompanySetting::query()->firstOrCreate([], $settings->defaultCompany());
        InvoiceSetting::query()->firstOrCreate([], $settings->defaultInvoice());
        ShippingSetting::query()->firstOrCreate([], $settings->defaultShipping());
        $settings->payments();

        foreach ([0, 5, 12, 18, 28] as $rate) {
            TaxSlab::query()->firstOrCreate([
                'name' => "GST {$rate}%",
                'rate' => $rate,
            ], [
                'is_active' => true,
            ]);
        }
    }

    private function assignCategoryTaxSlabs(): void
    {
        $slabs = TaxSlab::query()->get()->keyBy(fn (TaxSlab $slab): string => (string) (float) $slab->rate);

        foreach ([
            'home-made-health-mix' => 5,
            'masala-powders' => 5,
            'womens-clothing' => 5,
            'electronics' => 18,
        ] as $slug => $rate) {
            Category::query()->where('slug', $slug)->update([
                'tax_slab_id' => $slabs->get((string) (float) $rate)?->id,
            ]);
        }
    }

    private function catalogCategories(): array
    {
        return [
            [
                'name' => 'Home Made Health Mix',
                'slug' => 'home-made-health-mix',
                'tagline' => 'Homemade nutrition',
                'headline' => 'Traditional health mix for daily strength',
                'description' => 'Fresh, home-style health mix powders prepared for family nutrition, breakfast routines and everyday wellness.',
                'image' => 'assets/banners/home-made-health-mix.jpg',
                'accent' => '#496d47',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Masala Powders',
                'slug' => 'masala-powders',
                'tagline' => 'Fresh aroma',
                'headline' => 'Small-batch masala powders',
                'description' => 'Aromatic spice powders for everyday Indian cooking, prepared with a focus on freshness, flavour and trusted ingredients.',
                'image' => 'assets/banners/masala-powders.jpg',
                'accent' => '#9b3a1d',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'tagline' => 'Smart essentials',
                'headline' => 'Mobiles, laptops and everyday electronics',
                'description' => 'Useful electronics for work, entertainment and daily life, including laptops, mobiles, headsets and smart watches.',
                'image' => 'assets/banners/electronics.jpg',
                'accent' => '#263e68',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => "Women's Clothing",
                'slug' => 'womens-clothing',
                'tagline' => 'Curated style',
                'headline' => "Women's clothing for every occasion",
                'description' => "Elegant women's clothing selections with polished styling, clear details and Sushako support.",
                'image' => 'assets/banners/womens-clothing.jpg',
                'accent' => '#a54569',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }

    private function seedStagingProduct(): void
    {
        $category = Category::query()->where('slug', 'home-made-health-mix')->firstOrFail();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Sushako Staging Sample Product',
            'slug' => 'sushako-razorpay-test-product',
            'collection' => 'Payment Testing',
            'subcategory' => 'Staging Sample',
            'brand' => 'Sushako',
            'badge' => 'Staging',
            'short_description' => 'A one rupee staging product for checking cart, order creation and payment gateway flow before launch.',
            'full_description' => 'This staging sample product is kept only to confirm checkout, order ID, invoice and payment gateway behavior before real products are published.',
            'fabric' => 'Staging Sample',
            'fit' => 'Standard',
            'sleeve' => 'Not Applicable',
            'pattern' => 'Sample',
            'occasion' => 'Gateway Check',
            'country_of_origin' => 'India',
            'return_policy' => 'Not for customer sale',
            'mrp' => 1,
            'selling_price' => 1,
            'rating' => 5.0,
            'reviews' => 1,
            'is_published' => true,
            'is_new' => true,
            'is_best_seller' => false,
            'local_delivery' => true,
            'fulfillment_scope' => 'Sushako Fulfillment',
            'sort_order' => 999,
        ]);

        foreach ([
            ['path' => 'assets/banners/home-made-health-mix.jpg', 'label' => 'Primary View'],
            ['path' => 'assets/banners/masala-powders.jpg', 'label' => 'Ingredient View'],
            ['path' => 'assets/banners/womens-clothing.jpg', 'label' => 'Store View'],
        ] as $index => $image) {
            $product->images()->create([
                'path' => $image['path'],
                'label' => $image['label'],
                'sort_order' => $index + 1,
            ]);
        }

        $product->variants()->create([
            'sku' => 'SS-STAGE-001',
            'colour' => 'Standard',
            'colour_hex' => '#d8ccb9',
            'size' => 'Standard',
            'stock' => 99,
        ]);
    }

    private function seedLenovoLaptop(): void
    {
        $category = Category::query()->where('slug', 'electronics')->firstOrFail();
        $product = Product::query()->updateOrCreate([
            'slug' => 'lenovo-100e-celeron-laptop',
        ], [
            'category_id' => $category->id,
            'name' => 'Lenovo 100e Celeron Laptop',
            'collection' => 'Sushako Budget Tech',
            'subcategory' => 'Budgeted Laptop',
            'brand' => 'Lenovo',
            'badge' => 'Budget Pick',
            'short_description' => 'Lenovo 100e Celeron laptop with 4GB RAM and a choice of 500GB HDD or 500GB SSD storage.',
            'full_description' => 'A practical Lenovo 100e Celeron laptop for students, browsing, online classes, billing work and everyday office use. Choose the 500GB HDD model for the lowest price or the 500GB SSD model for faster startup and smoother daily performance.',
            'fabric' => 'Intel Celeron',
            'fit' => '4GB RAM',
            'sleeve' => '500GB HDD or 500GB SSD',
            'pattern' => 'Lenovo 100e compact laptop',
            'occasion' => 'Students, browsing and office basics',
            'country_of_origin' => 'India',
            'return_policy' => 'Sushako support with invoice after order',
            'mrp' => 11000,
            'selling_price' => 9000,
            'rating' => 4.7,
            'reviews' => 8,
            'is_published' => true,
            'is_new' => true,
            'is_best_seller' => true,
            'local_delivery' => true,
            'fulfillment_scope' => 'Sushako Electronics Fulfillment',
            'sort_order' => 10,
        ]);

        $product->images()->delete();

        foreach ([
            ['path' => 'assets/products/lenovo-100e/lenovo-100e-01.jpg', 'label' => 'Open front view'],
            ['path' => 'assets/products/lenovo-100e/lenovo-100e-02.jpg', 'label' => 'Side ports view'],
            ['path' => 'assets/products/lenovo-100e/lenovo-100e-03.jpg', 'label' => 'Touch display angle'],
            ['path' => 'assets/products/lenovo-100e/lenovo-100e-04.jpg', 'label' => 'Hinge profile view'],
            ['path' => 'assets/products/lenovo-100e/lenovo-100e-05.jpg', 'label' => 'Top lid view'],
        ] as $index => $image) {
            $product->images()->create([
                'path' => $image['path'],
                'label' => $image['label'],
                'sort_order' => $index + 1,
            ]);
        }

        foreach ([
            ['sku' => 'SS-LEN100E-HDD', 'size' => '500GB HDD', 'price' => 9000],
            ['sku' => 'SS-LEN100E-SSD', 'size' => '500GB SSD', 'price' => 11000],
        ] as $variant) {
            $product->variants()->updateOrCreate([
                'sku' => $variant['sku'],
            ], [
                'colour' => 'Standard',
                'colour_hex' => '#d8ccb9',
                'size' => $variant['size'],
                'option_name' => 'Storage',
                'option_value' => $variant['size'],
                'price' => $variant['price'],
                'stock' => 5,
            ]);
        }
    }
}
