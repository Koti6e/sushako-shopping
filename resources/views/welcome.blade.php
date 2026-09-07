<x-layouts.customer title="Sushako Shopping">
    <section class="premium-hero-carousel storefront-carousel marketplace-content-carousel" data-hero-carousel aria-label="Marketplace highlights">
        <div class="hero-carousel__track">
            @forelse ($heroContent as $content)
                <article class="hero-carousel__slide marketplace-content-hero @if ($loop->first) is-active @endif" data-hero-slide>
                    @if ($content['image'])
                        <img src="{{ $content['image'] }}" alt="{{ $content['title'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    @else
                        <div class="marketplace-content-no-preview"><i class="fa-regular fa-image" aria-hidden="true"></i> No Preview Available</div>
                    @endif
                    <div class="site-shell hero-carousel__content">
                        <p class="eyebrow">Sushako Marketplace</p>
                        <h1>{{ $content['title'] }}</h1>
                        @if ($content['subtitle'])
                            <p>{{ $content['subtitle'] }}</p>
                        @endif
                        @if ($content['destination'] && $content['cta_label'])
                            <div class="hero-carousel__actions">
                                <a class="button button--primary" href="{{ $content['destination'] }}">{{ $content['cta_label'] }}</a>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <article class="hero-carousel__slide marketplace-content-hero is-active" data-hero-slide>
                    <div class="marketplace-content-no-preview"><i class="fa-regular fa-image" aria-hidden="true"></i> No Preview Available</div>
                    <div class="site-shell hero-carousel__content">
                        <p class="eyebrow">Sushako Marketplace</p>
                        <h1>Marketplace content is being prepared.</h1>
                        <a class="button button--primary" href="{{ route('shop') }}">Browse Products</a>
                    </div>
                </article>
            @endforelse
        </div>
        @if ($heroContent->count() > 1)
            <button class="hero-carousel__arrow hero-carousel__arrow--prev" type="button" data-hero-prev aria-label="Previous banner">‹</button>
            <button class="hero-carousel__arrow hero-carousel__arrow--next" type="button" data-hero-next aria-label="Next banner">›</button>
            <div class="hero-carousel__dots" role="tablist" aria-label="Hero banner navigation">
                @foreach ($heroContent as $content)
                    <button type="button" class="@if ($loop->first) is-active @endif" data-hero-dot="{{ $loop->index }}" aria-label="Show {{ $content['title'] }}"></button>
                @endforeach
            </div>
        @endif
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
            <div><p class="eyebrow">Marketplace Catalog</p><h2>Shop by Category</h2></div>
            <a href="{{ route('shop') }}">View All Categories</a>
        </div>
        <div class="category-grid category-grid--compact">
            @forelse ($activeCategories as $category)
                <a class="category-card category-card--compact" href="{{ route('category.show', $category['slug']) }}">
                    @if ($category['image'])
                        <img src="{{ $category['image'] }}" alt="{{ $category['name'] }} category" loading="lazy">
                    @else
                        <span class="category-card__no-preview"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
                    @endif
                    <span>{{ $category['name'] }}</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            @empty
                <x-ui.empty-state title="Categories are being prepared" description="Available marketplace categories will appear here." />
            @endforelse
        </div>
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
            <div><p class="eyebrow">Marketplace Products</p><h2>Explore active listings</h2></div>
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
