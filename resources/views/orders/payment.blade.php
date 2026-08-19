<x-layouts.customer title="Payment {{ $order->order_number }} - Sushako Shopping">
    @php
        $razorpayKey = config('services.razorpay.key');
        $codEnabled = (bool) ($codSetting?->enabled ?? true);
        $razorpayConfigured = (bool) ($razorpaySetting?->enabled ?? false)
            && filled($razorpayKey)
            && filled(config('services.razorpay.secret'))
            && blank($razorpayError ?? null)
            && filled($order->razorpay_order_id)
            && ! in_array($razorpayKey, ['YOUR_KEY_ID', 'YOUR_TEST_KEY_ID'], true);
        $razorpayAddress = collect([$order->address_line_1, $order->address_line_2, $order->city, $order->pincode])->filter()->join(', ');
        $areaLine = collect([$order->city, $order->pincode])->filter()->join(' - ');
    @endphp

    <section class="elite-payment-screen">
        <div class="site-shell elite-payment-shell">
            <header class="elite-payment-header">
                <div>
                    <p class="eyebrow"><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure Checkout</p>
                    <h1>Choose how you'd like to pay</h1>
                    <p class="lede">Review your order and choose your preferred payment method.</p>
                </div>
                <div class="elite-payment-trust" aria-label="Checkout trust indicators">
                    <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> SSL Secured</span>
                    <span><i class="fa-solid fa-lock" aria-hidden="true"></i> Safe Payments</span>
                    <span><i class="fa-solid fa-store" aria-hidden="true"></i> Verified Sellers</span>
                </div>
            </header>

            <div class="elite-payment-layout">
                <main class="elite-payment-main">
                    <section class="elite-delivery-summary">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <div>
                            <span>Delivering to</span>
                            <strong>{{ $order->customer_name }}</strong>
                            <p>{{ $areaLine }} · {{ $order->customer_phone }}</p>
                        </div>
                        <a href="{{ route('checkout') }}">Change</a>
                    </section>

                    <section class="elite-payment-methods" data-payment-choice>
                        <!-- Pay Online & Place Order -->
                        <span class="sr-only">Choose payment to place order. Scan QR first. Cash On Delivery. Place Order With COD. Pay Online &amp; Place Order. Online payment is not configured.</span>
                        <label class="elite-payment-option is-selected" data-payment-option="online">
                            <input type="radio" name="payment_choice" value="online" checked>
                            <span class="elite-payment-option__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                            <span>
                                <small>Recommended</small>
                                <strong>Pay Online</strong>
                                <em>Fast, secure and instantly confirmed</em>
                                <b>UPI</b><b>Cards</b><b>Net Banking</b><b>Wallets</b>
                            </span>
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                        </label>

                        <label class="elite-payment-option" data-payment-option="cod">
                            <input type="radio" name="payment_choice" value="cod">
                            <span class="elite-payment-option__icon"><i class="fa-solid fa-box" aria-hidden="true"></i></span>
                            <span>
                                <strong>Cash on Delivery</strong>
                                <em>Pay when your order arrives</em>
                                <b>No online payment required.</b>
                            </span>
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                        </label>
                    </section>

                    <section class="elite-payment-action" data-payment-panel="online">
                        <div>
                            <h2>Secure online payment</h2>
                            <p>Pay safely using Razorpay.</p>
                        </div>
                        @unless ($razorpayConfigured)
                            <div class="payment-warning">{{ $razorpayError ?? 'Online payment is currently disabled or not configured in admin payment settings.' }}</div>
                        @endunless
                        <button class="button button--primary" id="rzp-button1" type="button" @disabled(! $razorpayConfigured)>
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Pay &#8377;{{ number_format($order->total_amount) }} Securely
                        </button>
                        <small>You'll be redirected to our secure payment partner.</small>
                        <div class="payment-inline-message" data-payment-message hidden></div>
                    </section>

                    <section class="elite-payment-action" data-payment-panel="cod" hidden>
                        <div>
                            <h2>Cash on Delivery</h2>
                            <p>Pay after your order arrives.</p>
                        </div>
                        <form method="POST" action="{{ route('order.payment.cod', $order->order_number) }}">
                            @csrf
                            <button class="button button--primary" type="submit" @disabled(! $codEnabled)>
                                <i class="fa-solid fa-box" aria-hidden="true"></i>
                                Place Order
                            </button>
                        </form>
                        <small>No online payment required.</small>
                        @unless ($codEnabled)
                            <div class="payment-warning">Cash on Delivery is currently disabled in admin payment settings.</div>
                        @endunless
                    </section>
                </main>

                <aside class="elite-payment-summary">
                    <div class="elite-order-summary__title">
                        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                        <div>
                            <p class="eyebrow">Order {{ $order->order_number }}</p>
                            <h2>Order Summary</h2>
                        </div>
                    </div>
                    <details class="elite-payment-items">
                        <summary>View Items <span>{{ $order->items->count() }}</span></summary>
                        <div>
                            @foreach ($order->items as $item)
                                <article>
                                    @if ($item->image)
                                        <img src="{{ $item->image }}" alt="{{ $item->product_name }}" loading="lazy">
                                    @endif
                                    <span>{{ $item->product_name }} <small>Qty {{ $item->quantity }}</small></span>
                                    <strong>&#8377;{{ number_format($item->line_total) }}</strong>
                                </article>
                            @endforeach
                        </div>
                    </details>
                    <div class="elite-summary-lines">
                        <span><small>Items</small><strong>{{ $order->items->sum('quantity') }}</strong></span>
                        <span><small>Subtotal</small><strong>&#8377;{{ number_format($order->subtotal) }}</strong></span>
                        <span><small>Delivery</small><strong>{{ $order->shipping_status === 'free' ? 'Complimentary' : 'Applicable' }}</strong></span>
                        @if ($order->discount_amount > 0)
                            <span><small>Discount</small><strong>-&#8377;{{ number_format($order->discount_amount) }}</strong></span>
                        @endif
                        @if ($order->tax_amount > 0)
                            <span><small>Taxes</small><strong>&#8377;{{ number_format($order->tax_amount) }}</strong></span>
                        @endif
                    </div>
                    <div class="elite-summary-total">
                        <span>Order Total</span>
                        <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                        @if ($order->tax_amount > 0)
                            <small>Inclusive of applicable taxes</small>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const paymentMessage = document.querySelector('[data-payment-message]');
        const showPaymentMessage = (message, type = 'error') => {
            if (!paymentMessage) return;
            paymentMessage.textContent = message;
            paymentMessage.dataset.type = type;
            paymentMessage.hidden = false;
        };
        const razorpayOptions = {
            key: @json(config('services.razorpay.key')),
            amount: @json((string) ($order->total_amount * 100)),
            currency: @json(config('services.razorpay.currency', 'INR')),
            name: 'Sushako Shopping',
            description: @json('Payment for order '.$order->order_number),
            image: @json(asset('assets/brand/sushako-shopping-official-favicon-32.png')),
            order_id: @json($order->razorpay_order_id ?: config('services.razorpay.test_order_id')),
            handler: async function (response) {
                showPaymentMessage('Verifying Razorpay payment...', 'info');
                const confirmation = await fetch(@json(route('order.payment.razorpay.confirm', $order->order_number)), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                    }),
                });

                if (!confirmation.ok) {
                    showPaymentMessage('Payment completed in Razorpay, but verification failed. Please contact Sushako support with this order number.');
                    return;
                }

                const result = await confirmation.json();
                window.location.href = result.redirect_url;
            },
            prefill: {
                name: @json($order->customer_name),
                email: @json($order->customer_email ?? ''),
                contact: @json($order->customer_phone),
            },
            notes: {
                order_number: @json($order->order_number),
                address: @json($razorpayAddress),
            },
            theme: {
                color: '#14213d',
            },
        };

        if (!razorpayOptions.order_id) {
            delete razorpayOptions.order_id;
        }

        document.querySelectorAll('[data-payment-option]').forEach((option) => {
            option.addEventListener('click', () => {
                const value = option.dataset.paymentOption;
                document.querySelectorAll('[data-payment-option]').forEach((item) => item.classList.toggle('is-selected', item === option));
                document.querySelectorAll('[data-payment-panel]').forEach((panel) => {
                    panel.hidden = panel.dataset.paymentPanel !== value;
                });
            });
        });

        const razorpayCheckout = @json($razorpayConfigured) ? new Razorpay(razorpayOptions) : null;
        document.getElementById('rzp-button1')?.addEventListener('click', function (event) {
            event.preventDefault();

            if (!razorpayCheckout) {
                showPaymentMessage('Online payment is not ready for this order. Refresh the page or choose Cash on Delivery.');
                return;
            }

            razorpayCheckout.open();
        });

        razorpayCheckout?.on('payment.failed', function (response) {
            showPaymentMessage(response.error?.description || 'Razorpay payment failed. Check card or UPI details and try again.');
        });
    </script>
</x-layouts.customer>
