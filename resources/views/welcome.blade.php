<x-layouts.customer title="Sushako Shopping">
    @php
        $officialProducts = collect($products)->where('seller_official', true)->values();
        $nearbyProducts = collect($localProducts ?? [])->take(4);
    @endphp

    <section class="premium-hero-carousel storefront-carousel" data-hero-carousel data-reveal aria-label="Sushako storefront highlights">
        <div class="hero-carousel__track">
            @foreach ($banners as $banner)
                <article class="hero-carousel__slide @if($loop->first) is-active @endif" data-hero-slide>
                    <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async">
                    <div class="site-shell hero-carousel__content">
                        <p class="eyebrow">{{ $banner['tagline'] }}</p>
                        <h1>{{ $banner['title'] }}</h1>
                        <p>{{ $banner['subtitle'] }}</p>
                        <div class="hero-carousel__actions">
                            <a class="button button--primary" href="{{ $banner['href'] }}"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> {{ $banner['cta'] }}</a>
                            <a class="button button--glass" href="{{ $banner['secondary_href'] }}"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i> {{ $banner['secondary_cta'] }}</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <button class="hero-carousel__arrow hero-carousel__arrow--prev" type="button" data-hero-prev aria-label="Previous banner">‹</button>
        <button class="hero-carousel__arrow hero-carousel__arrow--next" type="button" data-hero-next aria-label="Next banner">›</button>
        <div class="hero-carousel__dots" role="tablist" aria-label="Hero banner navigation">
            @foreach ($banners as $banner)
                <button type="button" class="@if($loop->first) is-active @endif" data-hero-dot="{{ $loop->index }}" aria-label="Show {{ $banner['title'] }}"></button>
            @endforeach
        </div>
    </section>

    <section class="site-shell storefront-discovery-panel" data-reveal>
        <div>
            <p class="eyebrow">Marketplace Discovery</p>
            <h2>Everything you need, from stores you can trust.</h2>
            <p>Shop Sushako Official Store products and verified marketplace sellers with clear seller identity, secure checkout and delivery transparency.</p>
        </div>
        <div class="storefront-indicators">
            <span><i class="fa-solid fa-store" aria-hidden="true"></i><strong>Local Sellers</strong><small>Discover stores near your delivery area</small></span>
            <span><i class="fa-solid fa-certificate" aria-hidden="true"></i><strong>Official Store</strong><small>Sushako-owned products and support</small></span>
            <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><strong>Trusted Checkout</strong><small>Secure orders with clear invoices</small></span>
        </div>
    </section>

    <section class="site-shell section-block section-block--storefront" data-reveal>
        <div class="section-heading section-heading--editorial">
            <div>
                <p class="eyebrow">Shop by Category</p>
                <h2>Browse marketplace categories from trusted sellers.</h2>
            </div>
            <a href="{{ route('shop') }}">View All Categories</a>
        </div>
        <div class="category-grid category-grid--compact">
            @foreach ($activeCategories as $category)
                <a class="category-card category-card--compact" href="{{ route('department.show', $category['slug']) }}">
                    <img src="{{ $category['image'] }}" alt="{{ $category['name'] }} category" loading="lazy">
                    <span>{{ $category['name'] }}</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            @endforeach
        </div>
    </section>

    @if ($nearbyProducts->isNotEmpty())
        <section class="site-shell section-block section-block--storefront available-products-section" data-reveal>
            <div class="section-heading section-heading--editorial">
                <div>
                    <p class="eyebrow">Products Near You</p>
                    <h2>Fast local discovery from marketplace sellers.</h2>
                    <p class="lede">Set your delivery location to see availability and local delivery relevance.</p>
                </div>
                <a href="{{ route('shop') }}">View Nearby Products</a>
            </div>
            <div class="product-grid product-grid--compact product-grid--available">
                @foreach ($nearbyProducts as $featured)
                    <x-product.card :product="$featured" compact />
                @endforeach
            </div>
        </section>
    @endif

    <section class="site-shell section-block section-block--storefront available-products-section" data-reveal>
        <div class="section-heading section-heading--editorial">
            <div>
                <p class="eyebrow">Featured Products</p>
                <h2>Browse products from Sushako Official Store and trusted marketplace sellers.</h2>
                <p class="lede">Every product card shows seller identity, price, rating and delivery guidance before you buy.</p>
            </div>
            <a href="{{ route('shop') }}">View All Products</a>
        </div>
        <div class="product-grid product-grid--compact product-grid--available">
            @forelse ($products as $featured)
                <x-product.card :product="$featured" compact />
            @empty
                <div class="empty-cart empty-cart--catalog">
                    <p class="eyebrow">Being Selected</p>
                    <h2>Thoughtful products are being prepared.</h2>
                    <p class="lede">Published products will appear here automatically after they are added from admin.</p>
                </div>
            @endforelse
        </div>
    </section>

    @if ($officialProducts->isNotEmpty())
        <section class="site-shell section-block section-block--storefront available-products-section" data-reveal>
            <div class="section-heading section-heading--editorial">
                <div>
                    <p class="eyebrow">Sushako Official Store</p>
                    <h2>Products fulfilled by Sushako with direct store support.</h2>
                </div>
                <a href="{{ route('stores.show', 'sushako-official-store') }}">Visit Official Store</a>
            </div>
            <div class="product-grid product-grid--compact product-grid--available">
                @foreach ($officialProducts as $featured)
                    <x-product.card :product="$featured" compact />
                @endforeach
            </div>
        </section>
    @endif

    <section id="trusted-stores" class="site-shell seller-promo-carousel" data-seller-carousel data-reveal aria-label="Sushako seller platform">
        <div class="section-heading section-heading--editorial">
            <div>
                <p class="eyebrow">Trusted Local Stores</p>
                <h2>Seller tools built for real store operations.</h2>
            </div>
            <a href="{{ route('seller.login') }}">Become a Seller</a>
        </div>
        <div class="seller-promo-carousel__viewport">
            <div class="seller-promo-carousel__track" data-seller-carousel-track>
                @foreach ($sellerBanners as $banner)
                    <article class="seller-promo-slide" data-seller-slide>
                        <picture>
                            <source media="(max-width: 700px)" srcset="{{ $banner['mobile_image'] }}">
                            <img src="{{ $banner['desktop_image'] }}" alt="{{ $banner['title'] }}" loading="lazy" width="760" height="280">
                        </picture>
                        <div>
                            <h3>{{ $banner['title'] }}</h3>
                            <p>{{ $banner['description'] }}</p>
                            <a href="{{ $banner['cta_url'] }}">{{ $banner['cta_label'] }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
        <div class="seller-promo-carousel__controls">
            <button type="button" data-seller-prev aria-label="Previous seller banner"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
            <button type="button" data-seller-next aria-label="Next seller banner"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
    </section>

    <section class="site-shell delivery-strip" data-reveal>
        <div>
            <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
            <div>
                <strong>Complimentary Delivery Awaits</strong>
                <span>Enjoy complimentary delivery on eligible orders of &#8377;999 or more.</span>
            </div>
        </div>
        <a class="button button--ghost" href="{{ route('shop') }}">Start Shopping</a>
    </section>

    <section class="site-shell coming-section" id="whats-next" data-reveal>
        <div class="section-heading section-heading--editorial">
            <div>
                <p class="eyebrow">More Categories Opening Soon</p>
                <h2>More verified sellers and product categories are being onboarded.</h2>
            </div>
        </div>
        <div class="upcoming-preview-grid">
            <article class="upcoming-preview-card">
                <span>Seller Onboarding</span>
                <i class="fa-solid fa-house-chimney" aria-hidden="true"></i>
                <strong>Home & Living</strong>
                <small>More local home stores will appear after verification.</small>
            </article>
            <article class="upcoming-preview-card">
                <span>Seller Onboarding</span>
                <i class="fa-solid fa-heart-pulse" aria-hidden="true"></i>
                <strong>Health & Wellness</strong>
                <small>Wellness products will appear from trusted sellers.</small>
            </article>
            <article class="upcoming-preview-card">
                <span>Seller Onboarding</span>
                <i class="fa-solid fa-shirt" aria-hidden="true"></i>
                <strong>Women's Fashion</strong>
                <small>Clothing sellers can publish products after approval.</small>
            </article>
            <article class="upcoming-preview-card">
                <span>Seller Onboarding</span>
                <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                <strong>Kitchen Essentials</strong>
                <small>Kitchen products will expand as sellers go live.</small>
            </article>
            <article class="upcoming-preview-card">
                <span>Seller Onboarding</span>
                <i class="fa-solid fa-children" aria-hidden="true"></i>
                <strong>Kids & Family</strong>
                <small>Family products will be listed by verified stores.</small>
            </article>
            <article class="upcoming-preview-card">
                <span>Seller Onboarding</span>
                <i class="fa-solid fa-bookmark" aria-hidden="true"></i>
                <strong>Seasonal Picks</strong>
                <small>Seasonal marketplace products will appear here.</small>
            </article>
        </div>
    </section>

    <section class="site-shell stay-curious-section" data-reveal>
        <i class="fa-solid fa-store" aria-hidden="true"></i>
        <div>
            <p class="eyebrow">Marketplace Growth</p>
            <h2>More sellers are joining Sushako Shopping.</h2>
            <p>Browse current products now, or set your location to discover seller availability as new stores go live.</p>
        </div>
        <div>
            <a class="button button--primary" href="{{ route('shop') }}">Explore Products</a>
            <a class="button button--ghost" href="#trusted-stores">Find Stores Near You</a>
        </div>
    </section>

    <section class="trust-section trust-section--storefront" data-reveal>
        <div class="site-shell trust-grid trust-grid--storefront">
            <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><strong>Secure Checkout</strong><small>Protected payment experience.</small></span>
            <span><i class="fa-solid fa-store" aria-hidden="true"></i><strong>Seller Identity</strong><small>Know who you are buying from before checkout.</small></span>
            <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i><strong>Delivery Across India</strong><small>Serving eligible locations nationwide.</small></span>
            <span><i class="fa-solid fa-headset" aria-hidden="true"></i><strong>Order Assistance</strong><small>Support throughout your shopping journey.</small></span>
        </div>
    </section>
</x-layouts.customer>
