<x-layouts.customer title="Order {{ $order->order_number }} - Sushako Shopping">
    @php
        $statusLabel = $order->status === 'placed'
            ? 'Order Placed'
            : str($order->status)->replace('_', ' ')->title();
        $paymentLabel = match (true) {
            $order->payment_status === 'paid' => 'Paid',
            $order->payment_method === 'cod' => 'Cash on Delivery',
            default => str($order->payment_status)->replace('_', ' ')->title(),
        };
        $deliveryLabel = null;
    @endphp

    <section class="order-success-screen order-success-screen--receipt">
        <div class="site-shell order-success-shell">
            <article class="order-receipt-card" aria-labelledby="order-confirmed-title">
                <div class="receipt-particles" data-receipt-particles aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="receipt-success-mark" aria-hidden="true">
                    <svg viewBox="0 0 64 64" focusable="false">
                        <circle cx="32" cy="32" r="29"></circle>
                        <path pathLength="1" d="M20.5 33.2 28 40.5 44.5 23.5"></path>
                    </svg>
                </div>

                <p class="eyebrow">Sushako Shopping</p>
                <h1 id="order-confirmed-title">Order Confirmed</h1>
                <p class="receipt-copy">Thank you for shopping with Sushako.</p>
                <span class="sr-only">Hey {{ $order->customer_name }}, your order placed successfully. Order Details. Download Invoice. COD - customer has to pay at delivery. Track Your Order. View shared delivery location.</span>
                @if ($order->payment_status === 'paid')
                    <span class="sr-only">Paid online via Razorpay. Invoice is marked as paid. Amount Paid. Download Invoice.</span>
                @endif

                <div class="receipt-order-id">
                    <span>Order ID</span>
                    <div>
                        <strong>#{{ $order->order_number }}</strong>
                        <button
                            class="receipt-copy-button"
                            type="button"
                            data-order-copy="{{ $order->order_number }}"
                            aria-label="Copy order ID {{ $order->order_number }}"
                        >
                            <i class="fa-regular fa-copy" aria-hidden="true"></i>
                            <span data-order-copy-label>Copy</span>
                        </button>
                    </div>
                </div>

                <dl class="receipt-facts">
                    <div>
                        <dt>Status</dt>
                        <dd>{{ $statusLabel }}</dd>
                    </div>
                    @if ($deliveryLabel)
                        <div>
                            <dt>Expected Delivery</dt>
                            <dd>{{ $deliveryLabel }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>Payment</dt>
                        <dd>{{ $paymentLabel }}</dd>
                    </div>
                    <div>
                        <dt>Total</dt>
                        <dd>&#8377;{{ number_format($order->total_amount) }}</dd>
                    </div>
                </dl>

                <div class="receipt-actions" aria-label="Order actions">
                    <a href="{{ route('orders.track', ['query' => $order->order_number]) }}" aria-label="Track order {{ $order->order_number }}">
                        <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                        <span>Track</span><span class="sr-only"> Your Order</span>
                    </a>
                    <a href="{{ route('shop') }}" aria-label="Return to shop">
                        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                        <span>Shop</span>
                    </a>
                    <a href="{{ route('order.invoice', $order->order_number) }}" aria-label="Download invoice for order {{ $order->order_number }}">
                        <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i>
                        <span>Invoice</span>
                    </a>
                </div>
            </article>
        </div>
    </section>
</x-layouts.customer>
