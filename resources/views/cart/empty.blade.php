<x-layouts.customer title="Shopping Bag - Sushako Shopping">
    <section class="elite-cart-screen">
        <div class="site-shell elite-cart-shell">
            <div class="elite-empty-bag">
                <div class="elite-empty-bag__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.5 8.25V7a4.5 4.5 0 0 1 9 0v1.25"/><path d="M5.2 8.25h13.6l.85 11.15a2 2 0 0 1-2 2.1H6.35a2 2 0 0 1-2-2.1L5.2 8.25Z"/></svg>
                </div>
                <p class="eyebrow">Shopping Bag</p>
                <h1>Your cart is waiting</h1>
                <p class="lede">Discover something worth bringing home.</p>
                <div class="elite-empty-bag__actions">
                    <a href="{{ route('shop') }}" class="button button--primary">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.5 8.25V7a4.5 4.5 0 0 1 9 0v1.25"/><path d="M5.2 8.25h13.6l.85 11.15a2 2 0 0 1-2 2.1H6.35a2 2 0 0 1-2-2.1L5.2 8.25Z"/></svg>
                        Explore Products
                    </a>
                    <a href="{{ route('shop') }}#categories" class="button button--secondary">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6"/><rect x="14" y="4" width="6" height="6"/><rect x="4" y="14" width="6" height="6"/><rect x="14" y="14" width="6" height="6"/></svg>
                        Browse Categories
                    </a>
                </div>
            </div>

            <div class="elite-cart-trust">
                <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><strong>Secure Shopping</strong><small>Protected checkout with trusted payments.</small></span>
                <span><i class="fa-solid fa-box" aria-hidden="true"></i><strong>Thoughtfully Selected</strong><small>Quality products from trusted sellers.</small></span>
                <span><i class="fa-solid fa-headset" aria-hidden="true"></i><strong>Order Assistance</strong><small>Support before and after your purchase.</small></span>
            </div>

            @if (($recommendations ?? collect())->isNotEmpty())
                <section class="elite-cart-recommendations">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Selected For You</p>
                            <h2>Discover Something You'll Love</h2>
                            <p class="lede">Popular choices selected for you.</p>
                        </div>
                    </div>
                    <div class="product-grid">
                        @foreach ($recommendations as $product)
                            <x-product.card :product="$product" compact />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
</x-layouts.customer>
