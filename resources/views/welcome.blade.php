<x-layouts.customer title="Sushako Shopping">
    <section class="site-shell marketplace-hero" data-hero-carousel aria-label="Marketplace promotions">
        <div class="marketplace-hero__slides">
            @foreach ($heroBanners as $banner)
                <article class="marketplace-hero__slide @if($loop->first) is-active @endif" data-hero-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of 5" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                    <div class="marketplace-hero__copy">
                        @if ($loop->first)<p class="eyebrow">Shop Local. Shop Smart. Shop Sushako.</p>@else<p class="eyebrow">{{ $banner['eyebrow'] }}</p>@endif
                        <h1>{{ $banner['title'] }}</h1>
                        @if ($banner['subtitle'])<p>{{ $banner['subtitle'] }}</p>@endif
                        <a class="button button--primary" href="{{ $banner['href'] }}">{{ $banner['cta'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="marketplace-hero__media">
                        @if ($banner['image'])
                            <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" width="720" height="360" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        @else
                            <img src="{{ asset('assets/brand/sushako-shopping-official-icon-512.png') }}" alt="Sushako" width="512" height="512" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        @endif
                    </div>
                </article>
            @endforeach

            <article class="marketplace-hero__slide marketplace-hero__slide--seller" data-hero-slide role="group" aria-roledescription="slide" aria-label="5 of 5" aria-hidden="true">
                <div class="marketplace-hero__copy">
                    <p class="eyebrow">For independent businesses</p>
                    <h2>Sell on Sushako</h2>
                    <p>Bring your products to a growing marketplace of local and independent stores.</p>
                    <a class="button button--primary" href="{{ route('seller.login') }}">Become a Seller <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="marketplace-hero__media marketplace-hero__media--brand">
                    <img src="{{ asset('assets/brand/sushako-shopping-official-icon-512.png') }}" alt="Sushako marketplace" width="512" height="512" loading="lazy">
                </div>
            </article>
        </div>
        <div class="marketplace-hero__controls">
            <button type="button" data-hero-prev aria-label="Previous promotion"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg></button>
            <div class="marketplace-hero__indicators" aria-label="Choose promotion">
                @for ($slide = 0; $slide < 5; $slide++)
                    <button type="button" data-hero-dot aria-label="Show promotion {{ $slide + 1 }}" aria-pressed="{{ $slide === 0 ? 'true' : 'false' }}"></button>
                @endfor
            </div>
            <button type="button" data-hero-next aria-label="Next promotion"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg></button>
        </div>
    </section>

    <section class="site-shell discovery-workspace" id="categories" aria-label="Shop by category and discover products">
        <aside class="master-category-panel">
            <div class="master-category-panel__heading">
                <p class="eyebrow">Marketplace</p>
                <h2>Shop by Category</h2>
            </div>
            <nav aria-label="Master categories">
                <a class="is-active" href="{{ route('shop') }}"><span>All Products</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @foreach ($categories as $category)
                    <a href="{{ route('category.show', $category['slug']) }}">
                        <span>{{ $category['name'] }}</span>
                        <small>{{ $category['product_count'] }}</small>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="discovery-rails">
            <section class="product-rail-section" aria-labelledby="fresh-picks-heading">
                <div class="product-rail-heading">
                    <div><p class="eyebrow">From live marketplace sellers</p><h2 id="fresh-picks-heading">Fresh picks</h2></div>
                    <div class="product-rail-controls">
                        <button type="button" data-rail-prev aria-label="Scroll fresh picks left"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg></button>
                        <button type="button" data-rail-next aria-label="Scroll fresh picks right"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg></button>
                    </div>
                </div>
                <div class="product-rail" data-product-rail tabindex="0" role="region" aria-label="Fresh picks">
                    @forelse ($products as $product)
                        <div class="product-rail__item"><x-product.card :product="$product" compact /></div>
                    @empty
                        <x-ui.empty-state title="Products are on their way" description="Published products from marketplace sellers will appear here." />
                    @endforelse
                </div>
            </section>

            @foreach ($categoryDiscovery as $discovery)
                <section class="product-rail-section" aria-labelledby="category-rail-{{ $discovery['category']['slug'] }}">
                    <div class="product-rail-heading">
                        <div><p class="eyebrow">{{ $discovery['category']['name'] }}</p><h2 id="category-rail-{{ $discovery['category']['slug'] }}">Popular in {{ $discovery['category']['name'] }}</h2></div>
                        <div class="product-rail-controls">
                            <a href="{{ route('category.show', $discovery['category']['slug']) }}">Explore category</a>
                            <button type="button" data-rail-prev aria-label="Scroll {{ $discovery['category']['name'] }} left"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg></button>
                            <button type="button" data-rail-next aria-label="Scroll {{ $discovery['category']['name'] }} right"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg></button>
                        </div>
                    </div>
                    <div class="product-rail" data-product-rail tabindex="0" role="region" aria-label="{{ $discovery['category']['name'] }} products">
                        @foreach ($discovery['products'] as $product)
                            <div class="product-rail__item"><x-product.card :product="$product" compact /></div>
                        @endforeach
                    </div>
                </section>
            @endforeach
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
</x-layouts.customer>