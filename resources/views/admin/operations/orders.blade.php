<x-layouts.admin title="Orders - Admin">
    <x-admin.shell title="Orders">
        <x-slot:actions>
            <x-admin.action :href="route('admin.shipping-labels.index')" tone="secondary" icon="fa-solid fa-tags">Labels</x-admin.action>
            <x-admin.action :href="route('admin.orders.index')" tone="primary" icon="fa-solid fa-arrows-rotate">Refresh</x-admin.action>
        </x-slot:actions>

        <section class="admin-dashboard-panel admin-orders-page admin-orders-table-page">
            @include('admin.shipping-labels.partials.orders-nav')

            <div class="admin-orders-page__heading">
                <div>
                    <h1>Order Management</h1>
                    <p>Review customer orders, check fulfilment details and open orders for processing.</p>
                </div>

                <div class="admin-orders-page__count">
                    <span>Orders on this page</span>
                    <strong>{{ $orders->count() }}</strong>
                </div>
            </div>

            @if (session('status'))
                <div class="status-banner" role="status">{{ session('status') }}</div>
            @endif

            <section class="admin-orders-table-card" aria-label="Customer orders">
                @if ($orders->isEmpty())
                    <div class="admin-orders-table-empty">
                        <span class="admin-orders-table-empty__icon" aria-hidden="true">
                            <i class="fa-solid fa-box-open"></i>
                        </span>
                        <h2>No orders found</h2>
                        <p>Orders will appear here after customers complete checkout.</p>
                    </div>
                @else
                    <div class="admin-orders-table-scroll">
                        <table class="admin-orders-table">
                            <thead>
                                <tr>
                                    <th scope="col">Order</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Items</th>
                                    <th scope="col">Order Total</th>
                                    <th scope="col">Shipping</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        $providerLabel = $order->shipping_provider === 'others'
                                            ? $order->shipping_provider_other
                                            : ($shippingProviders[$order->shipping_provider] ?? 'Not selected');

                                        $statusLabel = match ($order->status) {
                                            'placed' => 'New Order',
                                            'packing' => 'Processing',
                                            'shipped' => 'In Transit',
                                            'delivered' => 'Delivered',
                                            default => str($order->status)->replace('_', ' ')->title()->toString(),
                                        };
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="admin-orders-table__order">
                                                <a href="{{ route('admin.orders.show', $order->order_number) }}">
                                                    {{ $order->order_number }}
                                                </a>

                                                <time datetime="{{ $order->placed_at?->toIso8601String() }}">
                                                    {{ $order->placed_at?->format('d M Y, h:i A') ?? 'Date unavailable' }}
                                                </time>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="admin-orders-table__customer">
                                                <strong>{{ $order->customer_name }}</strong>
                                                <span>{{ $order->customer_phone }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="admin-orders-table__item-count">
                                                {{ $order->items->count() }}
                                                {{ $order->items->count() === 1 ? 'item' : 'items' }}
                                            </span>
                                        </td>

                                        <td>
                                            <strong class="admin-orders-table__amount">
                                                ₹{{ number_format((float) $order->total_amount, 2) }}
                                            </strong>
                                        </td>

                                        <td>
                                            <span class="admin-orders-table__shipping">
                                                {{ filled($providerLabel) ? $providerLabel : 'Not selected' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="admin-orders-table__status admin-orders-table__status--{{ $order->status }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <td>
                                            <a
                                                class="admin-orders-table__open"
                                                href="{{ route('admin.orders.show', $order->order_number) }}"
                                                aria-label="Open order {{ $order->order_number }}"
                                            >
                                                Open Order
                                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-orders-table-pagination">
                        {{ $orders->links() }}
                    </div>
                @endif
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
