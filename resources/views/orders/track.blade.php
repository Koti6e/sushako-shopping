<x-layouts.customer title="Track Order - Sushako Shopping">
    <section class="content-screen">
        <div class="site-shell checkout-grid">
            <form class="checkout-panel" method="POST" action="{{ route('orders.track.lookup') }}">
                @csrf
                <x-brand.logo loading="eager" />
                <p class="eyebrow">Track Order</p>
                <h1>Your Sushako orders</h1>
                <p class="lede">Only orders placed from your authenticated account are shown here.</p>
                <div class="track-trust-row" aria-label="Tracking trust symbols">
                    <span><i class="fa-solid fa-barcode" aria-hidden="true"></i> Order ID</span>
                    <span><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Account protected</span>
                    <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Delivery status</span>
                </div>
                @if ($errors->any())
                    <div class="status-banner">{{ $errors->first() }}</div>
                @endif
                <label for="query">Filter By Order ID</label>
                <input id="query" name="query" value="{{ $query['query'] ?? old('query') }}" placeholder="SS{{ now()->format('Ym') }}00001">
                <button class="button button--primary" type="submit"><i class="fa-solid fa-route" aria-hidden="true"></i> Track Order</button>
            </form>

            <aside class="order-card">
                <span>Results</span>
                @if ($query && $orders->isEmpty())
                    <strong>No orders found</strong>
                    <p>Please check the order ID and try again. Only orders from your account can be shown.</p>
                @elseif ($orders->isEmpty())
                    <strong>Ready when you are</strong>
                    <p>Your account orders will appear here after checkout is completed.</p>
                @else
                    @foreach ($orders as $order)
                        <article class="mini-cart-line">
                            <div>
                                <h3>{{ $order->order_number }}</h3>
                                <p>{{ $order->customer_name }} · {{ ucfirst($order->status) }} · {{ ucfirst($order->payment_status) }}</p>
                                <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                            </div>
                        </article>
                    @endforeach
                @endif
            </aside>
        </div>
    </section>
</x-layouts.customer>
