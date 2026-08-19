@php
    $queryWithoutPage = request()->except('page');
    $urlWith = function (array $changes = []) use ($queryWithoutPage) {
        $params = array_merge($queryWithoutPage, $changes);
        $params = array_filter($params, fn ($value) => filled($value) && $value !== 'all');

        return url()->current().($params ? '?'.http_build_query($params) : '');
    };
    $productsUrlWith = function (array $changes = []) use ($queryWithoutPage) {
        $params = array_merge($queryWithoutPage, $changes);
        $params = array_filter($params, fn ($value) => filled($value) && $value !== 'all');

        return route('products.index').($params ? '?'.http_build_query($params) : '');
    };
    $clearUrl = route('products.index');
    $resultLabel = $productsTotal === 1 ? '1 product found' : "{$productsTotal} products found";
    $activeFilterCount = collect($activeFilterChips)->reject(fn (array $chip): bool => $chip['key'] === 'sort')->count();
@endphp

<x-layouts.customer :title="$title">
    <section class="shop-collection-intro">
        <div class="site-shell">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span>Products</span>
            </nav>
            <div class="shop-collection-intro__content">
                <div>
                    <p class="eyebrow">Products</p>
                    <h1>{{ $heading }}</h1>
                    <p class="lede">{{ $lede }}</p>
                </div>
                @if ($query)
                    <p class="shop-search-context">Search results for <strong>"{{ $query }}"</strong></p>
                @endif
            </div>
        </div>
    </section>

    <section class="site-shell products-browse-layout" data-shop-experience data-server-filtered>
        <button class="products-filter-backdrop" type="button" data-products-overlay aria-label="Close panels" hidden></button>

        <aside class="products-sidebar" data-mobile-filter-panel aria-label="Product filters">
            <div class="products-sidebar__mobile-header">
                <strong>Filter & Categories</strong>
                <button type="button" data-filter-close aria-label="Close filters"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>

            <form class="products-sidebar__search" method="GET" action="{{ url()->current() }}">
                @foreach (request()->except(['q', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="products-search">Search</label>
                <div>
                    <input id="products-search" name="q" value="{{ $filters['q'] }}" placeholder="Search products">
                    <button type="submit" aria-label="Search products"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                </div>
            </form>

            <section class="products-filter-group" aria-labelledby="filter-shop">
                <h2 id="filter-shop">Shop</h2>
                <nav class="products-filter-list" aria-label="Shop filters">
                    @foreach ($shopOptions as $value => $option)
                        <a href="{{ $urlWith(['shop' => $value]) }}" @class(['is-active' => $filters['shop'] === $value]) @if($filters['shop'] === $value) aria-current="true" @endif>
                            <i class="fa-solid {{ $option['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $option['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </section>

            <section class="products-filter-group" aria-labelledby="filter-category">
                <h2 id="filter-category">Shop by Category</h2>
                <nav class="products-filter-list" aria-label="Category filters">
                    <a href="{{ $productsUrlWith(['category' => null]) }}" @class(['is-active' => ! $filters['category']]) @if(! $filters['category']) aria-current="true" @endif>
                        <i class="fa-solid fa-border-all" aria-hidden="true"></i>
                        <span>All Categories</span>
                    </a>
                    @foreach ($activeCategories as $category)
                        <a href="{{ $productsUrlWith(['category' => $category['slug']]) }}" @class(['is-active' => $filters['category'] === $category['slug']]) @if($filters['category'] === $category['slug']) aria-current="true" @endif>
                            <i class="fa-solid fa-tag" aria-hidden="true"></i>
                            <span>{{ $category['name'] }}</span>
                            <small>{{ $category['product_count'] }}</small>
                        </a>
                    @endforeach
                </nav>
            </section>

            <section class="products-filter-group" aria-labelledby="filter-price">
                <h2 id="filter-price">Price</h2>
                <nav class="products-filter-list" aria-label="Price filters">
                    @foreach ($priceOptions as $value => $label)
                        <a href="{{ $urlWith(['price' => $filters['price'] === $value ? null : $value]) }}" @class(['is-active' => $filters['price'] === $value]) @if($filters['price'] === $value) aria-current="true" @endif>
                            <i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>
            </section>

            <section class="products-filter-group" aria-labelledby="filter-availability">
                <h2 id="filter-availability">Availability</h2>
                <nav class="products-filter-list" aria-label="Availability filters">
                    <a href="{{ $urlWith(['stock' => $filters['stock'] === 'in-stock' ? null : 'in-stock']) }}" @class(['is-active' => $filters['stock'] === 'in-stock']) @if($filters['stock'] === 'in-stock') aria-current="true" @endif>
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        <span>In Stock</span>
                    </a>
                </nav>
            </section>

            @if ($activeFilterCount > 0 || filled($filters['q']))
                <section class="products-filter-group" aria-labelledby="filter-clear">
                    <h2 id="filter-clear">Clear Filters</h2>
                    <a class="products-clear-link" href="{{ $clearUrl }}">
                        <i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i>
                        Clear All
                    </a>
                </section>
            @endif

            <div class="products-sidebar__footer">
                @if ($activeFilterCount > 0 || filled($filters['q']))
                    <a class="button button--ghost" href="{{ $clearUrl }}">Clear All</a>
                @endif
                <button class="button button--primary" type="button" data-filter-close>Apply Filters · {{ $resultLabel }}</button>
            </div>
        </aside>

        <div class="products-results">
            <div class="products-toolbar">
                <div class="products-toolbar__mobile">
                    <button class="filter-toggle-button" type="button" data-filter-toggle aria-expanded="false">
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                        Filter & Categories
                        @if ($activeFilterCount > 0)
                            <span>{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                    <button class="filter-toggle-button" type="button" data-sort-toggle aria-expanded="false">
                        <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                        Sort
                    </button>
                </div>
                <p>
                    @if ($productsTotal > 0)
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $productsTotal }} {{ $productsTotal === 1 ? 'product' : 'products' }}
                    @else
                        0 products found
                    @endif
                </p>
                <form method="GET" action="{{ url()->current() }}" class="products-sort-form">
                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="products-sort">Sort by</label>
                    <select id="products-sort" name="sort" data-server-sort>
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <section class="products-sort-sheet" data-sort-sheet aria-label="Sort products" aria-modal="true" hidden>
                <div class="products-sort-sheet__header">
                    <strong>Sort by</strong>
                    <button type="button" data-sort-close aria-label="Close sort options"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <nav aria-label="Mobile sort options">
                    @foreach ($sortOptions as $value => $label)
                        <a href="{{ $urlWith(['sort' => $value]) }}" @class(['is-active' => $filters['sort'] === $value]) @if($filters['sort'] === $value) aria-current="true" @endif>
                            <span>{{ $label }}</span>
                            @if ($filters['sort'] === $value)
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </section>

            @if ($activeFilterChips)
                <div class="active-filter-chips" aria-label="Active filters">
                    @foreach ($activeFilterChips as $chip)
                        <a href="{{ $chip['key'] === 'category' ? $productsUrlWith(['category' => null]) : $urlWith([$chip['key'] => null]) }}" aria-label="Remove {{ $chip['label'] }} filter">
                            {{ $chip['label'] }}
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </a>
                    @endforeach
                    <a class="active-filter-chips__clear" href="{{ $clearUrl }}">Clear All</a>
                </div>
            @endif

            @if ($productsTotal > 0)
                <div class="product-grid products-grid" data-product-grid>
                    @foreach ($products as $product)
                        <x-product.card :product="$product" compact />
                    @endforeach
                </div>

                @if ($products->hasPages())
                    <nav class="products-pagination" aria-label="Product pagination">
                        {{ $products->links() }}
                    </nav>
                @endif
            @else
                <div class="empty-cart products-no-results">
                    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                    <h2>Nothing matched your selection</h2>
                    <p class="lede">Try adjusting a filter or exploring another collection.</p>
                    <div>
                        <a class="button button--primary" href="{{ $clearUrl }}">Clear Filters</a>
                        <a class="button button--ghost" href="{{ route('shop') }}">Explore All Products</a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.customer>
