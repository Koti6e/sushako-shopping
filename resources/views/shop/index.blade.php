<x-layouts.customer :title="$title">
    <section class="shop-hero @isset($activeDepartment) shop-hero--department @endisset" @isset($activeDepartment) style="--department-accent: {{ $activeDepartment['accent'] }}; --department-image: url('{{ $activeDepartment['image'] }}')" @endisset>
        <div class="site-shell">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span>{{ $heading }}</span>
            </nav>
            @isset($activeDepartment)
                <p class="eyebrow">{{ $activeDepartment['tagline'] }}</p>
            @endisset
            <h1>{{ $heading }}</h1>
            <p class="lede">{{ $lede ?? 'Premium shopping, curated categories, same-day dispatch messaging, and secure Razorpay checkout readiness.' }}</p>
            @isset($activeDepartment)
                <div class="department-offers" aria-label="{{ $activeDepartment['name'] }} offers">
                    @foreach ($activeDepartment['offers'] as $offer)
                        <span>{{ $offer }}</span>
                    @endforeach
                </div>
            @endisset
        </div>
    </section>

    <section class="site-shell shop-layout" data-shop-experience>
        <aside class="filter-panel" data-mobile-filter-panel>
            <div class="filter-panel__header">
                <p class="eyebrow">Refine</p>
                <h2>@isset($activeDepartment) {{ $activeDepartment['name'] }} @else Marketplace @endisset Filters</h2>
            </div>
            <form class="search-box" data-filter-form>
                <label for="q">Search</label>
                <input id="q" name="q" value="{{ $query ?? '' }}" placeholder="Search product, brand, category" data-filter-search>
            </form>

            @foreach ($categoryNavigation as $group => $items)
                <details class="category-nav-group" {{ ($loop->first || isset($activeDepartment)) ? 'open' : '' }}>
                    <summary>{{ $group }}</summary>
                    @foreach ($items as $item)
                        <button type="button" data-category-filter="{{ $item }}">{{ $item }}</button>
                    @endforeach
                </details>
            @endforeach

            <div class="filter-block">
                <h3>Price</h3>
                <input type="range" min="100" max="{{ $filterOptions['max_price'] }}" value="{{ $filterOptions['max_price'] }}" step="100" data-price-filter>
                <p>Up to <strong data-price-output>&#8377;{{ number_format($filterOptions['max_price']) }}</strong></p>
            </div>

            @foreach ($filterOptions['options'] as $key => $values)
                <div class="filter-block">
                    <h3>{{ $filterOptions['filters'][$key] ?? str($key)->headline() }}</h3>
                    <div class="chip-row">
                        @foreach ($values as $value)
                            <button type="button" data-option-filter="{{ $key }}" data-option-value="{{ $value }}">{{ $value }}</button>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="filter-block">
                <h3>Availability</h3>
                <label><input type="checkbox" value="1" data-availability-filter checked> In Stock</label>
            </div>

            <div class="filter-block">
                <h3>Discount</h3>
                <label><input type="checkbox" value="30" data-discount-filter> 30% and above</label>
                <label><input type="checkbox" value="40" data-discount-filter> 40% and above</label>
            </div>

            <div class="filter-block">
                <h3>Rating</h3>
                <label><input type="checkbox" value="4.5" data-rating-filter> 4.5 stars and above</label>
                <label><input type="checkbox" value="4.8" data-rating-filter> 4.8 stars and above</label>
            </div>
        </aside>

        <div class="product-listing">
            <div class="listing-toolbar">
                <button class="filter-toggle-button" type="button" data-filter-toggle>Filters</button>
                <span><strong data-product-count>{{ $products->count() }}</strong> products shown</span>
                <select aria-label="Sort products" data-sort-filter>
                    <option value="featured">Featured</option>
                    <option value="newest">Newest</option>
                    <option value="price_asc">Price Low → High</option>
                    <option value="price_desc">Price High → Low</option>
                    <option value="rating_desc">Highest Rated</option>
                    <option value="best_selling">Best Selling</option>
                </select>
            </div>
            <div class="product-grid" data-product-grid>
                @forelse ($products as $product)
                    <x-product.card :product="$product" />
                @empty
                    <div class="empty-cart empty-cart--catalog">
                        <p class="eyebrow">Launching Soon</p>
                        <h2>Products coming soon</h2>
                        <p class="lede">This category is ready. Published products will appear here automatically after they are added from admin.</p>
                    </div>
                @endforelse
            </div>
            <div class="empty-cart shop-empty-state" data-shop-empty hidden>
                <h2>No products match those filters</h2>
                <p class="lede">Try clearing a filter or searching across departments, brands or offers.</p>
            </div>

            @isset($activeDepartment)
                <section class="department-meta-grid">
                    <article>
                        <p class="eyebrow">Popular Brands</p>
                        <h2>{{ implode(' · ', $activeDepartment['brands']) }}</h2>
                    </article>
                    <article>
                        <p class="eyebrow">Department Offers</p>
                        <h2>{{ implode(' · ', $activeDepartment['offers']) }}</h2>
                    </article>
                </section>
            @endisset
        </div>
    </section>
</x-layouts.customer>
