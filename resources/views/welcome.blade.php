<x-layouts.customer
    title="Sushako Shopping"
    description="Discover products from independent sellers and local businesses on Sushako Shopping."
>
<style>
/* =========================================================
   SUSHAKO SHOPPING — STOREFRONT DESIGN SYSTEM
   Scoped exclusively to the homepage.
   ========================================================= */

.ss-home {
    --ss-forest: #12392e;
    --ss-forest-dark: #0c291f;
    --ss-emerald: #176b4d;
    --ss-gold: #c9a46b;
    --ss-ink: #202a24;
    --ss-muted: #78837b;
    --ss-border: #e9ede8;
    --ss-paper: #f7f8f5;
    --ss-white: #ffffff;
    --ss-radius: 16px;
    --ss-shadow: 0 12px 35px rgba(20, 45, 33, .065);

    color: var(--ss-ink);
    padding-bottom: 40px;
}

.ss-home *,
.ss-home *::before,
.ss-home *::after {
    box-sizing: border-box;
}

.ss-home a {
    text-decoration: none;
}

.ss-home button {
    font: inherit;
}

.ss-home .ss-container {
    width: min(1440px, calc(100% - 48px));
    margin-inline: auto;
}

/* Announcement */

.ss-home .ss-announcement {
    background: var(--ss-forest-dark);
    color: #fff;
    text-align: center;
    padding: 9px 15px;
    font-size: 11px;
    letter-spacing: .35px;
}

.ss-home .ss-announcement strong {
    color: #e4c18b;
}

/* Main navigation */

.ss-home .ss-navigation {
    background: var(--ss-white);
    border-bottom: 1px solid var(--ss-border);
}

.ss-home .ss-navigation-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 54px;
    overflow-x: auto;
    scrollbar-width: none;
}

.ss-home .ss-navigation-inner::-webkit-scrollbar {
    display: none;
}

.ss-home .ss-navigation a {
    flex-shrink: 0;
    color: #46534a;
    font-size: 12px;
    font-weight: 650;
    padding: 17px 0;
    transition: color .2s ease;
}

.ss-home .ss-navigation a:hover {
    color: var(--ss-emerald);
}

.ss-home .ss-navigation .ss-nav-highlight {
    color: #a77b42;
}

/* Hero */

.ss-home .ss-hero {
    position: relative;
    margin-top: 24px;
    border-radius: 20px;
    overflow: hidden;
    background: var(--ss-forest);
    color: white;
    isolation: isolate;
}

.ss-home .ss-hero-slides {
    position: relative;
}

.ss-home .ss-hero-slide {
    display: none;
    grid-template-columns: 1.05fr .95fr;
    min-height: 390px;
}

.ss-home .ss-hero-slide.is-active {
    display: grid;
    animation: ssHeroEnter .55s ease both;
}

@keyframes ssHeroEnter {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.ss-home .ss-hero-copy {
    padding: clamp(35px, 5vw, 76px);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.ss-home .ss-eyebrow {
    color: var(--ss-gold);
    text-transform: uppercase;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 17px;
}

.ss-home .ss-hero h1,
.ss-home .ss-hero h2 {
    color: white;
    font-size: clamp(34px, 4.2vw, 59px);
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: -2.2px;
    max-width: 600px;
}

.ss-home .ss-hero-copy > p:not(.ss-eyebrow) {
    color: #d1dfd5;
    font-size: 13px;
    line-height: 1.85;
    max-width: 440px;
    margin-top: 19px;
}

.ss-home .ss-hero-cta {
    display: inline-flex;
    align-items: center;
    gap: 13px;
    margin-top: 27px;
    padding: 13px 21px;
    background: var(--ss-gold);
    color: #18291f;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 800;
    transition: transform .2s, background .2s;
}

.ss-home .ss-hero-cta:hover {
    transform: translateY(-2px);
    background: #e6c58f;
}

.ss-home .ss-hero-media {
    position: relative;
    min-height: 390px;
    overflow: hidden;
}

.ss-home .ss-hero-media::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, var(--ss-forest), transparent 38%);
    pointer-events: none;
}

.ss-home .ss-hero-media img {
    width: 100%;
    height: 100%;
    min-height: 390px;
    object-fit: cover;
    object-position: center;
}

.ss-home .ss-hero-media--brand {
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #1b4d3a, #102e24);
}

.ss-home .ss-hero-media--brand img {
    width: min(65%, 310px);
    height: auto;
    min-height: 0;
    object-fit: contain;
}

.ss-home .ss-hero-media--brand::after {
    display: none;
}

/* Carousel controls */

.ss-home .ss-hero-controls {
    position: absolute;
    z-index: 5;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 15px;
}

.ss-home .ss-hero-controls > button {
    width: 33px;
    height: 33px;
    border: 1px solid rgba(255,255,255,.35);
    background: rgba(255,255,255,.1);
    color: white;
    border-radius: 50%;
    display: grid;
    place-items: center;
    cursor: pointer;
}

.ss-home .ss-hero-controls svg,
.ss-home .ss-rail-controls svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
}

.ss-home .ss-hero-dots {
    display: flex;
    gap: 7px;
    align-items: center;
}

.ss-home .ss-hero-dots button {
    width: 7px;
    height: 7px;
    border: 0;
    border-radius: 20px;
    background: rgba(255,255,255,.4);
    cursor: pointer;
    padding: 0;
    transition: width .2s, background .2s;
}

.ss-home .ss-hero-dots button[aria-pressed="true"] {
    width: 23px;
    background: var(--ss-gold);
}

/* Discovery layout */

.ss-home .ss-discovery {
    display: grid;
    grid-template-columns: 245px minmax(0, 1fr);
    gap: 27px;
    margin-top: 42px;
    align-items: start;
}

.ss-home .ss-category-panel {
    background: white;
    border: 1px solid var(--ss-border);
    border-radius: var(--ss-radius);
    padding: 22px 18px;
    position: sticky;
    top: 15px;
}

.ss-home .ss-category-panel h2 {
    font-size: 19px;
    font-weight: 800;
    letter-spacing: -.6px;
}

.ss-home .ss-category-panel .ss-eyebrow {
    margin-bottom: 7px;
}

.ss-home .ss-category-list {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.ss-home .ss-category-list a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 10px;
    color: #46544b;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
    transition: background .2s, color .2s;
}

.ss-home .ss-category-list a:hover,
.ss-home .ss-category-list a.is-active {
    background: #edf4ef;
    color: var(--ss-forest);
}

.ss-home .ss-category-list small {
    color: #929c94;
    font-size: 10px;
}

/* Product rails */

.ss-home .ss-rail-section {
    min-width: 0;
    margin-bottom: 39px;
}

.ss-home .ss-rail-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 17px;
}

.ss-home .ss-rail-heading h2 {
    font-size: 23px;
    line-height: 1.2;
    letter-spacing: -.8px;
    font-weight: 800;
}

.ss-home .ss-rail-heading .ss-eyebrow {
    margin-bottom: 6px;
}

.ss-home .ss-rail-heading p {
    color: var(--ss-muted);
    font-size: 11px;
    margin-top: 5px;
}

.ss-home .ss-rail-controls {
    display: flex;
    align-items: center;
    gap: 7px;
}

.ss-home .ss-rail-controls button {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border: 1px solid var(--ss-border);
    border-radius: 50%;
    background: white;
    color: var(--ss-forest);
    cursor: pointer;
}

.ss-home .ss-rail-controls button:hover {
    background: var(--ss-forest);
    color: white;
}

.ss-home .ss-rail-controls a {
    color: var(--ss-emerald);
    font-size: 11px;
    font-weight: 750;
    margin-right: 8px;
}

.ss-home .ss-product-rail {
    display: flex;
    align-items: stretch;
    gap: 14px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #c8d4ca transparent;
    padding-bottom: 12px;
}

.ss-home .ss-product-rail:focus-visible {
    outline: 2px solid var(--ss-emerald);
    outline-offset: 4px;
}

.ss-home .ss-product-rail__item {
    flex: 0 0 210px;
    min-width: 0;
    scroll-snap-align: start;
}

/* Existing product component sizing */

.ss-home .ss-product-rail__item > * {
    height: 100%;
}

.ss-home .ss-product-rail .product-card,
.ss-home .ss-product-grid .product-card {
    height: 100%;
}

/* Collections */

.ss-home .ss-collection {
    margin-top: 30px;
    margin-bottom: 45px;
}

.ss-home .ss-collection-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.ss-home .ss-collection-heading h2 {
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    letter-spacing: -1px;
}

.ss-home .ss-collection-heading .ss-eyebrow {
    margin-bottom: 7px;
}

.ss-home .ss-collection-heading .ss-lede {
    color: var(--ss-muted);
    font-size: 12px;
    margin-top: 8px;
    line-height: 1.7;
}

.ss-home .ss-collection-link {
    color: var(--ss-emerald);
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.ss-home .ss-product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 17px;
}

.ss-home .ss-product-grid > * {
    min-width: 0;
}

/* Editorial accent */

.ss-home .ss-editorial-strip {
    margin-top: 45px;
    margin-bottom: 45px;
    background: linear-gradient(120deg, #102e24, #1d533c);
    color: white;
    padding: 38px;
    border-radius: 17px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
}

.ss-home .ss-editorial-strip h2 {
    font-size: clamp(23px, 3vw, 34px);
    font-weight: 800;
    letter-spacing: -1px;
}

.ss-home .ss-editorial-strip p {
    color: #cfddd3;
    font-size: 12px;
    margin-top: 8px;
    max-width: 560px;
    line-height: 1.8;
}

.ss-home .ss-editorial-strip a {
    flex-shrink: 0;
    background: var(--ss-gold);
    color: #1b2b22;
    padding: 13px 21px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
}

/* Responsive */

@media (max-width: 1050px) {
    .ss-home .ss-discovery {
        grid-template-columns: 210px minmax(0, 1fr);
        gap: 18px;
    }

    .ss-home .ss-product-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .ss-home .ss-product-rail__item {
        flex-basis: 185px;
    }
}

@media (max-width: 760px) {
    .ss-home {
        padding-bottom: 20px;
    }

    .ss-home .ss-container {
        width: calc(100% - 28px);
    }

    .ss-home .ss-announcement {
        font-size: 10px;
    }

    .ss-home .ss-navigation-inner {
        justify-content: flex-start;
        gap: 23px;
        min-height: 44px;
    }

    .ss-home .ss-navigation a {
        font-size: 11px;
        padding: 13px 0;
    }

    .ss-home .ss-hero {
        margin-top: 13px;
        border-radius: 13px;
    }

    .ss-home .ss-hero-slide,
    .ss-home .ss-hero-slide.is-active {
        grid-template-columns: 1fr;
        min-height: 0;
    }

    .ss-home .ss-hero-copy {
        padding: 35px 23px 26px;
    }

    .ss-home .ss-hero h1,
    .ss-home .ss-hero h2 {
        font-size: 37px;
        letter-spacing: -1.4px;
    }

    .ss-home .ss-hero-copy > p:not(.ss-eyebrow) {
        font-size: 12px;
        margin-top: 13px;
    }

    .ss-home .ss-hero-cta {
        margin-top: 19px;
        padding: 11px 16px;
    }

    .ss-home .ss-hero-media {
        min-height: 185px;
        max-height: 220px;
    }

    .ss-home .ss-hero-media img {
        min-height: 185px;
        max-height: 220px;
    }

    .ss-home .ss-hero-media::after {
        background: linear-gradient(180deg, var(--ss-forest), transparent 30%);
    }

    .ss-home .ss-hero-media--brand img {
        min-height: 0;
        max-height: 150px;
    }

    .ss-home .ss-hero-controls {
        bottom: 12px;
    }

    .ss-home .ss-discovery {
        display: block;
        margin-top: 25px;
    }

    .ss-home .ss-category-panel {
        position: static;
        padding: 17px;
        margin-bottom: 28px;
    }

    .ss-home .ss-category-panel h2 {
        font-size: 17px;
    }

    .ss-home .ss-category-list {
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 5px;
        margin-top: 13px;
        scrollbar-width: none;
    }

    .ss-home .ss-category-list::-webkit-scrollbar {
        display: none;
    }

    .ss-home .ss-category-list a {
        flex: 0 0 auto;
        border: 1px solid var(--ss-border);
        padding: 9px 13px;
        white-space: nowrap;
        font-size: 11px;
    }

    .ss-home .ss-rail-section {
        margin-bottom: 29px;
    }

    .ss-home .ss-rail-heading h2 {
        font-size: 19px;
    }

    .ss-home .ss-rail-controls button {
        width: 30px;
        height: 30px;
    }

    .ss-home .ss-product-rail {
        gap: 10px;
    }

    .ss-home .ss-product-rail__item {
        flex-basis: 155px;
    }

    .ss-home .ss-collection {
        margin-top: 25px;
        margin-bottom: 32px;
    }

    .ss-home .ss-collection-heading h2 {
        font-size: 22px;
    }

    .ss-home .ss-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .ss-home .ss-editorial-strip {
        flex-direction: column;
        align-items: flex-start;
        padding: 25px;
        margin-top: 30px;
        margin-bottom: 30px;
    }
}

@media (max-width: 380px) {
    .ss-home .ss-product-rail__item {
        flex-basis: 145px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ss-home *,
    .ss-home *::before,
    .ss-home *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>

<div class="ss-home">

    <div class="ss-announcement">
        Discover more from independent businesses.
        <strong>Welcome to Sushako Shopping.</strong>
    </div>

    <nav class="ss-navigation" aria-label="Marketplace navigation">
        <div class="ss-container ss-navigation-inner">
            <a href="{{ route('shop') }}">All Products</a>
            <a href="#categories">Categories</a>
            <a href="#fresh-picks">Fresh Picks</a>
            <a href="#collections">Collections</a>
            <a href="{{ route('stores.index') }}">Explore Stores</a>
            <a href="{{ route('seller.login') }}" class="ss-nav-highlight">Sell on Sushako ↗</a>
        </div>
    </nav>

    {{-- HERO CAROUSEL: Existing dynamic banner data retained --}}
    <section
        class="ss-container ss-hero"
        data-hero-carousel
        aria-label="Marketplace promotions"
    >
        <div class="ss-hero-slides">

            @foreach ($heroBanners as $banner)
                <article
                    class="ss-hero-slide @if($loop->first) is-active @endif"
                    data-hero-slide
                    role="group"
                    aria-roledescription="slide"
                    aria-label="{{ $loop->iteration }} of 5"
                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                >
                    <div class="ss-hero-copy">
                        @if ($loop->first)
                            <p class="ss-eyebrow">Shop Local. Shop Smart. Shop Sushako.</p>
                        @else
                            <p class="ss-eyebrow">{{ $banner['eyebrow'] }}</p>
                        @endif

                        <h1>{{ $banner['title'] }}</h1>

                        @if ($banner['subtitle'])
                            <p>{{ $banner['subtitle'] }}</p>
                        @endif

                        <a class="ss-hero-cta" href="{{ $banner['href'] }}">
                            {{ $banner['cta'] }}
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="ss-hero-media">
                        @if ($banner['image'])
                            <img
                                src="{{ $banner['image'] }}"
                                alt="{{ $banner['title'] }}"
                                width="720"
                                height="360"
                                @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                            >
                        @else
                            <img
                                src="{{ asset('assets/brand/sushako-shopping-official-icon-512.png') }}"
                                alt="Sushako"
                                width="512"
                                height="512"
                                loading="lazy"
                            >
                        @endif
                    </div>
                </article>
            @endforeach

            {{-- Existing seller promotion retained --}}
            <article
                class="ss-hero-slide"
                data-hero-slide
                role="group"
                aria-roledescription="slide"
                aria-label="5 of 5"
                aria-hidden="true"
            >
                <div class="ss-hero-copy">
                    <p class="ss-eyebrow">For independent businesses</p>
                    <h2>Bring your business to Sushako.</h2>
                    <p>
                        Bring your products to a growing marketplace of
                        local and independent stores.
                    </p>
                    <a class="ss-hero-cta" href="{{ route('seller.login') }}">
                        Become a Seller
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="ss-hero-media ss-hero-media--brand">
                    <img
                        src="{{ asset('assets/brand/sushako-shopping-official-icon-512.png') }}"
                        alt="Sushako marketplace"
                        width="512"
                        height="512"
                        loading="lazy"
                    >
                </div>
            </article>

        </div>

        <div class="ss-hero-controls">
            <button type="button" data-hero-prev aria-label="Previous promotion">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M19 12H5m7 7-7-7 7-7"/>
                </svg>
            </button>

            <div class="ss-hero-dots" aria-label="Choose promotion">
                @for ($slide = 0; $slide < 5; $slide++)
                    <button
                        type="button"
                        data-hero-dot
                        aria-label="Show promotion {{ $slide + 1 }}"
                        aria-pressed="{{ $slide === 0 ? 'true' : 'false' }}"
                    ></button>
                @endfor
            </div>

            <button type="button" data-hero-next aria-label="Next promotion">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h14m-7-7 7 7-7 7"/>
                </svg>
            </button>
        </div>
    </section>

    {{-- PRODUCT DISCOVERY --}}
    <section
        class="ss-container ss-discovery"
        id="categories"
        aria-label="Marketplace discovery"
    >

        {{-- MASTER CATEGORY NAVIGATION --}}
        <aside class="ss-category-panel">
            <p class="ss-eyebrow">Marketplace</p>
            <h2>Shop by Category</h2>

            <nav class="ss-category-list" aria-label="Master categories">
                <a class="is-active" href="{{ route('shop') }}">
                    <span>All Products</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('category.show', $category['slug']) }}">
                        <span>{{ $category['name'] }}</span>

                        @if ((int) $category['product_count'] > 0)
                            <small>{{ $category['product_count'] }}</small>
                        @endif
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="ss-discovery-content">

            @if (isset($featuredCampaigns) && $featuredCampaigns->isNotEmpty())
                <section class="ss-rail-section" aria-labelledby="featured-products-heading">
                    <div class="ss-rail-heading"><div><p class="ss-eyebrow">Sponsored marketplace placement</p><h2 id="featured-products-heading">Featured Products</h2><p>Seller-sponsored products, clearly labelled and independently ranked.</p></div></div>
                    <div class="ss-product-rail" data-product-rail tabindex="0" role="region" aria-label="Featured products">
                        @foreach ($featuredCampaigns as $campaign)
                            <div class="ss-product-rail__item"><span class="status-badge">Sponsored</span><a href="{{ route('featured.click', $campaign) }}"><x-product.card :product="\App\Support\ProductCatalog::productArray($campaign->product)" compact /></a></div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- FRESH PICKS --}}
            <section
                class="ss-rail-section"
                id="fresh-picks"
                aria-labelledby="fresh-picks-heading"
            >
                <div class="ss-rail-heading">
                    <div>
                        <p class="ss-eyebrow">From live marketplace sellers</p>
                        <h2 id="fresh-picks-heading">Fresh Picks</h2>
                        <p>Discover products from our growing seller community.</p>
                    </div>

                    <div class="ss-rail-controls">
                        <button type="button" data-rail-prev aria-label="Scroll fresh picks left">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19 12H5m7 7-7-7 7-7"/>
                            </svg>
                        </button>

                        <button type="button" data-rail-next aria-label="Scroll fresh picks right">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    class="ss-product-rail"
                    data-product-rail
                    tabindex="0"
                    role="region"
                    aria-label="Fresh picks"
                >
                    @forelse ($products as $product)
                        <div class="ss-product-rail__item">
                            <x-product.card :product="$product" compact />
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="Products are on their way"
                            description="Published products from marketplace sellers will appear here."
                        />
                    @endforelse
                </div>
            </section>

            {{-- CATEGORY DISCOVERY --}}
            @foreach ($categoryDiscovery as $discovery)
                <section
                    class="ss-rail-section"
                    aria-labelledby="category-rail-{{ $discovery['category']['slug'] }}"
                >
                    <div class="ss-rail-heading">
                        <div>
                            <p class="ss-eyebrow">
                                {{ $discovery['category']['name'] }}
                            </p>

                            <h2 id="category-rail-{{ $discovery['category']['slug'] }}">
                                Popular in {{ $discovery['category']['name'] }}
                            </h2>
                        </div>

                        <div class="ss-rail-controls">
                            <a href="{{ route('category.show', $discovery['category']['slug']) }}">
                                Explore →
                            </a>

                            <button
                                type="button"
                                data-rail-prev
                                aria-label="Scroll {{ $discovery['category']['name'] }} left"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M19 12H5m7 7-7-7 7-7"/>
                                </svg>
                            </button>

                            <button
                                type="button"
                                data-rail-next
                                aria-label="Scroll {{ $discovery['category']['name'] }} right"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12h14m-7-7 7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        class="ss-product-rail"
                        data-product-rail
                        tabindex="0"
                        role="region"
                        aria-label="{{ $discovery['category']['name'] }} products"
                    >
                        @foreach ($discovery['products'] as $product)
                            <div class="ss-product-rail__item">
                                <x-product.card :product="$product" compact />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

        </div>
    </section>

    {{-- DYNAMIC CURATED COLLECTIONS --}}
    <div id="collections">
        @foreach ($collectionSections as $section)
            <section class="ss-container ss-collection">

                <div class="ss-collection-heading">
                    <div>
                        <p class="ss-eyebrow">Curated Collection</p>
                        <h2>{{ $section['title'] }}</h2>

                        @if ($section['subtitle'])
                            <p class="ss-lede">{{ $section['subtitle'] }}</p>
                        @endif
                    </div>

                    @if ($section['destination'] && $section['cta_label'])
                        <a
                            class="ss-collection-link"
                            href="{{ $section['destination'] }}"
                        >
                            {{ $section['cta_label'] }} →
                        </a>
                    @endif
                </div>

                <div class="ss-product-grid">
                    @foreach ($section['products'] as $product)
                        <x-product.card :product="$product" compact />
                    @endforeach
                </div>

            </section>
        @endforeach
    </div>

    {{-- SELLER DISCOVERY --}}
    <section class="ss-container ss-editorial-strip">
        <div>
            <p class="ss-eyebrow">For independent businesses</p>
            <h2>Every great business deserves to be discovered.</h2>
            <p>
                Join the Sushako seller community and showcase your products
                to customers exploring independent businesses.
            </p>
        </div>

        <a href="{{ route('seller.login') }}">
            Start Selling →
        </a>
    </section>

</div>

<script>
(() => {
    'use strict';

    const roots = document.querySelectorAll('.ss-home[data-ss-initialized]');

    if (roots.length) return;

    const root = document.querySelector('.ss-home');

    if (!root) return;

    root.setAttribute('data-ss-initialized', 'true');

    // HERO CAROUSEL
    const carousel = root.querySelector('[data-hero-carousel]');

    if (carousel) {
        const slides = Array.from(carousel.querySelectorAll('[data-hero-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-hero-dot]'));
        const previous = carousel.querySelector('[data-hero-prev]');
        const next = carousel.querySelector('[data-hero-next]');

        let current = 0;
        let timer = null;

        const showSlide = (index) => {
            if (!slides.length) return;

            current = (index + slides.length) % slides.length;

            slides.forEach((slide, i) => {
                const active = i === current;

                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });

            dots.forEach((dot, i) => {
                dot.setAttribute(
                    'aria-pressed',
                    i === current ? 'true' : 'false'
                );
            });
        };

        const stopTimer = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        const startTimer = () => {
            stopTimer();

            if (slides.length < 2) return;

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            timer = window.setInterval(() => {
                showSlide(current + 1);
            }, 6500);
        };

        previous?.addEventListener('click', () => {
            showSlide(current - 1);
            startTimer();
        });

        next?.addEventListener('click', () => {
            showSlide(current + 1);
            startTimer();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                startTimer();
            });
        });

        carousel.addEventListener('mouseenter', stopTimer);
        carousel.addEventListener('mouseleave', startTimer);

        carousel.addEventListener('focusin', stopTimer);
        carousel.addEventListener('focusout', startTimer);

        showSlide(0);
        startTimer();
    }

    // PRODUCT RAIL CONTROLS
    root.querySelectorAll('.ss-rail-section').forEach(section => {
        const rail = section.querySelector('[data-product-rail]');

        if (!rail) return;

        const previous = section.querySelector('[data-rail-prev]');
        const next = section.querySelector('[data-rail-next]');

        const scrollAmount = () => Math.max(
            220,
            Math.round(rail.clientWidth * 0.8)
        );

        previous?.addEventListener('click', () => {
            rail.scrollBy({
                left: -scrollAmount(),
                behavior: 'smooth'
            });
        });

        next?.addEventListener('click', () => {
            rail.scrollBy({
                left: scrollAmount(),
                behavior: 'smooth'
            });
        });
    });
})();
</script>

</x-layouts.customer>
