<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SellerStorefrontCategory;
use App\Models\TaxSlab;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use App\Services\SellerCommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, SellerAccountService $accounts): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.products.index', [
            'vendor' => $vendor,
            'products' => $vendor->products()
                ->with(['category', 'sellerStorefrontCategory', 'images', 'variants'])
                ->when($request->query('search'), fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
                ->when($request->query('category'), fn ($query, string $category) => $query->where('category_id', $category))
                ->when($request->query('status'), fn ($query, string $status) => $query->where('seller_status', $status))
                ->when($request->query('stock') === 'low', fn ($query) => $query->whereHas('variants', fn ($variant) => $variant->whereColumn('product_variants.stock', '<=', 'products.low_stock_threshold')))
                ->when($request->query('stock') === 'out', fn ($query) => $query->whereHas('variants', fn ($variant) => $variant->where('stock', '<=', 0)))
                ->latest()
                ->paginate(16)
                ->withQueryString(),
            'categories' => $accounts->sellerCategories(),
        ]);
    }

    public function create(Request $request, SellerAccountService $accounts, SellerCommissionService $commission): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.products.form', [
            'vendor' => $vendor,
            'product' => new Product(['seller_status' => Product::SELLER_STATUS_DRAFT, 'product_condition' => 'new']),
            'categories' => $accounts->sellerCategories(),
            'storefrontCategories' => $this->assignableStorefrontCategories($vendor),
            'taxSlabs' => TaxSlab::query()->where('is_active', true)->orderBy('rate')->get(),
            'pricingPreview' => $commission->preview($vendor, null, 1000),
        ]);
    }

    public function store(Request $request, SellerAccountService $accounts, SellerCommissionService $commission): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $this->validated($request, $vendor);
        $status = $this->publishableStatus($vendor, $data['seller_status']);
        if (($data['go_live_mode'] ?? 'publish_now') === 'schedule') {
            $status = Product::SELLER_STATUS_SCHEDULED;
        }
        $isFirstProduct = ! $vendor->products()->exists();

        $product = $vendor->products()->create($this->payload($data, $status));
        $variant = $product->variants()->create([
            'sku' => filled($data['sku'] ?? null) ? $data['sku'] : $this->uniqueSku($product),
            'colour' => $data['colour'] ?? 'Standard',
            'colour_hex' => $data['colour_hex'] ?? '#d8ccb9',
            'size' => $data['size'] ?? 'Standard',
            'stock' => $data['stock'],
            'price' => $data['selling_price'],
        ]);

        foreach ($request->file('images', []) as $index => $file) {
            $product->images()->create([
                'path' => $accounts->storeUploadedFile($file, 'seller-products'),
                'label' => $index === 0 ? 'Primary View' : 'Product View',
                'sort_order' => $index,
            ]);
        }

        $commission->preview($vendor, $product, (int) $product->selling_price, 1);

        if ($isFirstProduct) {
            return redirect()->route('seller.dashboard')->with('status', 'Your first product is ready.');
        }

        return redirect()->route('seller.products.edit', $product)->with('status', "Product saved. SKU {$variant->sku} is ready.");
    }

    public function edit(Request $request, Product $product, SellerAccountService $accounts, SellerCommissionService $commission): View
    {
        $vendor = $request->attributes->get('vendor');
        $this->authorizeProduct($vendor, $product);

        return view('seller.products.form', [
            'vendor' => $vendor,
            'product' => $product->load(['images', 'variants']),
            'categories' => $accounts->sellerCategories(),
            'storefrontCategories' => $this->assignableStorefrontCategories($vendor),
            'taxSlabs' => TaxSlab::query()->where('is_active', true)->orderBy('rate')->get(),
            'pricingPreview' => $commission->preview($vendor, $product, (int) $product->selling_price),
        ]);
    }

    public function update(Request $request, Product $product, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $this->authorizeProduct($vendor, $product);
        $data = $this->validated($request, $vendor, $product);
        $status = $this->publishableStatus($vendor, $data['seller_status'], $product);
        if (($data['go_live_mode'] ?? 'publish_now') === 'schedule') {
            $status = Product::SELLER_STATUS_SCHEDULED;
        }

        $product->fill($this->payload($data, $status, $product))->save();
        $variant = $product->variants()->first();
        $variant?->fill([
            'sku' => filled($data['sku'] ?? null) ? $data['sku'] : $variant->sku,
            'colour' => $data['colour'] ?? $variant->colour,
            'colour_hex' => $data['colour_hex'] ?? $variant->colour_hex,
            'size' => $data['size'] ?? $variant->size,
            'stock' => $data['stock'],
            'price' => $data['selling_price'],
        ])->save();

        foreach ($request->file('images', []) as $index => $file) {
            $product->images()->create([
                'path' => $accounts->storeUploadedFile($file, 'seller-products'),
                'label' => $index === 0 && ! $product->images()->exists() ? 'Primary View' : 'Product View',
                'sort_order' => (int) $product->images()->max('sort_order') + 1,
            ]);
        }

        return back()->with('status', 'Product updated.');
    }

    public function destroyImage(Request $request, ProductImage $image): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $this->authorizeProduct($vendor, $image->product);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Product image removed.');
    }

    private function validated(Request $request, Vendor $vendor, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)->where('available_to_sellers', true)],
            'seller_storefront_category_id' => [
                'nullable',
                'integer',
                Rule::exists('seller_storefront_categories', 'id')
                    ->where('vendor_id', $vendor->id)
                    ->whereIn('status', [SellerStorefrontCategory::STATUS_ACTIVE, SellerStorefrontCategory::STATUS_DRAFT]),
            ],
            'tax_slab_id' => ['nullable', 'integer', 'exists:tax_slabs,id'],
            'name' => ['required', 'string', 'max:140'],
            'brand' => ['nullable', 'string', 'max:120'],
            'short_description' => ['required', 'string', 'max:500'],
            'full_description' => ['nullable', 'string', 'max:3000'],
            'mrp' => ['nullable', 'integer', 'min:1', 'gte:selling_price'],
            'buying_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:1'],
            'seller_status' => ['required', Rule::in(['draft', 'approved', 'inactive'])],
            'go_live_mode' => ['required', Rule::in(['publish_now', 'schedule'])],
            'go_live_at' => ['required_if:go_live_mode,schedule', 'nullable', 'date', 'after:now'],
            'seller_rejection_reason' => ['nullable', 'string', 'max:1000'],
            'continue_selling_when_out_of_stock' => ['nullable', 'boolean'],
            'product_condition' => ['required', Rule::in(['new', 'refurbished', 'factory_refurbished', 'seller_refurbished', 'open_box'])],
            'refurbishment_type' => ['required_unless:product_condition,new', 'nullable', 'string', 'max:80'],
            'refurbishment_grade' => ['required_unless:product_condition,new', 'nullable', Rule::in(['A', 'B', 'C'])],
            'condition_description' => ['required_unless:product_condition,new', 'nullable', 'string', 'max:1200'],
            'cosmetic_condition' => ['required_unless:product_condition,new', 'nullable', 'string', 'max:160'],
            'testing_details' => ['required_unless:product_condition,new', 'nullable', 'string', 'max:1200'],
            'warranty_period' => ['nullable', 'string', 'max:80'],
            'package_contents' => ['nullable', 'string', 'max:1000'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('product_variants', 'sku')->ignore($product?->variants()->first()?->id)],
            'colour' => ['nullable', 'string', 'max:80'],
            'colour_hex' => ['nullable', 'string', 'max:20'],
            'size' => ['nullable', 'string', 'max:80'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['brand'] = filled($data['brand'] ?? null) ? $data['brand'] : ($vendor->store_display_name ?: $vendor->business_name);
        $data['mrp'] = max((int) ($data['mrp'] ?? $data['selling_price']), (int) $data['selling_price']);
        $data['package_contents'] = filled($data['package_contents'] ?? null) ? $data['package_contents'] : 'Product package';
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 2;
        $data['return_policy'] = $vendor->return_policy_summary ?: 'No Returns';

        $vendor->categories()->syncWithoutDetaching([$data['category_id']]);

        return $data;
    }

    private function sellerCategoriesFallback(): int
    {
        return Category::query()
            ->where('is_active', true)
            ->where('available_to_sellers', true)
            ->orderBy('name')
            ->value('id') ?: abort(422, 'No seller marketplace categories are available.');
    }

    private function payload(array $data, string $status, ?Product $product = null): array
    {
        return [
            'category_id' => $data['category_id'],
            'seller_storefront_category_id' => $data['seller_storefront_category_id'] ?? null,
            'tax_slab_id' => $data['tax_slab_id'] ?? null,
            'name' => $data['name'],
            'slug' => $product?->slug ?: $this->uniqueSlug($data['name']),
            'collection' => 'Seller Marketplace',
            'subcategory' => 'Seller Listed',
            'brand' => $data['brand'],
            'badge' => $data['product_condition'] === 'new' ? 'Seller Pick' : 'Refurbished',
            'short_description' => $data['short_description'],
            'full_description' => $data['full_description'] ?? $data['short_description'],
            'return_policy' => $data['return_policy'],
            'mrp' => $data['mrp'],
            'selling_price' => $data['selling_price'],
            'buying_price' => $data['buying_price'],
            'is_published' => in_array($status, [Product::SELLER_STATUS_ACTIVE, Product::SELLER_STATUS_SCHEDULED], true),
            'local_delivery' => true,
            'fulfillment_scope' => 'Seller Self-Shipping',
            'seller_status' => $status,
            'scheduled_go_live_at' => $status === Product::SELLER_STATUS_SCHEDULED ? $data['go_live_at'] : null,
            'published_mode' => $status === Product::SELLER_STATUS_SCHEDULED ? 'schedule' : 'publish_now',
            'seller_rejection_reason' => $data['seller_rejection_reason'] ?? null,
            'continue_selling_when_out_of_stock' => (bool) ($data['continue_selling_when_out_of_stock'] ?? false),
            'product_condition' => $data['product_condition'],
            'refurbishment_type' => $data['refurbishment_type'] ?? null,
            'refurbishment_grade' => $data['refurbishment_grade'] ?? null,
            'condition_description' => $data['condition_description'] ?? null,
            'cosmetic_condition' => $data['cosmetic_condition'] ?? null,
            'testing_details' => $data['testing_details'] ?? null,
            'warranty_period' => $data['warranty_period'] ?? null,
            'package_contents' => $data['package_contents'],
            'low_stock_threshold' => $data['low_stock_threshold'],
        ];
    }

    private function publishableStatus(Vendor $vendor, string $requested, ?Product $product = null): string
    {
        if ($requested === 'approved') {
            $requested = Product::SELLER_STATUS_ACTIVE;
        }

        if ($requested !== Product::SELLER_STATUS_ACTIVE) {
            return $requested;
        }

        if ($vendor->current_plan === Vendor::PLAN_GROWTH) {
            $activeCount = $vendor->products()->where('seller_status', Product::SELLER_STATUS_ACTIVE)->when($product, fn ($query) => $query->whereKeyNot($product->id))->count();

            if ($activeCount >= 100) {
                return Product::SELLER_STATUS_DRAFT;
            }
        }

        return Product::SELLER_STATUS_ACTIVE;
    }

    private function authorizeProduct(Vendor $vendor, Product $product): void
    {
        abort_unless($product->vendor_id === $vendor->id, 404);
    }

    private function assignableStorefrontCategories(Vendor $vendor)
    {
        return $vendor->storefrontCategories()
            ->whereIn('status', [SellerStorefrontCategory::STATUS_ACTIVE, SellerStorefrontCategory::STATUS_DRAFT])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'seller-product';
        $slug = $base;
        $count = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }

    private function uniqueSku(Product $product): string
    {
        return 'SELLER-'.$product->id.'-'.strtoupper(Str::random(6));
    }
}
