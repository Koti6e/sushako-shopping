@php
    $authUser = auth()->user();
    $isCustomer = $authUser?->role === \App\Models\User::ROLE_CUSTOMER;
    $timelineLabels = [
        'placed' => 'Order Placed',
        'confirmed' => 'Confirmed',
        'packing' => 'Packed',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
    ];
    $exceptionLabels = [
        'cancelled' => 'Cancelled',
        'rejected' => 'Rejected',
        'refund_initiated' => 'Refund Initiated',
        'refunded' => 'Refunded',
    ];
@endphp

<x-layouts.customer title="Track Order - Sushako Shopping">
    <section class="content-screen track-order-page">
        <div class="site-shell track-order-layout">
            <form class="track-order-panel" method="POST" action="{{ route('orders.track.lookup') }}">
                @csrf
                <x-brand.logo loading="eager" />
                <p class="eyebrow">Track Order</p>
                <h1>{{ $isCustomer ? 'Your Sushako orders' : 'Track your Sushako order' }}</h1>
                <p class="lede">
                    {{ $isCustomer ? 'Signed-in customers can view only their own orders.' : 'Enter your Order ID and matching mobile number or email to view tracking safely.' }}
                </p>

                @if ($errors->any())
                    <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
                @endif

                <label for="query">Order ID</label>
                <input id="query" name="query" value="{{ $query['query'] ?? old('query') }}" placeholder="SS{{ now()->format('Ym') }}00001" required>

                @guest
                    <label for="contact">Mobile Number or Email</label>
                    <input id="contact" name="contact" value="{{ $query['contact'] ?? old('contact') }}" placeholder="Mobile number or email used on the order" required>
                    <small>We match this with the order contact before showing any details.</small>
                @else
                    <input type="hidden" name="contact" value="">
                @endguest

                <button class="button button--primary" type="submit"><i class="fa-solid fa-route" aria-hidden="true"></i> Track Order</button>

                <div class="track-trust-row" aria-label="Tracking trust symbols">
                    <span><i class="fa-solid fa-barcode" aria-hidden="true"></i> Order ID</span>
                    <span><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Protected lookup</span>
                    <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Delivery status</span>
                </div>
            </form>

            <section class="track-order-results" aria-label="Tracking results">
                @if ($query && $orders->isEmpty())
                    <article class="track-order-empty">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <strong>No matching order found</strong>
                        <p>Check the Order ID and contact details. For security, Sushako only shows orders that match your account or provided contact.</p>
                        <a class="button button--ghost" href="https://wa.me/{{ config('services.whatsapp.support_number') }}?text={{ rawurlencode('Hello Sushako Shopping, I need help tracking my order.') }}" target="_blank" rel="noopener noreferrer">Contact Support</a>
                    </article>
                @elseif ($orders->isEmpty())
                    <article class="track-order-empty">
                        <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                        <strong>Ready when you are</strong>
                        <p>{{ $isCustomer ? 'Your completed orders will appear here after checkout.' : 'Use the form to securely find your order.' }}</p>
                    </article>
                @else
                    @foreach ($orders as $order)
                        @php
                            $status = $order->status;
                            $isException = array_key_exists($status, $exceptionLabels);
                            $sellerNames = $order->items->pluck('vendor.store_display_name')->filter()->unique()->values();
                            $sellerCities = $order->items->pluck('vendor.city')->filter()->unique()->values();
                            $sessionOrder = session('checkout_orders.'.$order->order_number);
                            $hasSessionOrderAccess = is_array($sessionOrder)
                                && ($sessionOrder['phone'] ?? null) === $order->customer_phone
                                && (($sessionOrder['email'] ?? null) === $order->customer_email || blank($order->customer_email));
                            $canManageOrder = ($isCustomer && $order->user_id === $authUser?->id) || $hasSessionOrderAccess;
                            $safeInvoice = $canManageOrder && $order->status !== 'payment_pending';
                            $canChooseOverdueAction = $canManageOrder
                                && $order->status === 'placed'
                                && ($order->seller_order_status ?: 'new') === 'new'
                                && $order->seller_acceptance_due_at
                                && now()->greaterThan($order->seller_acceptance_due_at);
                            $canCancel = $canManageOrder && ! in_array($order->seller_order_status, ['shipped', 'delivered', 'cancelled'], true);
                        @endphp
                        <article class="track-order-card">
                            <header>
                                <div>
                                    <p class="eyebrow">{{ $isException ? 'Order Update' : 'Order Tracking' }}</p>
                                    <h2>{{ $order->order_number }}</h2>
                                    <span>{{ str($status)->replace('_', ' ')->title() }} · {{ $order->placed_at?->format('d M Y, h:i A') ?? $order->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                            </header>

                            <ol class="track-order-timeline">
                                @if ($isException)
                                    <li class="is-complete"><span></span>Order Placed</li>
                                    <li class="is-current is-exception"><span></span>{{ $exceptionLabels[$status] }}</li>
                                @else
                                    @foreach ($timelineLabels as $key => $label)
                                        @php
                                            $orderIndex = array_search($status, array_keys($timelineLabels), true);
                                            $itemIndex = $loop->index;
                                        @endphp
                                        <li @class(['is-complete' => $orderIndex !== false && $itemIndex < $orderIndex, 'is-current' => $key === $status || ($status === 'payment_pending' && $loop->first)])>
                                            <span></span>{{ $label }}
                                        </li>
                                    @endforeach
                                @endif
                            </ol>

                            <div class="track-order-grid">
                                <section>
                                    <h3>Seller / Store</h3>
                                    <p>{{ $sellerNames->isNotEmpty() ? $sellerNames->join(', ') : 'Sushako Store' }}</p>
                                    <small>{{ $sellerCities->isNotEmpty() ? $sellerCities->join(', ') : 'India' }}</small>
                                </section>
                                <section>
                                    <h3>Delivery</h3>
                                    <p>{{ $order->customer_name }} · {{ $order->city }} - {{ $order->pincode }}</p>
                                    <small>{{ $order->shipping_provider ? str($order->shipping_provider)->title() : 'Manual shipment updates' }}{{ $order->tracking_number ? ' · '.$order->tracking_number : '' }}</small>
                                </section>
                                <section>
                                    <h3>Payment</h3>
                                    <p>{{ str($order->payment_method)->replace('_', ' ')->title() }}</p>
                                    <small>{{ str($order->payment_status)->title() }}</small>
                                </section>
                            </div>

                            <section class="track-order-items">
                                <h3>Items</h3>
                                @foreach ($order->items as $item)
                                    <div>
                                        <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                                        <strong>&#8377;{{ number_format($item->line_total) }}</strong>
                                    </div>
                                @endforeach
                            </section>

                            @if ($canChooseOverdueAction)
                                <section class="track-order-items">
                                    <h3>Seller acceptance is delayed</h3>
                                    <p>The seller had 24 hours to accept this order. You can wait for the seller or request a refund.</p>
                                    <p>If a refund is processed, Razorpay or payment gateway charges may be deducted where applicable. COD orders usually have no online gateway deduction.</p>
                                    <div class="buy-actions">
                                        <form method="POST" action="{{ route('order.wait-for-seller', $order->order_number) }}">@csrf<button class="button button--secondary" type="submit">Wait for Seller</button></form>
                                        <form method="POST" action="{{ route('order.request-refund', $order->order_number) }}">@csrf<button class="button button--primary" type="submit">Request Refund</button></form>
                                    </div>
                                </section>
                            @endif

                            <footer>
                                @if ($order->status === 'payment_pending' && $canManageOrder)
                                    <a class="button button--primary" href="{{ route('order.payment', $order->order_number) }}">Complete Payment</a>
                                @endif
                                @if ($safeInvoice)
                                    <a class="button button--ghost" href="{{ route('order.invoice', $order->order_number) }}">Download Invoice</a>
                                @endif
                                @if ($canCancel)
                                    <form method="POST" action="{{ route('order.cancel', $order->order_number) }}">@csrf<button class="button button--ghost" type="submit">Cancel Order</button></form>
                                @endif
                                <a class="button button--secondary" href="https://wa.me/{{ config('services.whatsapp.support_number') }}?text={{ rawurlencode('Hello Sushako Shopping, I need help with order '.$order->order_number) }}" target="_blank" rel="noopener noreferrer">Need Help?</a>
                            </footer>
                        </article>
                    @endforeach
                @endif
            </section>
        </div>
    </section>
</x-layouts.customer>
