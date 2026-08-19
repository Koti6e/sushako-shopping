<x-layouts.customer title="Checkout - Sushako Shopping">
    @php
        $checkoutPhone = preg_replace('/\D+/', '', (string) $user?->phone);
        $checkoutPhone = str_starts_with($checkoutPhone, '91') && strlen($checkoutPhone) === 12 ? substr($checkoutPhone, 2) : $checkoutPhone;
    @endphp
    <section class="luxury-checkout">
        <div class="site-shell luxury-checkout__grid">
            <form class="checkout-panel luxury-checkout__form" method="POST" action="{{ route('checkout.place') }}">
                @csrf
                <x-brand.logo loading="eager" />
                <p class="eyebrow">Secure Checkout</p>
                <h1>Guest checkout in a few steps</h1>
                <p class="lede">No login needed. Share your delivery details, choose payment, and receive your order confirmation.</p>

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
                        <span class="checkout-phone-field"><b>+91</b><input id="customer_phone" name="customer_phone" type="tel" inputmode="numeric" pattern="[6-9]\d{9}" maxlength="10" value="{{ old('customer_phone', $checkoutPhone) }}" required autocomplete="tel" data-digits-only></span>

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
                        <label for="address_line_1">Address</label>
                        <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required autocomplete="address-line1">

                        <label for="address_line_2">Flat / Door Number <span>(Optional)</span></label>
                        <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" autocomplete="address-line2">

                        <label for="city">City / Area <span>(Optional)</span></label>
                        <input id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2">

                        <label for="pincode">Pincode</label>
                        <input id="pincode" name="pincode" inputmode="numeric" pattern="\d{6}" maxlength="6" value="{{ old('pincode') }}" required autocomplete="postal-code" data-digits-only>

                        <label for="landmark">Landmark <span>(Optional)</span></label>
                        <input id="landmark" name="landmark" value="{{ old('landmark') }}">
                    </div>
                </section>

                <section class="checkout-section checkout-location-box">
                    <div>
                        <p class="eyebrow">Delivery Location</p>
                        <h2>Google Maps Location <span>(Optional)</span></h2>
                        <p>Share your location link to help the seller find you faster.</p>
                    </div>
                    <div class="checkout-location-box__actions">
                        <label for="delivery_location_url">Google Maps Location <span>(Optional)</span></label>
                        <input id="delivery_location_url" name="delivery_location_url" type="url" value="{{ old('delivery_location_url') }}" placeholder="https://maps.google.com/..." data-location-url>
                        <button class="button button--secondary" type="button" data-use-current-location>
                            <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i>
                            Use Current Location
                        </button>
                    </div>
                </section>

                @php
                    $paymentPreference = old('payment_preference', 'online');
                @endphp
                <section class="checkout-section checkout-payment-method-section" data-checkout-payment-method>
                    <div>
                        <p class="eyebrow">Payment Method</p>
                        <h2>Review your order and choose the payment option that works best for you.</h2>
                    </div>
                    <div class="checkout-payment-choice-grid" role="radiogroup" aria-label="Payment method">
                        <label class="checkout-payment-choice @if($paymentPreference === 'online') is-selected @endif" data-checkout-payment-card>
                            <input type="radio" name="payment_preference" value="online" @checked($paymentPreference === 'online')>
                            <span class="checkout-payment-choice__content">
                                <small>Recommended</small>
                                <span class="checkout-payment-choice__title"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Pay Online</span>
                                <em>Complete your payment securely and receive instant order confirmation.</em>
                                <span class="checkout-payment-choice__features">
                                    <b><i class="fa-solid fa-check" aria-hidden="true"></i> Instant order confirmation</b>
                                    <b><i class="fa-solid fa-check" aria-hidden="true"></i> Encrypted payment processing</b>
                                    <b><i class="fa-solid fa-check" aria-hidden="true"></i> UPI, Cards, Net Banking and Wallets</b>
                                </span>
                                <span class="checkout-payment-choice__logos" aria-label="Supported online payment methods">
                                    <img src="{{ asset('assets/payments/upi.svg') }}" alt="UPI">
                                    <img src="{{ asset('assets/payments/visa.svg') }}" alt="Visa">
                                    <img src="{{ asset('assets/payments/mastercard.svg') }}" alt="Mastercard">
                                    <img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay">
                                </span>
                                <span class="checkout-payment-choice__selected-note" data-selected-note>Secure payment powered by Razorpay</span>
                            </span>
                            <span class="checkout-payment-choice__visual">
                                <img src="{{ asset('images/checkout/pay-online.svg') }}" alt="Secure smartphone payment with parcel confirmation" width="640" height="480" loading="lazy">
                            </span>
                            <i class="fa-solid fa-circle-check checkout-payment-choice__check" aria-hidden="true"></i>
                        </label>

                        <label class="checkout-payment-choice @if($paymentPreference === 'cod') is-selected @endif" data-checkout-payment-card>
                            <input type="radio" name="payment_preference" value="cod" @checked($paymentPreference === 'cod')>
                            <span class="checkout-payment-choice__content">
                                <span class="checkout-payment-choice__title"><i class="fa-solid fa-box" aria-hidden="true"></i> Cash on Delivery</span>
                                <em>Receive your order first and pay at your doorstep.</em>
                                <span class="checkout-payment-choice__features">
                                    <b><i class="fa-solid fa-check" aria-hidden="true"></i> Pay after receiving your order</b>
                                    <b><i class="fa-solid fa-check" aria-hidden="true"></i> No online payment required</b>
                                    <b><i class="fa-solid fa-check" aria-hidden="true"></i> Simple doorstep payment</b>
                                </span>
                                <span class="checkout-payment-choice__selected-note" data-selected-note>Pay directly when your order is delivered.</span>
                            </span>
                            <span class="checkout-payment-choice__visual">
                                <img src="{{ asset('images/checkout/cash-on-delivery.svg') }}" alt="Doorstep parcel delivery for cash on delivery" width="640" height="480" loading="lazy">
                            </span>
                            <i class="fa-solid fa-circle-check checkout-payment-choice__check" aria-hidden="true"></i>
                        </label>
                    </div>

                    <div class="checkout-payment-support" aria-label="Payment reassurance">
                        <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Secure checkout</span>
                        <span><i class="fa-solid fa-lock" aria-hidden="true"></i> Encrypted payments</span>
                        <span><i class="fa-solid fa-box-open" aria-hidden="true"></i> Order support</span>
                    </div>
                    <p class="checkout-payment-reassurance" data-payment-reassurance aria-live="polite">
                        {{ $paymentPreference === 'cod'
                            ? 'Pay directly when your order is delivered.'
                            : 'Your payment details are processed securely. Sushako does not store your card or UPI credentials.' }}
                    </p>
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

                <button
                    class="button button--primary checkout-payment-submit"
                    type="submit"
                    data-checkout-payment-submit
                    data-online-label="Pay &#8377;{{ number_format($summary['total']) }} Securely"
                    data-cod-label="Place Order · &#8377;{{ number_format($summary['total']) }}"
                >
                    <i class="fa-solid {{ $paymentPreference === 'cod' ? 'fa-box' : 'fa-lock' }}" aria-hidden="true" data-checkout-payment-submit-icon></i>
                    <span data-checkout-payment-submit-label>{{ $paymentPreference === 'cod' ? 'Place Order · ₹'.number_format($summary['total']) : 'Pay ₹'.number_format($summary['total']).' Securely' }}</span>
                </button>
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
