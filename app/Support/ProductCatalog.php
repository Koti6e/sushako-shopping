<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCatalog
{
    private static ?array $categoriesCache = null;

    private static ?Collection $productsCache = null;

    public static function banners(): array
    {
        return collect(self::categories())->map(fn (array $category): array => [
            'tagline' => $category['tagline'],
            'title' => $category['headline'],
            'subtitle' => $category['description'],
            'cta' => 'Explore Category',
            'secondary_cta' => 'Shop with Sushako',
            'href' => route('department.show', $category['slug']),
            'secondary_href' => route('department.show', $category['slug']),
            'image' => $category['image'],
        ])->values()->all();
    }

    public static function categories(): array
    {
        if (self::$categoriesCache !== null) {
            return self::$categoriesCache;
        }

        return self::$categoriesCache = Category::query()
            ->where('is_active', true)
            ->with(['products' => fn ($query) => $query->where('is_published', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => self::categoryArray($category))
            ->all();
    }

    public static function departments(): array
    {
        return collect(self::categories())->keyBy('slug')->all();
    }

    public static function department(string $slug): ?array
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->first();

        return $category ? self::categoryArray($category) : null;
    }

    public static function products(): Collection
    {
        if (self::$productsCache !== null) {
            return self::$productsCache;
        }

        return self::$productsCache = self::productQuery()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Product $product): array => self::productArray($product));
    }

    public static function featuredProduct(): ?array
    {
        return self::products()->first();
    }

    public static function reviews(): array
    {
        return [[
            'name' => 'Sushako Customer',
            'avatar' => asset('images/brand/sushako-shopping-logo.png'),
            'rating' => 5,
            'text' => 'The Sushako store feels warm, polished and easy to shop with clear support whenever needed.',
        ]];
    }

    public static function localProducts(): Collection
    {
        return self::products()->filter(fn (array $product): bool => (bool) $product['local_delivery'])->values();
    }

    public static function sidebarCategoryTree(): array
    {
        return collect(self::categories())->mapWithKeys(fn (array $category): array => [
            $category['name'] => $category['sidebar'],
        ])->all();
    }

    public static function trendingCategories(): array
    {
        return collect(self::categories())
            ->flatMap(fn (array $category): array => array_keys($category['sidebar']))
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    public static function productBySlug(string $slug): ?array
    {
        $product = self::productQuery()->where('slug', $slug)->first();

        return $product ? self::productArray($product) : null;
    }

    public static function productModelBySlug(string $slug): ?Product
    {
        return self::productQuery()->where('slug', $slug)->first();
    }

    public static function productsForDepartment(string $slug): Collection
    {
        return self::productQuery()
            ->whereHas('category', fn (Builder $query) => $query->where('slug', $slug))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Product $product): array => self::productArray($product));
    }

    public static function productsForCategory(string $slug): Collection
    {
        return self::productsForDepartment($slug);
    }

    public static function search(?string $query): Collection
    {
        if (! $query) {
            return self::products();
        }

        $needle = mb_strtolower($query);

        return self::products()->filter(function (array $product) use ($needle): bool {
            return str_contains(mb_strtolower($product['name'].' '.$product['department'].' '.$product['subcategory'].' '.$product['brand'].' '.$product['collection']), $needle);
        })->values();
    }

    public static function categoryNavigation(?string $departmentSlug = null): array
    {
        if ($departmentSlug && ($department = self::department($departmentSlug))) {
            return $department['sidebar'];
        }

        return collect(self::categories())->mapWithKeys(fn (array $department): array => [
            strtoupper($department['name']) => array_keys($department['sidebar']),
        ])->all();
    }

    public static function filterOptions(?string $departmentSlug = null): array
    {
        $products = $departmentSlug ? self::productsForDepartment($departmentSlug) : self::products();
        $filters = ['brand' => 'Brand', 'collection' => 'Collection', 'availability' => 'Availability'];
        $options = [];

        foreach ($filters as $key => $label) {
            if ($key === 'availability') {
                continue;
            }

            $options[$key] = $products
                ->flatMap(fn (array $product) => $product['filter_values'][$key] ?? ($key === 'brand' ? [$product['brand']] : []))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [
            'filters' => $filters,
            'options' => $options,
            'max_price' => max(100, (int) $products->max('selling_price')),
        ];
    }

    public static function variantFor(Product $product, string $colour, string $size): ?ProductVariant
    {
        return $product->variants()
            ->where('colour', $colour)
            ->where('size', $size)
            ->first();
    }

    public static function productArray(Product $product): array
    {
        $product->loadMissing(['category', 'images', 'variants']);

        $variants = $product->variants->sortBy(fn (ProductVariant $variant): string => $variant->colour.'|'.$variant->size)->values();
        $variantOptionName = $variants->pluck('option_name')->filter()->first();
        $optionValues = $variants->pluck('option_value')->filter()->unique()->values()->all();
        $hasCustomOptions = filled($variantOptionName) && filled($optionValues);
        $variantPrices = $variants
            ->filter(fn (ProductVariant $variant): bool => filled($variant->option_value))
            ->mapWithKeys(fn (ProductVariant $variant): array => [
                $variant->option_value => (int) ($variant->price ?: $product->selling_price),
            ])
            ->all();
        $displaySellingPrice = $variantPrices ? min($variantPrices) : (int) $product->selling_price;
        $displayMrp = max((int) $product->mrp, $variantPrices ? max($variantPrices) : (int) $product->mrp);
        $colours = $variants
            ->groupBy('colour')
            ->mapWithKeys(fn (Collection $items, string $colour): array => [
                $colour => [
                    'hex' => $items->first()->colour_hex ?: self::colourHex($colour),
                    'stock' => $items->mapWithKeys(fn (ProductVariant $variant): array => [$variant->size => (int) $variant->stock])->all(),
                ],
            ])
            ->all();
        $sizes = $hasCustomOptions ? $optionValues : ($variants->pluck('size')->unique()->values()->all() ?: ['Standard']);
        $hasColourOptions = collect(array_keys($colours))->contains(fn (string $colour): bool => $colour !== 'Standard');
        $hasSizeOptions = ! $hasCustomOptions && collect($sizes)->contains(fn (string $size): bool => $size !== 'Standard');
        $images = $product->images->map(fn ($image): array => [
            'id' => $image->id,
            'label' => $image->label,
            'path' => $image->url(),
        ])->values()->all();

        if (! $images) {
            $images = [[
                'id' => null,
                'label' => 'Product Image',
                'path' => self::imageUrl($product->category->image ?: 'assets/banners/home-made-health-mix.jpg'),
            ]];
        }

        $stock = (int) $variants->sum('stock');
        $discount = $displayMrp > $displaySellingPrice ? (int) round((($displayMrp - $displaySellingPrice) / $displayMrp) * 100) : 0;
        $details = $product->category->slug === 'electronics' ? [
            'Processor' => $product->fabric ?: 'Intel Celeron',
            'Memory' => $product->fit ?: '4GB RAM',
            'Storage' => $product->sleeve ?: implode(' / ', $optionValues ?: ['Standard']),
            'Best For' => $product->occasion ?: 'Students, browsing and everyday office use',
            'Country of Origin' => $product->country_of_origin,
            'Return' => $product->return_policy,
        ] : [
            'Fabric' => $product->fabric ?: 'Premium Finish',
            'Fit' => $product->fit ?: 'Standard',
            'Sleeve' => $product->sleeve ?: 'Not Applicable',
            'Pattern' => $product->pattern ?: 'Premium Finish',
            'Occasion' => $product->occasion ?: 'Everyday Shopping',
            'Country of Origin' => $product->country_of_origin,
            'Return' => $product->return_policy,
        ];

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'collection' => $product->collection,
            'department' => $product->category->name,
            'department_slug' => $product->category->slug,
            'category' => $product->category->name,
            'category_slug' => $product->category->slug,
            'subcategory' => $product->subcategory ?: $product->category->name,
            'brand' => $product->brand,
            'badge' => $product->badge,
            'short_description' => $product->short_description,
            'full_description' => $product->full_description,
            'fabric' => $product->fabric ?: 'Premium Finish',
            'fit' => $product->fit ?: 'Standard',
            'sleeve' => $product->sleeve ?: 'Not Applicable',
            'pattern' => $product->pattern ?: 'Premium Finish',
            'occasion' => $product->occasion ?: 'Everyday Shopping',
            'country_of_origin' => $product->country_of_origin,
            'return_policy' => $product->return_policy,
            'mrp' => $displayMrp,
            'selling_price' => $displaySellingPrice,
            'discount' => $discount,
            'rating' => number_format($product->rating, 1),
            'reviews' => $product->reviews,
            'stock_label' => $stock > 0 ? ($stock <= 5 ? "Only {$stock} left" : 'In Stock') : 'Out of Stock',
            'available' => $stock > 0,
            'local_delivery' => (bool) $product->local_delivery,
            'fulfillment_scope' => $product->fulfillment_scope,
            'is_new' => (bool) $product->is_new,
            'is_best_seller' => (bool) $product->is_best_seller,
            'colours' => $colours ?: ['Standard' => ['hex' => '#d8ccb9', 'stock' => ['Standard' => $stock]]],
            'sizes' => $sizes,
            'has_colour_options' => $hasColourOptions,
            'has_size_options' => $hasSizeOptions,
            'has_custom_options' => $hasCustomOptions,
            'option_name' => $variantOptionName,
            'option_values' => $optionValues,
            'variant_prices' => $variantPrices,
            'details' => $details,
            'variants' => $variants->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'colour' => $variant->colour,
                'size' => $variant->size,
                'option_name' => $variant->option_name,
                'option_value' => $variant->option_value,
                'price' => (int) ($variant->price ?: $product->selling_price),
                'stock' => (int) $variant->stock,
            ])->all(),
            'images' => $images,
            'filter_values' => [
                'brand' => [$product->brand],
                'collection' => [$product->collection],
                'discount' => [$discount],
                'rating' => [$product->rating],
                'availability' => [$stock > 0 ? 'In Stock' : 'Out of Stock'],
            ],
            'trust_badges' => [
                'Secure Razorpay payments',
                'Invoice PDF after order',
                'Fast fulfillment updates',
                'WhatsApp support visible',
            ],
            'reviews_list' => [
                ['name' => 'Sushako Customer', 'rating' => 5, 'text' => 'A polished Sushako shopping experience with clear details and reassuring support.'],
            ],
        ];
    }

    private static function categoryArray(Category $category): array
    {
        $category->loadMissing(['products' => fn ($query) => $query->where('is_published', true)]);
        $subcategories = $category->products->pluck('subcategory')->filter()->unique()->values()->all();

        if (! $subcategories) {
            $subcategories = [$category->name];
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'tagline' => $category->tagline ?: 'Sushako category',
            'headline' => $category->headline ?: $category->name,
            'description' => $category->description ?: 'Curated Sushako products with trusted shopping and reliable support.',
            'image' => $category->image ? self::imageUrl($category->image) : asset('assets/banners/home-made-health-mix.jpg'),
            'accent' => $category->accent ?: '#2f6b4f',
            'brands' => $category->products->pluck('brand')->filter()->unique()->values()->all() ?: ['Sushako'],
            'offers' => ['Secure shopping', 'Invoice after order', 'WhatsApp support'],
            'sidebar' => [
                'Featured' => $subcategories,
            ],
            'filters' => [
                'brand' => 'Brand',
                'collection' => 'Collection',
                'availability' => 'Availability',
            ],
        ];
    }

    private static function productQuery(): Builder
    {
        return Product::query()
            ->with(['category', 'images', 'variants'])
            ->where('is_published', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true));
    }

    private static function colourHex(string $colour): string
    {
        return [
            'Sushako Blue' => '#14213d',
            'Black' => '#111111',
            'White' => '#f7f7f7',
            'Gold' => '#c59a3d',
            'Standard' => '#d8ccb9',
        ][$colour] ?? '#d8ccb9';
    }

    private static function imageUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '/assets/', 'assets/', '/images/', 'images/'])) {
            return asset(ltrim($path, '/'));
        }

        return Storage::disk('public')->url($path);
    }
}
