<x-layouts.customer title="Cart - Sushako Shopping">
    <section class="luxury-cart">
        <div class="site-shell luxury-cart__grid">
            <div class="luxury-cart__main" data-cart-summary>
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Shopping Bag</p>
                        <h1>Your Cart</h1>
                        <p class="lede">Review your products, adjust quantities, and continue to a secure Razorpay-ready checkout.</p>
                    </div>
                    <a class="button button--secondary" href="{{ route('shop') }}">Continue Shopping</a>
                </div>

                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <div class="cart-product-list">
                    @foreach ($cart as $key => $item)
                        <article class="cart-product-card" data-checkout-item data-cart-key="{{ $key }}">
                            <a class="cart-product-card__image" href="{{ route('products.show', $item['slug']) }}">
                                <img src="{{ $item['image'] }}" alt="{{ $item['product'] }}" loading="lazy">
                            </a>
                            <div class="cart-product-card__body">
                                <div>
                                    <p class="eyebrow">Sushako Store</p>
                                    <h2>{{ $item['product'] }}</h2>
                                    <p>{{ $item['colour'] }} / {{ $item['size'] }}</p>
                                </div>
                                <strong data-line-total>&#8377;{{ number_format($item['price'] * $item['quantity']) }}</strong>
                                <div class="cart-product-card__controls">
                                    <label>
                                        Quantity
                                        <input type="number" min="1" max="10" value="{{ $item['quantity'] }}" data-cart-quantity>
                                    </label>
                                    <button class="button button--secondary" type="button" data-cart-update>Update</button>
                                    <button class="button button--ghost" type="button" data-cart-remove>Remove</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="cart-summary-card" data-cart-summary>
                <div class="cart-summary-card__header">
                    <span>Order Summary</span>
                    <strong data-checkout-count>{{ count($cart) }} item{{ count($cart) === 1 ? '' : 's' }}</strong>
                </div>

                <div class="checkout-breakdown">
                    <span>Subtotal: <strong data-checkout-subtotal>&#8377;{{ number_format($summary['subtotal']) }}</strong></span>
                    <span>Shipping: <strong data-checkout-shipping>{{ $summary['shipping_label'] }}</strong></span>
                    <span class="checkout-shipping-note" data-checkout-shipping-note>{{ $summary['shipping_policy_text'] }}</span>
                    <span>GST: <strong data-checkout-tax>&#8377;{{ number_format($summary['tax']) }}</strong> included</span>
                    <span>Total: <strong data-checkout-total>&#8377;{{ number_format($summary['total']) }}</strong></span>
                </div>

                <div class="cart-trust-panel">
                    <p class="eyebrow">Trusted Checkout</p>
                    <div class="checkout-payment-logos" aria-label="Trusted checkout symbols">
                        <img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay">
                        <img src="{{ asset('assets/payments/upi.svg') }}" alt="UPI">
                        <img src="{{ asset('assets/payments/visa.svg') }}" alt="Visa">
                        <img src="{{ asset('assets/payments/mastercard.svg') }}" alt="Mastercard">
                    </div>
                    <div class="cart-support-grid">
                        <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp Support</span>
                        <span><i class="fa-solid fa-route" aria-hidden="true"></i> Track Order</span>
                        <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Fast Dispatch</span>
                        <span><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Easy Returns</span>
                    </div>
                </div>

                <a href="{{ route('checkout') }}" class="button button--primary">Proceed to Secure Checkout</a>
            </aside>
        </div>
    </section>
</x-layouts.customer>
