<x-layouts.seller title="Orders">
    <x-seller.header title="Orders" subtitle="Seller-scoped fulfilment queue with shipment status." :vendor="$vendor" />
    @php
        $allOrders = collect($orders->items());
        $statusTabs = collect([
            'all' => 'All Orders',
            'new' => 'New',
            'accepted' => 'Accepted',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ])->map(fn ($label, $status) => [
            'status' => $status,
            'label' => $label,
            'orders' => $status === 'all' ? $allOrders : $allOrders->where('seller_order_status', $status)->values(),
        ]);
    @endphp

    <section class="seller-content seller-orders-app">
        <nav class="seller-order-status-tabs" aria-label="Order status filters">
            @foreach ($statusTabs as $tab)
                <a href="{{ $tab['status'] === 'all' ? route('seller.orders.index') : route('seller.orders.index', ['status' => $tab['status']]) }}">
                    <span>{{ $tab['label'] }}</span>
                    <b>{{ $tab['orders']->count() }}</b>
                </a>
            @endforeach
        </nav>

        <section class="seller-order-card-list">
            @forelse ($statusTabs as $tab)
                <section id="seller-orders-{{ $tab['status'] }}" class="seller-order-status-section">
                    <header>
                        <h2>{{ $tab['label'] }}</h2>
                        <span>{{ $tab['orders']->count() }}</span>
                    </header>
                    @foreach ($tab['orders'] as $order)
                        <a class="seller-order-card" href="{{ route('seller.orders.show', $order->order_number) }}">
                            <span class="seller-order-card__main">
                                <strong>{{ $order->order_number }}</strong>
                                <small>{{ $order->customer_name }}</small>
                            </span>
                            <span class="seller-order-card__meta">
                                <b>Rs {{ number_format($order->total_amount) }}</b>
                                <small>{{ $order->items_count ?? $order->items->count() }} item{{ (($order->items_count ?? $order->items->count()) === 1) ? '' : 's' }}</small>
                            </span>
                            <span class="seller-order-card__deadline">{{ $order->shipment_deadline_at?->format('d M, h:i A') ?? 'Deadline pending' }}</span>
                        </a>
                    @endforeach
                </section>
            @empty
                <div class="seller-empty-state">
                    <strong>No orders yet</strong>
                    <p>Orders for your seller store will appear here after customers buy.</p>
                </div>
            @endforelse
        </section>

        {{ $orders->links() }}
    </section>
</x-layouts.seller>
