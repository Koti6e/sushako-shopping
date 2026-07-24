<x-layouts.customer title="Checkout - Sushako Shopping">
    <section class="luxury-checkout">
        <div class="site-shell luxury-checkout__grid">
            <form class="checkout-panel luxury-checkout__form" method="POST" action="{{ route('checkout.place') }}">
                @csrf
                <x-brand.logo loading="eager" />
                <p class="eyebrow">Secure Checkout</p>
                <h1>Trusted delivery, protected payment</h1>
                <p class="lede">Place your order from your Sushako account so invoices, tracking and support stay protected.</p>

                <div class="checkout-trust-strip" aria-label="Checkout trust signals">
                    <article class="trust-proof-card trust-proof-card--brand">
                        <img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay">
                        <span>Secure payment gateway ready</span>
                    </article>
                    <article class="trust-proof-card">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <span>WhatsApp support for order help</span>
                    </article>
                    <article class="trust-proof-card">
                        <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
                        <span>Dispatch and delivery tracking</span>
                    </article>
                </div>

                @if ($errors->any())
                    <div class="status-banner">{{ $errors->first() }}</div>
                @endif
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <section class="checkout-section">
                    <div>
                        <p class="eyebrow">Contact</p>
                        <h2>Who should receive this order?</h2>
                    </div>
                    <div class="account-form">
                        <label for="customer_name">Full Name</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', $user?->name) }}" required autocomplete="name">

                        <label for="customer_phone">Mobile Number</label>
                        <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $user?->phone) }}" required autocomplete="tel">

                        <label for="customer_email">Email Address</label>
                        <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', $user?->email) }}" autocomplete="email">
                    </div>
                </section>

                <section class="checkout-section">
                    <div>
                        <p class="eyebrow">Delivery Address</p>
                        <h2>Where should we deliver?</h2>
                    </div>
                    @if ($addresses->isNotEmpty())
                        <div class="saved-checkout-addresses" data-checkout-addresses>
                            @foreach ($addresses as $address)
                                <label class="saved-checkout-address">
                                    <input type="radio" name="saved_address_id" value="{{ $address->id }}"
                                        data-checkout-address
                                        data-customer-name="{{ $address->recipient_name }}"
                                        data-customer-phone="{{ $address->phone }}"
                                        data-address-line-1="{{ $address->address_line_1 }}"
                                        data-address-line-2="{{ $address->address_line_2 }}"
                                        data-city="{{ $address->city }}"
                                        data-pincode="{{ $address->pincode }}"
                                        data-landmark="{{ $address->landmark }}"
                                        data-location-url="{{ $address->delivery_location_url }}"
                                        @checked($address->is_default)>
                                    <span>
                                        <strong>{{ $address->label }} @if ($address->is_default) · Default @endif</strong>
                                        {{ $address->address_line_1 }}, {{ $address->city }} - {{ $address->pincode }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <div class="account-form">
                        <label for="address_line_1">Address Line 1</label>
                        <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required autocomplete="address-line1">

                        <label for="address_line_2">Address Line 2</label>
                        <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" autocomplete="address-line2">

                        <label for="city">City</label>
                        <input id="city" name="city" value="{{ old('city') }}" required autocomplete="address-level2">

                        <label for="pincode">Pincode</label>
                        <input id="pincode" name="pincode" value="{{ old('pincode') }}" required autocomplete="postal-code">

                        <label for="landmark">Landmark</label>
                        <input id="landmark" name="landmark" value="{{ old('landmark') }}">
                    </div>
                </section>

                <section class="checkout-section checkout-location-box">
                    <div>
                        <p class="eyebrow">Delivery Pin</p>
                        <h2>Share location for easier delivery</h2>
                        <p>Optional, but helpful for apartments, offices and hard-to-find addresses.</p>
                    </div>
                    <div class="checkout-location-box__actions">
                        <label for="delivery_location_url">Google Maps Link</label>
                        <input id="delivery_location_url" name="delivery_location_url" type="url" value="{{ old('delivery_location_url') }}" placeholder="https://maps.google.com/..." data-location-url>
                        <button class="button button--secondary" type="button" data-use-current-location>
                            <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i>
                            Use Current Location
                        </button>
                    </div>
                </section>

                <div class="checkout-flow">
                    <span>Details</span>
                    <span>Products</span>
                    <span><img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay"></span>
                    <span>Confirmation</span>
                </div>

                <label class="policy-agreement">
                    <input name="terms" type="checkbox" value="1" required>
                    <span>I agree to the Terms & Conditions, Privacy Policy, Shipping Policy and <a href="{{ route('policies.return-refund') }}">Return & Refund Policy</a>.</span>
                </label>

                <button class="button button--primary" type="submit">Continue To Payment</button>
            </form>

            <aside class="checkout-products-card" data-checkout-summary>
                <div class="checkout-products-card__header">
                    <div>
                        <span>Order Summary</span>
                        <strong data-checkout-count>{{ count($cart) }} item{{ count($cart) === 1 ? '' : 's' }}</strong>
                    </div>
                    <a href="{{ route('cart.empty') }}">Open Cart</a>
                </div>

                <div class="checkout-product-list">
                    @foreach ($cart as $key => $item)
                        <article class="checkout-product-card" data-checkout-item data-cart-key="{{ $key }}">
                            <img src="{{ $item['image'] }}" alt="{{ $item['product'] }}" loading="lazy">
                            <div>
                                <h3>{{ $item['product'] }}</h3>
                                <p>{{ $item['colour'] }} / {{ $item['size'] }}</p>
                                <strong data-line-total>&#8377;{{ number_format($item['price'] * $item['quantity']) }}</strong>
                                <div class="checkout-product-card__controls">
                                    <label>
                                        Qty
                                        <input type="number" min="1" max="10" value="{{ $item['quantity'] }}" data-cart-quantity>
                                    </label>
                                    <button class="button button--ghost" type="button" data-cart-update>Update</button>
                                    <button class="button button--ghost" type="button" data-cart-remove>Remove</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="checkout-breakdown">
                    <span>Subtotal: <strong data-checkout-subtotal>&#8377;{{ number_format($summary['subtotal']) }}</strong></span>
                    <span>Shipping: <strong data-checkout-shipping>{{ $summary['shipping_label'] }}</strong></span>
                    <span class="checkout-shipping-note" data-checkout-shipping-note>{{ $summary['shipping_policy_text'] }}</span>
                    <span>GST: <strong data-checkout-tax>&#8377;{{ number_format($summary['tax']) }}</strong> included</span>
                    <span>CGST: <strong>&#8377;{{ number_format($summary['cgst']) }}</strong> · SGST: <strong>&#8377;{{ number_format($summary['sgst']) }}</strong></span>
                    <span class="checkout-payment-logos">
                        <img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay">
                        <img src="{{ asset('assets/payments/upi.svg') }}" alt="UPI">
                        <img src="{{ asset('assets/payments/visa.svg') }}" alt="Visa">
                        <img src="{{ asset('assets/payments/mastercard.svg') }}" alt="Mastercard">
                    </span>
                </div>
                <div class="price-row price-row--large">
                    <strong data-checkout-total>&#8377;{{ number_format($summary['total']) }}</strong>
                    <span>Total now</span>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.customer>
