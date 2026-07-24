<x-layouts.admin title="Orders - Admin">
    <section class="admin-shell">
        <aside class="admin-sidebar">
            <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
            <nav>
                <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                <a href="{{ route('admin.products.index') }}"><i class="fa-solid fa-box"></i> Products</a>
                <a href="{{ route('admin.inventory.index') }}"><i class="fa-solid fa-warehouse"></i> Inventory</a>
                <a href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-receipt"></i> Orders</a>
                <a href="{{ route('admin.customers.index') }}"><i class="fa-solid fa-users"></i> Customers</a>
                <a href="{{ route('admin.settings.company') }}"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <x-admin.side-meta />
        </aside>

        <main class="admin-main">
            <header class="admin-topbar admin-topbar--premium">
                <div>
                    <span>Order Management</span>
                    <strong>Pack, ship, deliver</strong>
                </div>
                <div class="admin-topbar-actions">
                    <a class="button button--secondary" href="{{ route('admin.orders.index') }}">Order Queue</a>
                    <form class="admin-global-logout admin-global-logout--top" method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</button>
                    </form>
                </div>
            </header>
            <section class="admin-dashboard-panel">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Realtime Orders</p>
                        <h1>Order Queue</h1>
                        <p class="lede">Track Sushako store orders by stage. New orders blink softly because they need immediate action.</p>
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
        </main>
    </section>
</x-layouts.admin>
