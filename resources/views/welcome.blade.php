<x-layouts.customer title="Sushako Shopping">
    <section class="premium-hero-carousel" data-hero-carousel aria-label="Premium fashion banner carousel">
        <div class="hero-carousel__track">
            @foreach ($banners as $banner)
                <article class="hero-carousel__slide @if($loop->first) is-active @endif" data-hero-slide>
                    <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async">
                    <div class="site-shell hero-carousel__content">
                        <p class="eyebrow">{{ $banner['tagline'] }}</p>
                        <h1>{{ $banner['title'] }}</h1>
                        <p>{{ $banner['subtitle'] }}</p>
                        <div class="hero-carousel__actions">
                            <a class="button button--primary" href="{{ $banner['href'] }}">{{ $banner['cta'] }}</a>
                            <a class="button button--glass" href="{{ $banner['secondary_href'] }}">{{ $banner['secondary_cta'] }}</a>
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

    <section class="site-shell section-block">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Luxury Welcome</p>
                <h2>Shop with Sushako</h2>
            </div>
            <a href="{{ route('shop') }}">Shop All</a>
        </div>
        <div class="category-grid">
            @foreach ($categories as $category)
                <a class="category-card" href="{{ route('department.show', $category['slug']) }}">
                    <img src="{{ $category['image'] }}" alt="{{ $category['name'] }} category" loading="lazy">
                    <span>{{ $category['name'] }}</span>
                    <small>{{ implode(' · ', array_slice(array_keys($category['sidebar']), 0, 4)) }}</small>
                </a>
            @endforeach
        </div>
    </section>

    <section class="site-shell section-block">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Sushako Picks</p>
                <h2>Thoughtfully chosen for your home and style</h2>
            </div>
            <a href="{{ route('shop') }}">View All</a>
        </div>
        <div class="product-grid">
            @forelse ($products as $featured)
                <x-product.card :product="$featured" />
            @empty
                <div class="empty-cart empty-cart--catalog">
                    <p class="eyebrow">Launching Soon</p>
                    <h2>Products coming soon</h2>
                    <p class="lede">Homemade health mix, masala powders and women's clothing will appear here as soon as products are added from admin.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="site-shell section-block">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Sushako Promise</p>
                <h2>Trusted shopping with careful delivery.</h2>
            </div>
            <a href="{{ route('shop') }}">Shop Picks</a>
        </div>
        <div class="local-market-strip">
            <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Secure Payments</span>
            <span><i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Invoice Ready</span>
            <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp Support</span>
            <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Fast Dispatch</span>
        </div>
        <div class="product-grid">
            @forelse ($localProducts as $product)
                <x-product.card :product="$product" />
            @empty
                <div class="empty-cart empty-cart--catalog">
                    <p class="eyebrow">Sushako Store</p>
                    <h2>Products coming soon</h2>
                    <p class="lede">Fresh products will show automatically after they are added and published in admin.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="trust-section" id="about">
        <div class="site-shell service-grid">
            <article>
                <span>01</span>
                <h2>Trusted Store</h2>
                <p>Curated shopping with visible support and clear order details.</p>
            </article>
            <article>
                <span>02</span>
                <h2>Secure Payments</h2>
                <p>Protected payment options for UPI, cards and netbanking.</p>
            </article>
            <article>
                <span>03</span>
                <h2>Fast Dispatch</h2>
                <p>Same-day dispatch before 2 PM, with free shipping above &#8377;999.</p>
            </article>
        </div>
    </section>

    <section class="site-shell section-block">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Customer Reviews</p>
                <h2>Shoppers love the smooth experience</h2>
            </div>
        </div>
        <div class="review-grid">
            @foreach ($reviews as $review)
                <article>
                    <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }} review avatar" loading="lazy">
                    <strong>{{ $review['name'] }} · {{ $review['rating'] }} stars</strong>
                    <p>{{ $review['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="newsletter-section" id="contact">
        <div class="site-shell newsletter-panel">
            <div>
                <p class="eyebrow">Newsletter</p>
                <h2>Store offers, new arrivals and Sushako updates.</h2>
            </div>
            <form>
                <label class="sr-only" for="newsletter-email">Email</label>
                <input id="newsletter-email" type="email" placeholder="Email address">
                <button class="button button--primary" type="submit">Join</button>
            </form>
        </div>
    </section>
</x-layouts.customer>
