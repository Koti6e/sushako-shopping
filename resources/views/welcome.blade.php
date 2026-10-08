<x-layouts.customer title="Sushako Shopping">
    <section class="site-shell section-block marketplace-hero" aria-label="Marketplace hero">
        <div class="marketplace-hero__panel">
            <div class="marketplace-hero__copy">
                <p class="eyebrow">Verified sellers · Live stock</p>
                <h1>Shop Local. Shop Smart. Shop Sushako.</h1>
                <p class="lede">Discover everyday essentials, trusted local stores, and products that are already live in the marketplace.</p>
                <div class="hero-carousel__actions">
                    <a class="button button--primary" href="{{ route('shop') }}">Shop the Marketplace</a>
                    <a class="button button--ghost" href="{{ route('search') }}">Explore Finds</a>
                </div>
            </div>

            @if ($heroContent->isNotEmpty())
                @php($hero = $heroContent->first())
                <div class="marketplace-hero__feature">
                    @if ($hero['image'])
                        <img src="{{ $hero['image'] }}" alt="{{ $hero['title'] }}" loading="eager">
                    @else
                        <div class="marketplace-content-no-preview"><i class="fa-regular fa-image" aria-hidden="true"></i> No Preview Available</div>
                    @endif
                    <div>
                        <p class="eyebrow">Featured</p>
                        <h2>{{ $hero['title'] }}</h2>
                        @if ($hero['subtitle'])
                            <p>{{ $hero['subtitle'] }}</p>
                        @endif
                        @if ($hero['destination'] && $hero['cta_label'])
                            <a href="{{ $hero['destination'] }}">{{ $hero['cta_label'] }}</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="marketplace-hero__showcase" aria-label="Live marketplace picks">
            @foreach ($products->take(3) as $product)
                <a class="marketplace-hero__tile" href="{{ route('products.show', $product['slug']) }}">
                    <x-product.image :src="data_get($product, 'images.0.path')" :alt="$product['name'].' front view'" />
                    <span>{{ $product['name'] }}</span>
                    <strong>₹{{ number_format($product['selling_price']) }}</strong>
                </a>
            @endforeach
        </div>
    </section>

    @if ($announcements->isNotEmpty())
        <section class="marketplace-announcements" aria-label="Marketplace announcements">
            <div class="site-shell">
                @foreach ($announcements as $announcement)
                    <div>
                        <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                        <span><strong>{{ $announcement['title'] }}</strong>@if ($announcement['subtitle']) {{ $announcement['subtitle'] }}@endif</span>
                        @if ($announcement['destination'] && $announcement['cta_label'])
                            <a href="{{ $announcement['destination'] }}">{{ $announcement['cta_label'] }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="site-shell section-block section-block--storefront" id="categories">
        <div class="section-heading section-heading--editorial">
            <div><p class="eyebrow">Marketplace</p><h2>Browse by Category</h2></div>
            <a href="{{ route('shop') }}">View All Products</a>
        </div>

        <nav class="category-pills" aria-label="Marketplace categories">
            @forelse ($activeCategories as $category)
                <a class="category-pill" href="{{ route('category.show', $category['slug']) }}">{{ $category['name'] }}</a>
            @empty
                <span class="category-pill category-pill--muted">Categories are being prepared</span>
            @endforelse
        </nav>
    </section>

    @foreach ($collectionSections as $section)
        <section class="site-shell section-block section-block--storefront available-products-section">
            <div class="section-heading section-heading--editorial">
                <div>
                    <p class="eyebrow">Curated Collection</p>
                    <h2>{{ $section['title'] }}</h2>
                    @if ($section['subtitle'])<p class="lede">{{ $section['subtitle'] }}</p>@endif
                </div>
                @if ($section['destination'] && $section['cta_label'])
                    <a href="{{ $section['destination'] }}">{{ $section['cta_label'] }}</a>
                @endif
            </div>
            <div class="product-grid product-grid--compact product-grid--available">
                @foreach ($section['products'] as $product)
                    <x-product.card :product="$product" compact />
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="site-shell section-block section-block--storefront available-products-section">
        <div class="section-heading section-heading--editorial">
            <div><p class="eyebrow">Marketplace Products</p><h2>Fresh picks from live sellers</h2></div>
            <a href="{{ route('shop') }}">View All Products</a>
        </div>
        <div class="product-grid product-grid--compact product-grid--available">
            @forelse ($products as $product)
                <x-product.card :product="$product" compact />
            @empty
                <x-ui.empty-state title="No products available" description="Published seller products will appear here automatically." />
            @endforelse
        </div>
    </section>

    @if ($promotions->isNotEmpty())
        <section class="site-shell marketplace-promotions" aria-label="Marketplace promotions">
            @foreach ($promotions as $promotion)
                <article class="marketplace-promotion">
                    @if ($promotion['image'])
                        <img src="{{ $promotion['image'] }}" alt="{{ $promotion['title'] }}" loading="lazy">
                    @else
                        <div class="marketplace-promotion__no-preview">No Preview Available</div>
                    @endif
                    <div>
                        <p class="eyebrow">Marketplace update</p>
                        <h2>{{ $promotion['title'] }}</h2>
                        @if ($promotion['subtitle'])<p>{{ $promotion['subtitle'] }}</p>@endif
                        @if ($promotion['destination'] && $promotion['cta_label'])
                            <a class="button button--secondary" href="{{ $promotion['destination'] }}">{{ $promotion['cta_label'] }}</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</x-layouts.customer>
