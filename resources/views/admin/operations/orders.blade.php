<x-layouts.admin title="Orders - Admin">
    <x-admin.shell title="Orders">
        <x-slot:actions>
            <x-admin.action :href="route('admin.shipping-labels.index')" tone="secondary" icon="fa-solid fa-tags">Labels</x-admin.action>
            <x-admin.action :href="route('admin.orders.index')" tone="primary" icon="fa-solid fa-arrows-rotate">Refresh</x-admin.action>
        </x-slot:actions>
            <section class="admin-dashboard-panel">
                @include('admin.shipping-labels.partials.orders-nav')

                <div class="section-heading">
                    <div>
                        <h1>Order Queue</h1>
                    </div>
                </div>

                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <section class="admin-orders-board admin-orders-board--lanes">
                    @if ($orders->isEmpty())
                        <div class="empty-cart">
                            <h2>No orders yet</h2>
                            <p>Orders will appear here after checkout placement.</p>
                        </div>
                    @else
                        @foreach ($orderLanes as $status => $lane)
                            <section class="admin-order-lane admin-order-lane--{{ $status }}">
                                <div class="admin-order-lane__head">
                                    <div>
                                        <h2>{{ $lane['title'] }}</h2>
                                        <p>{{ $lane['subtitle'] }}</p>
                                    </div>
                                    <strong>{{ $lane['orders']->count() }}</strong>
                                </div>

                                <div class="admin-order-lane__cards">
                                    @forelse ($lane['orders'] as $order)
                                        @php
                                            $providerLabel = $order->shipping_provider === 'others'
                                                ? $order->shipping_provider_other
                                                : ($shippingProviders[$order->shipping_provider] ?? 'Not selected');
                                        @endphp
                                        <article class="admin-order-card admin-order-card--compact admin-order-card--{{ $status }} admin-order-card--clickable @if($status === 'placed') admin-order-card--urgent @endif">
                                            <a class="admin-order-card__summary admin-order-card__summary--compact" href="{{ route('admin.orders.show', $order->order_number) }}">
                                                <div>
                                                    <p class="eyebrow">{{ $order->placed_at?->format('d M, h:i A') }}</p>
                                                    <h3>{{ $order->order_number }}</h3>
                                                    <p>{{ $order->customer_name }}</p>
                                                    <p>{{ $order->customer_phone }}</p>
                                                </div>
                                                <div class="admin-order-card__summary-actions">
                                                    @if ($status === 'placed')
                                                        <span class="admin-action-pulse">Immediate Action</span>
                                                    @else
                                                        <span class="admin-status-pill admin-status-pill--{{ $order->status }}">{{ $lane['title'] }}</span>
                                                    @endif
                                                    <span class="admin-open-indicator"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i> Open</span>
                                                </div>
                                            </a>
                                            <div class="admin-order-card__queue-meta">
                                                <span>&#8377;{{ number_format($order->total_amount) }}</span>
                                                <span>{{ $order->items->count() }} item{{ $order->items->count() === 1 ? '' : 's' }}</span>
                                                <span>{{ $providerLabel }}</span>
                                            </div>
                                        </article>
                                    @empty
                                        <p class="admin-order-lane__empty">No {{ strtolower($lane['title']) }}.</p>
                                    @endforelse
                                </div>
                            </section>
                        @endforeach
                    @endif
                    {{ $orders->links() }}
                </section>
            </section>
    </x-admin.shell>
</x-layouts.admin>
