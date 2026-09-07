<?php

namespace App\Http\Controllers;

use App\Models\MarketingContent;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\SellerStorefrontCategory;
use App\Models\Vendor;
use App\Services\OfficialStoreService;
use App\Support\ProductCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class StorefrontController extends Controller
{
    public function home()
    {
        $products = ProductCatalog::marketplaceFeed(8);
        $categories = ProductCatalog::categories();
        $productDepartmentSlugs = $products->pluck('department_slug')->unique();
        $activeCategories = collect($categories)
            ->filter(fn (array $category): bool => $productDepartmentSlugs->contains($category['slug']))
            ->values();
        $content = MarketingContent::query()
            ->visible()
            ->with(['category', 'product', 'productCollection'])
            ->whereIn('placement', ['homepage_hero', 'homepage_announcement', 'homepage_promotion', 'homepage_collection', 'homepage_category'])
            ->get()
            ->map(fn (MarketingContent $item): array => $this->contentData($item));

        $collectionSections = $content
            ->where('placement', 'homepage_collection')
            ->map(function (array $item): array {
                $item['products'] = $item['collection']
                    ? $this->publicCollectionProducts($item['collection'])->take(8)
                    : collect();

                return $item;
            })
            ->filter(fn (array $item): bool => $item['products']->isNotEmpty())
            ->values();

        return view('welcome', [
            'heroContent' => $content->where('placement', 'homepage_hero')->values(),
            'announcements' => $content->where('placement', 'homepage_announcement')->values(),
            'promotions' => $content->whereIn('placement', ['homepage_promotion', 'homepage_category'])->values(),
            'collectionSections' => $collectionSections,
            'categories' => $categories,
            'activeCategories' => $activeCategories->isNotEmpty() ? $activeCategories : collect($categories)->take(4),
            'products' => $products->take(8),
        ]);
    }

    public function shop(Request $request)
    {
        return view('shop.index', array_merge($this->listingData($request), [
            'title' => 'Shop - Sushako Shopping',
        ]));
    }

    public function department(Request $request, string $slug)
    {
        $department = ProductCatalog::department($slug);
        abort_unless($department, 404);

        return view('shop.index', array_merge($this->listingData($request, $slug), [
            'title' => $department['name'].' - Sushako Shopping',
            'activeDepartment' => $department,
        ]));
    }

    public function category(Request $request, string $slug)
    {
        return $this->department($request, $slug);
    }

    public function collection(string $slug)
    {
        $collection = ProductCollection::query()->visible()->where('slug', $slug)->firstOrFail();

        return view('collections.show', [
            'collection' => $collection,
            'products' => $this->publicCollectionProducts($collection),
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->string('q')->toString();

        return view('shop.index', array_merge($this->listingData($request), [
            'title' => 'Search - Sushako Shopping',
        ]));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = trim($request->string('q')->toString());

        if (mb_strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'products' => [],
                'categories' => [],
                'stores' => [],
            ]);
        }

        $needle = mb_strtolower($query);
        $highlight = fn (string $value): string => preg_replace('/('.preg_quote($query, '/').')/i', '<mark>$1</mark>', e($value)) ?: e($value);

        $products = ProductCatalog::marketplaceQuery()
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('products.name', 'like', '%'.$query.'%')
                    ->orWhere('products.brand', 'like', '%'.$query.'%')
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$query.'%'))
                    ->orWhereHas('vendor', fn ($vendor) => $vendor
                        ->where('store_display_name', 'like', '%'.$query.'%')
                        ->orWhere('business_name', 'like', '%'.$query.'%'));
            })
            ->orderByDesc('products.is_best_seller')
            ->orderByDesc('products.created_at')
            ->take(5)
            ->get()
            ->map(function (Product $product) use ($highlight): array {
                $item = ProductCatalog::productArray($product);

                return [
                    'label' => $item['name'],
                    'highlight' => $highlight($item['name']),
                    'meta' => $item['seller_name'].' · ₹'.number_format($item['selling_price']),
                    'url' => route('products.show', $item['slug']),
                ];
            })
            ->values();

        $categories = collect(ProductCatalog::categories())
            ->filter(fn (array $category): bool => str_contains(mb_strtolower($category['name'].' '.$category['description']), $needle))
            ->take(4)
            ->map(fn (array $category): array => [
                'label' => $category['name'],
                'highlight' => $highlight($category['name']),
                'meta' => 'Marketplace category',
                'url' => route('department.show', $category['slug']),
            ])
            ->values();

        $storefrontCategories = SellerStorefrontCategory::query()
            ->with('vendor')
            ->where('status', SellerStorefrontCategory::STATUS_ACTIVE)
            ->where('name', 'like', '%'.$query.'%')
            ->whereHas('vendor', fn ($vendor) => $vendor
                ->where('store_status', Vendor::STORE_LIVE)
                ->where('store_visibility', Vendor::VISIBILITY_PUBLISHED))
            ->limit(3)
            ->get()
            ->map(fn (SellerStorefrontCategory $category): array => [
                'label' => $category->name,
                'highlight' => $highlight($category->name),
                'meta' => ($category->vendor?->store_display_name ?: $category->vendor?->business_name ?: 'Seller storefront').' category',
                'url' => route('stores.show', $category->vendor->slug),
            ]);

        $stores = Vendor::query()
            ->where('store_status', Vendor::STORE_LIVE)
            ->where('store_visibility', Vendor::VISIBILITY_PUBLISHED)
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('store_display_name', 'like', '%'.$query.'%')
                    ->orWhere('business_name', 'like', '%'.$query.'%')
                    ->orWhere('city', 'like', '%'.$query.'%');
            })
            ->limit(5)
            ->get()
            ->map(fn (Vendor $vendor): array => [
                'label' => $vendor->store_display_name ?: $vendor->business_name,
                'highlight' => $highlight($vendor->store_display_name ?: $vendor->business_name),
                'meta' => $vendor->city ?: 'India',
                'url' => route('stores.show', $vendor->slug),
            ])
            ->values();

        return response()->json([
            'query' => $query,
            'products' => $products,
            'categories' => $categories->concat($storefrontCategories)->take(5)->values(),
            'stores' => $stores,
            'view_all_url' => route('search', ['q' => $query]),
        ]);
    }

    public function product(string $slug)
    {
        $productModel = ProductCatalog::productModelBySlug($slug);
        abort_unless($productModel, 404);

        $viewer = request()->user();
        $sessionKey = 'product_viewed.'.$productModel->id;
        if (! in_array($viewer?->role, ['vendor', 'super_admin'], true) && ! request()->session()->has($sessionKey)) {
            $productModel->increment('seller_product_views');
            request()->session()->put($sessionKey, now()->toIso8601String());
        }

        $product = ProductCatalog::productArray($productModel->fresh(['category', 'images', 'variants', 'vendor']));

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => ProductCatalog::marketplaceFeed(8),
        ]);
    }

    public function seller(Vendor $vendor)
    {
        abort_unless($vendor->store_visibility === Vendor::VISIBILITY_PUBLISHED && $vendor->store_status === Vendor::STORE_LIVE, 404);

        $products = $vendor->products()
            ->with(['category', 'images', 'variants', 'vendor'])
            ->where('is_published', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->paginate(16)
            ->through(fn ($product): array => ProductCatalog::productArray($product));

        return view('stores.show', [
            'vendor' => $vendor->load(['storefrontCategories' => fn ($query) => $query->where('status', 'active')->orderBy('display_order')]),
            'products' => $products,
            'isOfficial' => app(OfficialStoreService::class)->isOfficial($vendor),
        ]);
    }

    public function storeLocation(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'source' => ['required', 'in:gps,pincode,manual'],
            'area' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:12'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        session([
            'delivery_location' => array_filter([
                'source' => $data['source'],
                'area' => $data['area'] ?? null,
                'city' => $data['city'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'label' => $this->locationLabel($data),
            ], fn ($value) => filled($value)),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'location' => session('delivery_location')]);
        }

        return back()->with('status', 'Delivery location updated.');
    }

    public function clearLocation(Request $request): JsonResponse|RedirectResponse
    {
        $request->session()->forget('delivery_location');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Delivery location cleared.');
    }

    private function listingData(Request $request, ?string $forcedCategory = null): array
    {
        $categories = collect(ProductCatalog::categories());
        $categoryModels = Category::query()
            ->where('is_active', true)
            ->with('parent.parent.parent')
            ->get()
            ->keyBy('id');
        $validCategorySlugs = $categoryModels->pluck('slug')->values();
        $categoryCounts = $this->marketplaceCategoryCounts($categoryModels);
        $activeCategories = $categories
            ->filter(fn (array $category): bool => (int) ($categoryCounts[$category['id']] ?? 0) > 0)
            ->map(fn (array $category): array => array_merge($category, [
                'product_count' => (int) ($categoryCounts[$category['id']] ?? 0),
            ]))
            ->values();

        $allowedShop = ['all', 'featured', 'new'];
        $allowedPrices = ['under-500', '500-999', '1000-4999', '5000-plus'];
        $allowedStock = ['in-stock'];
        $allowedSorts = ['featured', 'newest', 'price-low-high', 'price-high-low', 'name-a-z', 'name-az'];

        $filters = [
            'q' => trim($request->string('q')->toString()),
            'shop' => in_array($request->query('shop', 'all'), $allowedShop, true) ? $request->query('shop', 'all') : 'all',
            'category' => $forcedCategory ?: (in_array($request->query('category'), $validCategorySlugs->all(), true) ? $request->query('category') : null),
            'price' => in_array($request->query('price'), $allowedPrices, true) ? $request->query('price') : null,
            'stock' => in_array($request->query('stock'), $allowedStock, true) ? $request->query('stock') : null,
            'sort' => in_array($request->query('sort', 'featured'), $allowedSorts, true) ? $request->query('sort', 'featured') : 'featured',
        ];
        $filters['sort'] = $filters['sort'] === 'name-az' ? 'name-a-z' : $filters['sort'];

        $query = ProductCatalog::marketplaceQuery()
            ->when($filters['q'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('products.name', 'like', '%'.$search.'%')
                        ->orWhere('products.brand', 'like', '%'.$search.'%')
                        ->orWhere('products.collection', 'like', '%'.$search.'%')
                        ->orWhere('products.subcategory', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('vendor', fn ($vendor) => $vendor
                            ->where('store_display_name', 'like', '%'.$search.'%')
                            ->orWhere('business_name', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['shop'] === 'featured', fn ($query) => $query->where('products.is_best_seller', true))
            ->when($filters['shop'] === 'new', fn ($query) => $query->where('products.is_new', true))
            ->when($filters['category'], function ($query) use ($filters, $categoryModels): void {
                $category = $categoryModels->firstWhere('slug', $filters['category']);
                $query->whereIn('products.category_id', $category ? $this->categoryAndDescendantIds($category) : []);
            })
            ->when($filters['stock'] === 'in-stock', fn ($query) => $query->whereHas('variants', fn ($variant) => $variant->where('stock', '>', 0)))
            ->when($filters['price'], function ($query, string $price): void {
                match ($price) {
                    'under-500' => $query->where('products.selling_price', '<', 500),
                    '500-999' => $query->whereBetween('products.selling_price', [500, 999]),
                    '1000-4999' => $query->whereBetween('products.selling_price', [1000, 4999]),
                    '5000-plus' => $query->where('products.selling_price', '>=', 5000),
                };
            });

        $products = match ($filters['sort']) {
            'price-low-high' => $query->with(['category.parent.parent.parent', 'images', 'variants', 'vendor'])
                ->orderBy('products.selling_price')->orderBy('products.id'),
            'price-high-low' => $query->with(['category.parent.parent.parent', 'images', 'variants', 'vendor'])
                ->orderByDesc('products.selling_price')->orderByDesc('products.id'),
            'name-a-z' => $query->with(['category.parent.parent.parent', 'images', 'variants', 'vendor'])
                ->orderBy('products.name')->orderBy('products.id'),
            'newest' => ProductCatalog::sellerDiverseQuery($query, 'products.created_at desc, products.id desc'),
            default => ProductCatalog::sellerDiverseQuery($query),
        };

        $paginator = $products
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product): array => ProductCatalog::productArray($product));

        $category = $filters['category'] ? $categoryModels->firstWhere('slug', $filters['category']) : null;
        $chips = $this->activeFilterChips($filters, $category);

        return [
            'products' => $paginator,
            'productsTotal' => $paginator->total(),
            'categories' => $categories->all(),
            'activeCategories' => $activeCategories,
            'departments' => ProductCatalog::departments(),
            'filters' => $filters,
            'activeFilterChips' => $chips,
            'sortOptions' => [
                'featured' => 'Featured',
                'newest' => 'Newest Arrivals',
                'price-low-high' => 'Price: Low to High',
                'price-high-low' => 'Price: High to Low',
                'name-a-z' => 'Name: A to Z',
            ],
            'shopOptions' => [
                'all' => ['label' => 'All Products', 'icon' => 'fa-border-all'],
                'featured' => ['label' => 'Featured', 'icon' => 'fa-star'],
                'new' => ['label' => 'New Arrivals', 'icon' => 'fa-wand-magic-sparkles'],
            ],
            'priceOptions' => [
                'under-500' => 'Under ₹500',
                '500-999' => '₹500–₹999',
                '1000-4999' => '₹1,000–₹4,999',
                '5000-plus' => '₹5,000 and above',
            ],
            'stockOptions' => [
                'in-stock' => 'In Stock',
            ],
            'heading' => $category?->name ?: 'Shop the Marketplace',
            'lede' => $category ? 'Browse products in this Sushako marketplace category.' : 'Products from active marketplace sellers, with new discoveries arriving regularly.',
            'query' => $filters['q'],
            'forcedCategory' => $forcedCategory,
        ];
    }

    /** @param Collection<int, Category> $categories */
    private function marketplaceCategoryCounts(Collection $categories): Collection
    {
        $directCounts = ProductCatalog::marketplaceQuery()
            ->selectRaw('products.category_id, count(*) as aggregate')
            ->groupBy('products.category_id')
            ->pluck('aggregate', 'category_id');
        $counts = collect();

        foreach ($directCounts as $categoryId => $count) {
            $category = $categories->get($categoryId);

            while ($category) {
                $counts[$category->id] = (int) ($counts[$category->id] ?? 0) + (int) $count;
                $category = $category->parent;
            }
        }

        return $counts;
    }

    /** @return array<int, int> */
    private function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];
        $parents = [$category->id];

        while ($parents) {
            $parents = Category::query()->whereIn('parent_id', $parents)->pluck('id')->all();
            $ids = [...$ids, ...$parents];
        }

        return $ids;
    }

    private function matchesPriceRange(int $price, string $range): bool
    {
        return match ($range) {
            'under-500' => $price < 500,
            '500-999' => $price >= 500 && $price <= 999,
            '1000-4999' => $price >= 1000 && $price <= 4999,
            '5000-plus' => $price >= 5000,
            default => true,
        };
    }

    private function activeFilterChips(array $filters, ?array $category): array
    {
        $chips = [];

        if ($filters['q']) {
            $chips[] = ['key' => 'q', 'label' => 'Search: "'.$filters['q'].'"'];
        }
        if ($filters['shop'] !== 'all') {
            $chips[] = ['key' => 'shop', 'label' => $filters['shop'] === 'new' ? 'New Arrivals' : 'Featured'];
        }
        if ($category) {
            $chips[] = ['key' => 'category', 'label' => $category['name']];
        }
        if ($filters['price']) {
            $labels = [
                'under-500' => 'Under ₹500',
                '500-999' => '₹500–₹999',
                '1000-4999' => '₹1,000–₹4,999',
                '5000-plus' => '₹5,000 and above',
            ];
            $chips[] = ['key' => 'price', 'label' => $labels[$filters['price']]];
        }
        if ($filters['stock'] === 'in-stock') {
            $chips[] = ['key' => 'stock', 'label' => 'In Stock'];
        }

        return $chips;
    }

    private function locationLabel(array $data): string
    {
        $manual = collect([$data['area'] ?? null, $data['city'] ?? null, $data['pincode'] ?? null])->filter()->join(', ');

        return $manual ?: ($data['source'] === 'gps' ? 'Current Location' : 'Selected Location');
    }

    private function contentData(MarketingContent $content): array
    {
        return [
            'title' => $content->title,
            'subtitle' => $content->subtitle,
            'description' => $content->description,
            'cta_label' => $content->cta_label,
            'destination' => $content->destination(),
            'image' => $content->image_path ? Storage::disk('public')->url($content->image_path) : null,
            'mobile_image' => $content->mobile_image_path ? Storage::disk('public')->url($content->mobile_image_path) : null,
            'collection' => $content->productCollection,
        ];
    }

    private function publicCollectionProducts(ProductCollection $collection): Collection
    {
        return $collection->products()
            ->with(['category.parent.parent.parent', 'images', 'variants', 'vendor'])
            ->where('is_published', true)
            ->whereIn('seller_status', ['approved', 'active', 'scheduled'])
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('vendor', fn ($query) => $query
                ->where('store_status', Vendor::STORE_LIVE)
                ->where('store_visibility', Vendor::VISIBILITY_PUBLISHED))
            ->whereHas('variants')
            ->where('name', 'not like', '%staging%')
            ->where('name', 'not like', '%sample%')
            ->get()
            ->map(fn ($product): array => ProductCatalog::productArray($product));
    }
}
