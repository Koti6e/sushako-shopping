<x-layouts.customer title="Order {{ $order->order_number }} - Sushako Shopping">
    @php
        $isPaidOnline = $order->payment_method === 'razorpay' && $order->payment_status === 'paid';
        $isCod = $order->payment_method === 'cod';
        $paymentTitle = $isPaidOnline ? 'Paid Online' : ($isCod ? 'Cash on Delivery' : 'Payment Pending');
        $paymentSummary = $isPaidOnline ? 'Paid online via Razorpay' : ($isCod ? 'COD - customer has to pay at delivery' : 'Payment not completed');
        $amountLabel = $isPaidOnline ? 'Amount Paid' : 'Amount Due';
        $shippingLabel = $order->shipping_status === 'delivery_charges_applicable'
            ? 'Delivery charges applicable'
            : ($order->shipping_amount ? '₹'.number_format($order->shipping_amount) : 'Free');
    @endphp

    <section class="order-success-screen">
        <div class="site-shell order-success-shell">
            <div class="order-success-hero">
                <div class="order-success-hero__mark">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                </div>
                <div>
                    <p class="eyebrow">Order Confirmed</p>
                    <h1>Hey {{ $order->customer_name }}, your order placed successfully</h1>
                    <div class="order-id-receipt">
                        <span>Order ID</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>
                    <p class="lede">Thank you for shopping with Sushako. We have received your order and will keep this ID ready for tracking and support.</p>
                </div>
                <div class="order-success-hero__actions">
                    <a class="button button--secondary" href="{{ route('orders.track') }}"><i class="fa-solid fa-route" aria-hidden="true"></i> Track Your Order</a>
                    <a class="button button--secondary" href="{{ route('order.invoice', $order->order_number) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Download Invoice</a>
                    <a class="button button--secondary" href="{{ route('shop') }}"><i class="fa-solid fa-store" aria-hidden="true"></i> Continue Shopping</a>
                </div>
            </div>

            <div class="order-success-grid">
                <article class="order-success-card order-products-card">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Product Details</p>
                            <h2>Items in this order</h2>
                        </div>
                        <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                    </div>
                    <div class="order-product-list">
                        @foreach ($order->items as $item)
                            <article class="order-product-card">
                                @if ($item->image)
                                    <img src="{{ $item->image }}" alt="{{ $item->product_name }}" loading="lazy">
                                @endif
                                <div>
                                    <h3>{{ $item->product_name }}</h3>
                                    <p>{{ $item->colour }} / {{ $item->size }} · Qty {{ $item->quantity }}</p>
                                </div>
                                <strong>&#8377;{{ number_format($item->line_total) }}</strong>
                            </article>
                        @endforeach
                    </div>
                    <div class="price-row price-row--large">
                        <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                        <span>Total</span>
                    </div>
                </article>

                <aside class="order-success-card order-summary-card">
                    <p class="eyebrow">Order Details</p>
                    <h2>Summary</h2>
                    <div class="order-facts-grid">
                        <span><strong>Order</strong>{{ $order->order_number }}</span>
                        <span><strong>Status</strong>{{ ucfirst($order->status) }}</span>
                        <span><strong>Payment</strong>{{ $paymentSummary }}</span>
                        <span><strong>Shipping</strong>{{ $shippingLabel }}</span>
                        <span><strong>GST Included</strong>&#8377;{{ number_format($order->tax_amount) }}</span>
                        <span><strong>Placed</strong>{{ $order->placed_at?->format('d M Y, h:i A') }}</span>
                    </div>

                    <div class="payment-total-row">
                        <span>{{ $amountLabel }}</span>
                        <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                    </div>

                    <div class="summary-payment-note">
                        <div>
                            <span class="payment-state payment-state--{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span>
                            <strong>{{ $paymentTitle }}</strong>
                        </div>
                        @if ($isPaidOnline)
                            <p>Paid online via Razorpay. Invoice is marked as paid.</p>
                        @elseif ($isCod)
                            <p>Cash on Delivery selected. Customer has to pay &#8377;{{ number_format($order->total_amount) }} at delivery.</p>
                        @else
                            <a class="button button--primary" href="{{ route('order.payment', $order->order_number) }}">Complete Payment</a>
                        @endif
                    </div>
                </aside>

                <article class="order-success-card order-delivery-card">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Delivery Details</p>
                            <h2>Customer and address</h2>
                        </div>
                    </div>
                    <div class="order-detail-list">
                        <span><i class="fa-solid fa-user" aria-hidden="true"></i>{{ $order->customer_name }}</span>
                        <span><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $order->customer_phone }}</span>
                        @if ($order->customer_email)
                            <span><i class="fa-solid fa-envelope" aria-hidden="true"></i>{{ $order->customer_email }}</span>
                        @endif
                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $order->address_line_1 }}{{ $order->address_line_2 ? ', '.$order->address_line_2 : '' }}, {{ $order->city }} - {{ $order->pincode }}</span>
                        @if ($order->landmark)
                            <span><i class="fa-solid fa-map-pin" aria-hidden="true"></i>Landmark: {{ $order->landmark }}</span>
                        @endif
                    </div>
                    @if ($order->delivery_location_url)
                        <a class="button button--secondary" href="{{ $order->delivery_location_url }}" target="_blank" rel="noopener noreferrer">View shared delivery location</a>
                    @endif
                </article>
            </div>
        </div>
    </section>

</x-layouts.customer>
