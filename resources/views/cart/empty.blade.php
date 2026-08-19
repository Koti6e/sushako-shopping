<x-layouts.customer title="Shopping Bag - Sushako Shopping">
    <section class="elite-cart-screen">
        <div class="site-shell elite-cart-shell">
            <div class="elite-empty-bag">
                <div class="elite-empty-bag__icon" aria-hidden="true">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <p class="eyebrow">Shopping Bag</p>
                <h1>Your cart is waiting</h1>
                <p class="lede">Discover something worth bringing home.</p>
                <div class="elite-empty-bag__actions">
                    <a href="{{ route('shop') }}" class="button button--primary">
                        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                        Explore Products
                    </a>
                    <a href="{{ route('shop') }}#categories" class="button button--secondary">
                        <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>
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
