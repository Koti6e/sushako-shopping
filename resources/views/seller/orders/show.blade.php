<x-layouts.seller title="Order {{ $order->order_number }}">
    <x-seller.header title="Order {{ $order->order_number }}" subtitle="Customer delivery, item earnings, commission and fulfilment status." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        <div class="seller-dashboard-grid">
            <section class="seller-panel">
                <h2>Customer Delivery</h2>
                <p>{{ $order->customer_name }} · {{ $order->customer_phone }} · {{ $order->customer_email }}</p>
                <p>{{ $order->address_line_1 }}, {{ $order->address_line_2 }}, {{ $order->city }} {{ $order->pincode }}</p>
                @if ($order->delivery_location_url)
                    <a class="button button--secondary" href="{{ $order->delivery_location_url }}" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Open in Google Maps
                    </a>
                @endif
            </section>
            <section class="seller-panel">
                <h2>Fulfilment</h2>
                <form class="seller-stack-form" method="POST" action="{{ route('seller.orders.update', $order->order_number) }}">
                    @csrf @method('PUT')
                    <select name="seller_order_status"><option value="accepted">Accept</option><option value="packed">Packed</option><option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancel</option></select>
                    <input name="shipping_provider" placeholder="Courier" value="{{ $order->shipping_provider }}">
                    <input name="tracking_number" placeholder="Tracking number" value="{{ $order->tracking_number }}">
                    <input name="tracking_url" placeholder="Tracking URL" value="{{ $order->tracking_url }}">
                    <input name="reason" placeholder="Reason for cancellation when applicable">
                    <button>Update Order</button>
                </form>
            </section>
        </div>
        <section class="seller-panel">
            <h2>Seller Items</h2>
            @foreach ($order->items as $item)
                <div class="seller-key-value"><span>{{ $item->product_name }} x {{ $item->quantity }}</span><strong>Gross Rs {{ number_format((int) $item->gross_line_amount) }} · Platform Fee Rs {{ number_format((int) ($item->platform_fee_total ?: $item->commission_amount)) }} · Earning Rs {{ number_format((int) $item->seller_earning) }}</strong></div>
            @endforeach
        </section>
    </section>
</x-layouts.seller>
