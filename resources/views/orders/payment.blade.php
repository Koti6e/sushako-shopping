<x-layouts.customer title="Payment {{ $order->order_number }} - Sushako Shopping">
    @php
        $razorpayKey = config('services.razorpay.key');
        $codEnabled = (bool) ($codSetting?->enabled ?? true);
        $razorpayConfigured = (bool) ($razorpaySetting?->enabled ?? false)
            && filled($razorpayKey)
            && ! in_array($razorpayKey, ['YOUR_KEY_ID', 'YOUR_TEST_KEY_ID'], true);
        $razorpayAddress = collect([$order->address_line_1, $order->address_line_2, $order->city, $order->pincode])->filter()->join(', ');
    @endphp

    <section class="payment-selection-screen">
        <div class="site-shell payment-selection-shell">
            <div class="payment-selection-hero">
                <div>
                    <p class="eyebrow">Sushako Secure Payment</p>
                    <h1>Choose payment to place order</h1>
                    <p class="lede">Your order is waiting at the final velvet step. Complete the payment with Razorpay or confirm Cash on Delivery, and Sushako will move it into fulfillment.</p>
                    <div class="payment-red-carpet-strip">
                        <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Bank-grade checkout</span>
                        <span><i class="fa-solid fa-receipt" aria-hidden="true"></i> Invoice after order</span>
                        <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Support ready</span>
                    </div>
                </div>
                <div class="payment-total-row payment-total-row--hero">
                    <span>Red Carpet Total</span>
                    <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                    <small>Order {{ $order->order_number }}</small>
                </div>
            </div>

            <div class="payment-selection-grid">
                <article class="payment-choice-card payment-choice-card--online">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Pay Online</p>
                            <h2>Pay online and place order</h2>
                        </div>
                        <img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay">
                    </div>
                    <p class="payment-choice-lede">The fastest route into Sushako processing. Razorpay opens a secure payment window for UPI, cards and netbanking.</p>

                    <div class="payment-qr-priority">
                        <div class="payment-qr-box" aria-label="Razorpay QR priority visual">
                            <span></span><span></span><span></span>
                        </div>
                        <div>
                            <h3>Scan QR first</h3>
                            <p>Use UPI apps for the fastest online payment. After Razorpay succeeds, Sushako places the order and shows confirmation.</p>
                        </div>
                    </div>

                    <div class="payment-method-grid">
                        <span><img src="{{ asset('assets/payments/upi.svg') }}" alt="UPI"> QR / UPI</span>
                        <span><img src="{{ asset('assets/payments/visa.svg') }}" alt="Cards"> Cards</span>
                        <span><i class="fa-solid fa-building-columns" aria-hidden="true"></i> Netbanking</span>
                    </div>

                    <div class="payment-luxury-assurance">
                        <span><i class="fa-solid fa-lock" aria-hidden="true"></i> Encrypted Razorpay session</span>
                        <span><i class="fa-solid fa-bolt" aria-hidden="true"></i> Instant order confirmation</span>
                        <span><i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Paid invoice generated</span>
                    </div>

                    @unless ($razorpayConfigured)
                        <div class="payment-warning">Online payment is currently disabled or not configured in admin payment settings.</div>
                    @endunless

                    <button class="button button--primary" id="rzp-button1" type="button" @disabled(! $razorpayConfigured)>
                        <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                        Pay Online & Place Order
                    </button>
                    <div class="payment-inline-message" data-payment-message hidden></div>
                </article>

                <aside class="payment-choice-card payment-choice-card--cod">
                    <div>
                        <p class="eyebrow">Cash On Delivery</p>
                        <h2>Place order with COD</h2>
                        <p class="payment-choice-lede">Prefer to pay when your package arrives? Confirm COD and the order enters the queue with amount due at delivery.</p>
                    </div>
                    <div class="payment-cod-summary">
                        <span>Pay during delivery</span>
                        <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                        <small>Keep the amount ready when the Sushako delivery update arrives.</small>
                    </div>
                    <form method="POST" action="{{ route('order.payment.cod', $order->order_number) }}">
                        @csrf
                        <button class="button button--secondary" type="submit" @disabled(! $codEnabled)>
                            <i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i>
                            Place Order With COD
                        </button>
                    </form>
                    @unless ($codEnabled)
                        <div class="payment-warning">Cash on Delivery is currently disabled in admin payment settings.</div>
                    @endunless

                    <div class="payment-order-mini">
                        <strong>{{ $order->customer_name }}</strong>
                        <span>{{ $order->customer_phone }}</span>
                        <span>{{ $razorpayAddress }}</span>
                    </div>
                </aside>
            </div>

            <div class="payment-concierge-row">
                <span><i class="fa-solid fa-gem" aria-hidden="true"></i> Premium Sushako checkout</span>
                <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Dispatch starts after order placement</span>
                <span><i class="fa-solid fa-route" aria-hidden="true"></i> Tracking-ready order ID</span>
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
            image: @json(asset('images/brand/favicon-32x32.png')),
            order_id: @json($order->razorpay_order_id ?: config('services.razorpay.test_order_id')),
            handler: async function (response) {
                showPaymentMessage('Confirming payment with localhost order...', 'info');
                const confirmation = await fetch(@json(route('order.payment.razorpay-test', $order->order_number)), {
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
                    showPaymentMessage('Payment completed in Razorpay, but localhost confirmation failed. Please check Laravel logs.');
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

        const razorpayCheckout = @json($razorpayConfigured) ? new Razorpay(razorpayOptions) : null;
        document.getElementById('rzp-button1')?.addEventListener('click', function (event) {
            event.preventDefault();

            if (!razorpayCheckout) {
                showPaymentMessage('Online payment is not configured on this device. Update RAZORPAY_KEY_ID in .env, then run php artisan config:clear.');
                return;
            }

            razorpayCheckout.open();
        });

        razorpayCheckout?.on('payment.failed', function (response) {
            showPaymentMessage(response.error?.description || 'Razorpay payment failed. Check card or UPI details and try again.');
        });
    </script>
</x-layouts.customer>
