<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerCategoryRequest;
use App\Models\SellerStorefrontCategory;
use App\Services\SellerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request, SellerAccountService $accounts): View
    {
        $vendor = $request->attributes->get('vendor')->loadMissing(['categories', 'storefrontCategories.marketplaceCategory']);
        $filters = $request->validate([
            'category_search' => ['nullable', 'string', 'max:120'],
            'category_status' => ['nullable', Rule::in([
                SellerStorefrontCategory::STATUS_ACTIVE,
                SellerStorefrontCategory::STATUS_DRAFT,
                SellerStorefrontCategory::STATUS_HIDDEN,
            ])],
        ]);

        $storefrontCategories = $vendor->storefrontCategories()
            ->with('marketplaceCategory')
            ->withCount('products')
            ->when($filters['category_search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($filters['category_status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('seller.categories.index', [
            'vendor' => $vendor,
            'marketplaceCategories' => $accounts->sellerCategories(),
            'marketplaceMappingCounts' => $vendor->storefrontCategories()
                ->whereNotNull('category_id')
                ->selectRaw('category_id, count(*) as aggregate')
                ->groupBy('category_id')
                ->pluck('aggregate', 'category_id'),
            'storefrontCategories' => $storefrontCategories,
            'filters' => $filters,
        ]);
    }

    public function updateMarketplace(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)->where('available_to_sellers', true)],
        ]);

        $categoryIds = $data['categories'] ?? [];
        $vendor->categories()->sync($categoryIds);
        $accounts->audit($vendor, 'seller_marketplace_categories_updated', 'Seller updated marketplace category mapping.', ['category_ids' => $categoryIds]);

        return back()->with('status', 'Marketplace category mapping saved.');
    }

    public function requestMarketplace(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'requested_category_name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $existing = $vendor->categoryRequests()
            ->where('status', SellerCategoryRequest::STATUS_PENDING)
            ->whereRaw('LOWER(requested_category_name) = ?', [strtolower($data['requested_category_name'])])
            ->exists();

        if ($existing) {
            return back()->with('status', 'This category request is already pending review.');
        }

        $vendor->categoryRequests()->create([
            'requested_category_name' => $data['requested_category_name'],
            'description' => $data['description'] ?? null,
            'status' => SellerCategoryRequest::STATUS_PENDING,
        ]);

        $accounts->audit($vendor, 'seller_category_requested', 'Seller requested a new marketplace category.', [
            'requested_category_name' => $data['requested_category_name'],
        ]);

        return back()->with('status', 'Category request sent to Sushako for review.');
    }

    public function storeSubcategory(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $this->validateSubcategory($request, $vendor->id);

        if ($request->hasFile('image')) {
            $data['image_path'] = $accounts->storeUploadedFile($request->file('image'), 'seller-categories');
        }

        $data['slug'] = $this->uniqueSlug($vendor->id, $data['slug'] ?? $data['name']);
        $vendor->storefrontCategories()->create($data);

        return back()->with('status', 'Storefront category created.');
    }

    public function updateSubcategory(Request $request, SellerStorefrontCategory $category, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($category->vendor_id === $vendor->id, 404);

        $data = $this->validateSubcategory($request, $vendor->id, $category->id);
        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $accounts->storeUploadedFile($request->file('image'), 'seller-categories');
        }

        $data['slug'] = $this->uniqueSlug($vendor->id, $data['slug'] ?? $data['name'], $category->id);
        $category->fill($data)->save();

        return back()->with('status', 'Storefront category updated.');
    }

    public function destroySubcategory(Request $request, SellerStorefrontCategory $category): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($category->vendor_id === $vendor->id, 404);

        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'This category has '.$category->products()->count().' attached product(s). Reassign or remove products first.']);
        }

        $category->delete();

        return back()->with('status', 'Storefront category deleted.');
    }

    private function validateSubcategory(Request $request, int $vendorId, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)->where('available_to_sellers', true)],
            'name' => ['required', 'string', 'max:120', Rule::unique('seller_storefront_categories', 'name')->where('vendor_id', $vendorId)->ignore($ignoreId)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('seller_storefront_categories', 'slug')->where('vendor_id', $vendorId)->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', Rule::in([
                SellerStorefrontCategory::STATUS_ACTIVE,
                SellerStorefrontCategory::STATUS_DRAFT,
                SellerStorefrontCategory::STATUS_HIDDEN,
            ])],
        ]);

        $data['name'] = preg_replace('/\s+/', ' ', trim($data['name']));
        $normalizedName = Str::lower($data['name']);
        $duplicateExists = SellerStorefrontCategory::query()
            ->where('vendor_id', $vendorId)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'name' => 'This subcategory already exists for your store.',
            ]);
        }

        return $data;
    }

    private function uniqueSlug(int $vendorId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;

        while (SellerStorefrontCategory::query()->where('vendor_id', $vendorId)->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
