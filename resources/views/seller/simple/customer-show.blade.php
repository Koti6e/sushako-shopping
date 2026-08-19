@php
    $date = ($latestOrder->placed_at ?? $latestOrder->created_at)->format('d M Y');
@endphp

<x-layouts.seller title="{{ $latestOrder->customer_name }} - Customer">
    <x-seller.header title="{{ $latestOrder->customer_name }}" subtitle="My Store Customer · Latest order {{ $latestOrder->order_number }}" :vendor="$vendor" />

    <section class="seller-content seller-customer-profile">
        <article class="seller-customer-identity">
            <span class="customer-avatar">{{ str($latestOrder->customer_name)->substr(0, 2)->upper() }}</span>
            <div>
                <h2>{{ $latestOrder->customer_name }}</h2>
                <p>My Store Customer for {{ $vendor->store_display_name ?: $vendor->business_name }} · Last order {{ $date }}</p>
                <div class="seller-customer-actions">
                    @if ($contactActions['tel'])
                        <a href="{{ $contactActions['tel'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i>Call</a>
                    @endif
                    @if ($contactActions['whatsapp'])
                        <a href="{{ $contactActions['whatsapp'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                    @endif
                    @if ($contactActions['email'])
                        <a href="{{ $contactActions['email'] }}"><i class="fa-regular fa-envelope" aria-hidden="true"></i>Email</a>
                    @endif
                    @if ($contactActions['maps'])
                        <a href="{{ $contactActions['maps'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>Maps</a>
                    @endif
                </div>
            </div>
        </article>

        <section class="customer-panel">
            <div class="customer-panel__head"><h2>Store Summary</h2></div>
            <div class="customer-mini-grid customer-mini-grid--primary">
                @foreach ($summary as $metric)
                    <div>
                        <span>{{ $metric['label'] }}</span>
                        <strong>{{ $metric['value'] }}</strong>
                        <small>{{ $metric['support'] }}</small>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="customer-panel">
            <div class="customer-panel__head"><h2>Delivery Information</h2></div>
            <dl class="customer-detail-list customer-detail-list--compact">
                <div><dt>Customer Name</dt><dd>{{ $latestOrder->customer_name }}</dd></div>
                <div>
                    <dt>Mobile</dt>
                    <dd>
                        @if ($contactActions['tel'])
                            <a class="customer-text-link" href="{{ $contactActions['tel'] }}">{{ $latestOrder->customer_phone }}</a>
                        @else
                            {{ $latestOrder->customer_phone ?: 'Not provided' }}
                        @endif
                    </dd>
                </div>
                <div><dt>Email</dt><dd>@if($contactActions['email'])<a class="customer-text-link" href="{{ $contactActions['email'] }}">{{ $latestOrder->customer_email }}</a>@else{{ $latestOrder->customer_email ?: 'Not provided' }}@endif</dd></div>
                <div><dt>Pincode</dt><dd>{{ $latestOrder->pincode ?: 'Not provided' }}</dd></div>
                <div><dt>Landmark</dt><dd>{{ $latestOrder->landmark ?: 'Not provided' }}</dd></div>
                <div><dt>Google Maps</dt><dd>@if($contactActions['maps'])<a class="customer-text-link" href="{{ $contactActions['maps'] }}" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>@else Not provided @endif</dd></div>
            </dl>
            @if ($deliveryAddress)
                <div class="customer-address-strip">
                    <span>{{ $deliveryAddress }}</span>
                    <button type="button" data-copy-text="{{ $deliveryAddress }}">Copy Address</button>
                </div>
            @endif
        </section>

        <section class="customer-panel customer-panel--orders">
            <div class="customer-panel__head"><h2>Orders with Your Store</h2></div>
            <div class="customer-order-table" role="table" aria-label="Seller customer order history">
                <div class="customer-order-table__head" role="row">
                    <span>Order ID</span>
                    <span>Products</span>
                    <span>Store Amount</span>
                    <span>Payment</span>
                    <span>Status</span>
                    <span>Date</span>
                </div>
                @forelse ($orders as $order)
                    @php
                        $storeAmount = (int) $order->items->sum('line_total');
                        $products = $order->items->pluck('product_name')->filter()->take(2)->join(', ');
                    @endphp
                    <a class="customer-order-row customer-order-row--seller" href="{{ route('seller.orders.show', $order->order_number) }}" role="row">
                        <strong>{{ $order->order_number }}</strong>
                        <span>{{ $products ?: $order->items->count().' products' }}</span>
                        <span>Rs {{ number_format($storeAmount) }}</span>
                        <span>{{ str($order->payment_method)->replace('_', ' ')->title() }} · {{ str($order->payment_status)->title() }}</span>
                        <span><b class="customer-badge customer-badge--{{ $order->status }}">{{ str($order->status)->replace('_', ' ')->title() }}</b></span>
                        <small>{{ ($order->placed_at ?? $order->created_at)->format('d M Y') }}</small>
                    </a>
                @empty
                    <p class="customer-empty-copy">No orders with your store yet.</p>
                @endforelse
            </div>
            {{ $orders->links() }}
        </section>
    </section>
</x-layouts.seller>
