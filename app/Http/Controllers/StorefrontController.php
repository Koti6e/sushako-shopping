<?php

namespace App\Http\Controllers;

use App\Models\SellerStorefrontCategory;
use App\Models\StorefrontPromotionalBanner;
use App\Models\Vendor;
use App\Services\OfficialStoreService;
use App\Support\ProductCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class StorefrontController extends Controller
{
    public function home()
    {
        $products = ProductCatalog::products();
        $categories = ProductCatalog::categories();
        $productDepartmentSlugs = $products->pluck('department_slug')->unique();
        $activeCategories = collect($categories)
            ->filter(fn (array $category): bool => $productDepartmentSlugs->contains($category['slug']))
            ->values();
        $previewCategories = collect($categories)
            ->reject(fn (array $category): bool => $productDepartmentSlugs->contains($category['slug']))
            ->values();
        $heroCategories = $activeCategories->concat($previewCategories)->take(4)->values();
        $heroSlides = collect([[
            'tagline' => 'Sushako Marketplace',
            'title' => 'Shop Products from Trusted Local Sellers',
            'subtitle' => 'Discover quality products from verified stores near you and across the Sushako marketplace.',
            'cta' => 'Explore Products',
            'secondary_cta' => 'Find Stores Near You',
            'href' => route('shop'),
            'secondary_href' => '#trusted-stores',
            'image' => $heroCategories->first()['image'] ?? asset('assets/brand/sushako-shopping-official-full.png'),
        ]])->concat($heroCategories->map(fn (array $category): array => [
            'tagline' => $productDepartmentSlugs->contains($category['slug']) ? $category['tagline'] : 'More Stores Coming Soon',
            'title' => $productDepartmentSlugs->contains($category['slug']) ? $category['headline'] : 'Something New Is Taking Shape',
            'subtitle' => $productDepartmentSlugs->contains($category['slug'])
                ? $category['description']
                : 'More local sellers and product categories are being prepared for Sushako.',
            'cta' => $productDepartmentSlugs->contains($category['slug']) ? 'Shop Collection' : 'See What\'s Next',
            'secondary_cta' => 'Browse All',
            'href' => $productDepartmentSlugs->contains($category['slug']) ? route('department.show', $category['slug']) : '#whats-next',
            'secondary_href' => route('shop'),
            'image' => $category['image'],
        ]))->take(5)->values();

        return view('welcome', [
            'banners' => $heroSlides,
            'categories' => $categories,
            'activeCategories' => $activeCategories->isNotEmpty() ? $activeCategories : collect($categories)->take(4),
            'previewCategories' => $previewCategories,
            'products' => $products->take(8),
            'product' => ProductCatalog::featuredProduct(),
            'reviews' => ProductCatalog::reviews(),
            'departments' => ProductCatalog::departments(),
            'localProducts' => ProductCatalog::localProducts(),
            'sellerBanners' => $this->sellerBanners(),
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

        $products = ProductCatalog::products()
            ->filter(fn (array $product): bool => str_contains(mb_strtolower($product['name'].' '.$product['brand'].' '.$product['category'].' '.$product['seller_name']), $needle))
            ->take(5)
            ->map(fn (array $product): array => [
                'label' => $product['name'],
                'highlight' => $highlight($product['name']),
                'meta' => $product['seller_name'].' · ₹'.number_format($product['selling_price']),
                'url' => route('products.show', $product['slug']),
            ])
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
            'relatedProducts' => ProductCatalog::products(),
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
        $allProducts = ProductCatalog::products();
        $categories = collect(ProductCatalog::categories());
        $validCategorySlugs = $allProducts
            ->filter(fn (array $product): bool => (bool) $product['available'])
            ->pluck('department_slug')
            ->unique()
            ->values();
        $categoryCounts = $allProducts
            ->filter(fn (array $product): bool => (bool) $product['available'])
            ->groupBy('department_slug')
            ->map->count();
        $activeCategories = $categories
            ->filter(fn (array $category): bool => $validCategorySlugs->contains($category['slug']))
            ->map(fn (array $category): array => array_merge($category, [
                'product_count' => (int) ($categoryCounts[$category['slug']] ?? 0),
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

        $products = $allProducts
            ->when($filters['q'], fn (Collection $items): Collection => $items->filter(function (array $product) use ($filters): bool {
                $needle = mb_strtolower($filters['q']);

                return str_contains(mb_strtolower($product['name'].' '.$product['department'].' '.$product['subcategory'].' '.$product['brand'].' '.$product['collection'].' '.$product['seller_name']), $needle);
            }))
            ->when($filters['shop'] === 'featured', fn (Collection $items): Collection => $items->filter(fn (array $product): bool => (bool) $product['is_best_seller']))
            ->when($filters['shop'] === 'new', fn (Collection $items): Collection => $items->filter(fn (array $product): bool => (bool) $product['is_new']))
            ->when($filters['category'], fn (Collection $items): Collection => $items->filter(fn (array $product): bool => $product['department_slug'] === $filters['category']))
            ->when($filters['stock'] === 'in-stock', fn (Collection $items): Collection => $items->filter(fn (array $product): bool => (bool) $product['available']))
            ->when($filters['price'], fn (Collection $items): Collection => $items->filter(fn (array $product): bool => $this->matchesPriceRange((int) $product['selling_price'], $filters['price'])))
            ->values();

        $products = match ($filters['sort']) {
            'newest' => $products->sortByDesc(fn (array $product): bool => (bool) $product['is_new'])->values(),
            'price-low-high' => $products->sortBy(fn (array $product): int => (int) $product['selling_price'])->values(),
            'price-high-low' => $products->sortByDesc(fn (array $product): int => (int) $product['selling_price'])->values(),
            'name-a-z' => $products->sortBy(fn (array $product): string => $product['name'])->values(),
            default => $products->sortByDesc(fn (array $product): int => ((int) $product['is_best_seller'] * 2) + (int) $product['is_new'])->values(),
        };

        $perPage = 12;
        $page = max(1, (int) $request->query('page', 1));
        $paginator = new LengthAwarePaginator(
            $products->forPage($page, $perPage)->values(),
            $products->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );
        $paginator->appends($request->except('page'));

        $category = $filters['category'] ? $activeCategories->firstWhere('slug', $filters['category']) : null;
        $chips = $this->activeFilterChips($filters, $category);

        return [
            'products' => $paginator,
            'productsTotal' => $products->count(),
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
            'heading' => 'Shop the Collection',
            'lede' => 'Thoughtfully selected products, with new discoveries arriving regularly.',
            'query' => $filters['q'],
            'forcedCategory' => $forcedCategory,
        ];
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

    private function sellerBanners(): Collection
    {
        if (! Schema::hasTable('storefront_promotional_banners')) {
            return $this->defaultSellerBanners();
        }

        $configured = StorefrontPromotionalBanner::query()->visible()->get()->map(fn (StorefrontPromotionalBanner $banner): array => [
            'title' => $banner->title,
            'description' => $banner->description,
            'desktop_image' => asset('storage/'.$banner->desktop_image_path),
            'mobile_image' => asset('storage/'.$banner->mobile_image_path),
            'cta_label' => $banner->cta_label,
            'cta_url' => $banner->cta_url,
        ]);

        if ($configured->isNotEmpty()) {
            return $configured;
        }

        return $this->defaultSellerBanners();
    }

    private function defaultSellerBanners(): Collection
    {
        return collect([
            ['title' => 'Start Selling with Zero Upfront Cost', 'description' => 'Join the Free Plan with unlimited products and unlimited orders. Sushako commission applies only to eligible successful sales.', 'desktop_image' => asset('assets/banners/home-made-health-mix.jpg'), 'mobile_image' => asset('assets/banners/home-made-health-mix.jpg'), 'cta_label' => 'Become a Seller', 'cta_url' => route('seller.login')],
            ['title' => 'Build Your Store Under Your Business Identity', 'description' => 'Upload your logo, manage products and fulfil orders using your preferred self-shipping method.', 'desktop_image' => asset('assets/banners/home-made-masala.jpg'), 'mobile_image' => asset('assets/banners/home-made-masala.jpg'), 'cta_label' => 'Open Your Store', 'cta_url' => route('seller.login')],
            ['title' => 'Upgrade and Sell with Zero Commission', 'description' => 'Growth and Enterprise plans include zero marketplace commission while the paid plan remains active.', 'desktop_image' => asset('assets/banners/electronics.jpg'), 'mobile_image' => asset('assets/banners/electronics.jpg'), 'cta_label' => 'View Seller Plans', 'cta_url' => route('seller.login')],
            ['title' => 'Manage Products, Orders and Settlements', 'description' => 'Operate your business from one professional seller dashboard powered by Sushako.', 'desktop_image' => asset('assets/banners/womens-fashion.jpg'), 'mobile_image' => asset('assets/banners/womens-fashion.jpg'), 'cta_label' => 'Explore Seller Platform', 'cta_url' => route('seller.login')],
        ]);
    }
}
