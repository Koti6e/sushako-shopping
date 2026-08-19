<x-layouts.customer title="Shopping Bag - Sushako Shopping">
    @php
        $threshold = max(1, (int) ($shippingSettings->free_shipping_threshold ?? 999));
        $eligibleSubtotal = (int) $summary['subtotal'];
        $remainingForDelivery = max(0, $threshold - $eligibleSubtotal);
        $deliveryProgress = min(100, (int) round(($eligibleSubtotal / $threshold) * 100));
        $deliveryUnlocked = $summary['shipping_status'] === 'free';
    @endphp

    <section class="elite-cart-screen">
        <div class="site-shell elite-cart-shell">
            <header class="elite-cart-heading">
                <div class="elite-cart-heading__icon" aria-hidden="true">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <div>
                    <p class="eyebrow">Shopping Bag</p>
                    <h1>Your Shopping Bag</h1>
                    <p class="lede">Review your selections before checkout.</p>
                </div>
                <a class="button button--secondary" href="{{ route('shop') }}">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    Explore Products
                </a>
            </header>

            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            <div class="elite-cart-layout">
                <main class="elite-cart-main" data-cart-summary>
                    <section
                        class="delivery-progress-card @if($deliveryUnlocked) is-unlocked @endif"
                        data-delivery-progress
                        data-threshold="{{ $threshold }}"
                    >
                        <div>
                            <i class="fa-solid {{ $deliveryUnlocked ? 'fa-circle-check' : 'fa-truck-fast' }}" aria-hidden="true" data-delivery-icon></i>
                            <div>
                                <strong data-delivery-title>
                                    {{ $deliveryUnlocked ? 'Complimentary delivery unlocked' : "You're ₹".number_format($remainingForDelivery)." away from complimentary delivery" }}
                                </strong>
                                <p data-delivery-copy>
                                    {{ $deliveryUnlocked ? 'Delivery is on us for this order.' : "Add a little more to your bag and we'll take care of the delivery." }}
                                </p>
                            </div>
                        </div>
                        <span><i data-delivery-progress-bar style="width: {{ $deliveryProgress }}%"></i></span>
                    </section>

                    <section class="elite-cart-items" aria-label="Shopping bag items">
                        @foreach ($cart as $key => $item)
                            <article class="elite-cart-item" data-checkout-item data-cart-key="{{ $key }}">
                                <a class="elite-cart-item__image" href="{{ route('products.show', $item['slug']) }}">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['product'] }}" loading="lazy">
                                </a>
                                <div class="elite-cart-item__body">
                                    <div>
                                        <span class="elite-cart-item__seller">Sold by Sushako Select</span>
                                        <h2><a href="{{ route('products.show', $item['slug']) }}">{{ $item['product'] }}</a></h2>
                                        <p>{{ $item['colour'] }} / {{ $item['size'] }}</p>
                                    </div>
                                    <div class="elite-cart-item__meta">
                                        <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Delivery updates after order placement</span>
                                    </div>
                                    <div class="elite-cart-item__footer">
                                        <div class="elite-quantity" aria-label="Quantity selector">
                                            <button type="button" data-cart-step="-1" aria-label="Decrease quantity" @disabled($item['quantity'] <= 1)>
                                                <i class="fa-solid fa-minus" aria-hidden="true"></i>
                                            </button>
                                            <input type="number" min="1" max="10" value="{{ $item['quantity'] }}" data-cart-quantity data-cart-update aria-label="Quantity for {{ $item['product'] }}">
                                            <button type="button" data-cart-step="1" aria-label="Increase quantity">
                                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <strong data-line-total>&#8377;{{ number_format($item['price'] * $item['quantity']) }}</strong>
                                        <button class="elite-remove-button" type="button" data-cart-remove aria-label="Remove {{ $item['product'] }}">
                                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    @if (($recommendations ?? collect())->isNotEmpty())
                        <section class="elite-cart-recommendations">
                            <div class="section-heading">
                                <div>
                                    <p class="eyebrow"><i class="fa-solid fa-plus" aria-hidden="true"></i> You May Also Like</p>
                                    <h2>Complete Your Selection</h2>
                                    <p class="lede">A few thoughtful additions you may like.</p>
                                </div>
                            </div>
                            <div class="product-grid">
                                @foreach ($recommendations as $product)
                                    <x-product.card :product="$product" compact />
                                @endforeach
                            </div>
                        </section>
                    @endif
                </main>

                <aside class="elite-order-summary" data-cart-summary>
                    <div class="elite-order-summary__title">
                        <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                        <div>
                            <p class="eyebrow">Receipt</p>
                            <h2>Order Summary</h2>
                        </div>
                    </div>
                    <div class="elite-summary-lines">
                        <span><small>Items</small><strong data-checkout-count>{{ count($cart) }} item{{ count($cart) === 1 ? '' : 's' }}</strong></span>
                        <span><small>Subtotal</small><strong data-checkout-subtotal>&#8377;{{ number_format($summary['subtotal']) }}</strong></span>
                        <span><small>Delivery</small><strong data-checkout-shipping>{{ $deliveryUnlocked ? 'Complimentary' : $summary['shipping_label'] }}</strong></span>
                        @if (($summary['discount'] ?? 0) > 0)
                            <span><small>Discount</small><strong>-&#8377;{{ number_format($summary['discount']) }}</strong></span>
                        @endif
                        @if (($summary['tax'] ?? 0) > 0)
                            <span><small>Taxes</small><strong data-checkout-tax>&#8377;{{ number_format($summary['tax']) }}</strong></span>
                        @endif
                    </div>
                    <div class="elite-summary-total">
                        <span>Order Total</span>
                        <strong data-checkout-total>&#8377;{{ number_format($summary['total']) }}</strong>
                        @if (($summary['tax'] ?? 0) > 0)
                            <small>Inclusive of applicable taxes</small>
                        @endif
                    </div>
                    <a href="{{ route('checkout') }}" class="button button--primary elite-checkout-button">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        Proceed Securely
                    </a>
                    <div class="elite-summary-trust">
                        <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Secure checkout</span>
                        <span><i class="fa-solid fa-box" aria-hidden="true"></i> Carefully packed</span>
                        <span><i class="fa-solid fa-headset" aria-hidden="true"></i> Order assistance</span>
                    </div>
                </aside>
            </div>
        </div>

        <div class="elite-mobile-checkout" data-mobile-checkout>
            <div>
                <span>Order Total</span>
                <strong data-mobile-checkout-total>&#8377;{{ number_format($summary['total']) }}</strong>
            </div>
            <a href="{{ route('checkout') }}">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                Proceed
            </a>
        </div>
    </section>
</x-layouts.customer>
