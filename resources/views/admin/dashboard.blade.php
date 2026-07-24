<x-layouts.admin title="Admin Dashboard - Sushako Shopping">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
            <nav aria-label="Admin navigation">
                <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                <a href="{{ route('admin.categories.index') }}"><i class="fa-solid fa-layer-group"></i> Categories</a>
                <a href="{{ route('admin.products.index') }}"><i class="fa-solid fa-box"></i> Products</a>
                <a href="{{ route('admin.inventory.index') }}"><i class="fa-solid fa-warehouse"></i> Inventory</a>
                <a href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-receipt"></i> Orders</a>
                <a href="{{ route('admin.customers.index') }}"><i class="fa-solid fa-users"></i> Customers</a>
                <a href="{{ route('admin.settings.company') }}"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="{{ route('home') }}"><i class="fa-solid fa-store"></i> Storefront</a>
            </nav>
            <x-admin.side-meta />
        </aside>
        <main class="admin-main">
            <header class="admin-topbar admin-topbar--premium">
                <div>
                    <span>Sushako Store Admin</span>
                    <strong>Today’s store overview</strong>
                </div>
                <div class="admin-topbar-actions">
                    <a class="button button--secondary" href="{{ route('admin.orders.index') }}">Open Order Queue</a>
                    <form class="admin-global-logout admin-global-logout--top" method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</button>
                    </form>
                </div>
            </header>
            <section class="admin-dashboard-panel admin-command-center">
                <p class="eyebrow">Store Dashboard</p>
                <h1>Your Sushako Store Dashboard</h1>
                <p class="lede">A quick, real-time view of orders, customers, catalog, inventory and fulfillment.</p>

                <div class="admin-kpi-grid admin-kpi-grid--premium">
                    <a href="{{ route('admin.orders.index') }}"><strong>{{ $storeCounts['orders'] }}</strong><span>Orders</span></a>
                    <a href="{{ route('admin.orders.index') }}"><strong>{{ $storeCounts['pending_orders'] }}</strong><span>Backlog</span></a>
                    <a href="{{ route('admin.orders.index') }}"><strong>{{ $storeCounts['shipped_orders'] }}</strong><span>Shipped</span></a>
                    <a href="{{ route('admin.orders.index') }}"><strong>{{ $storeCounts['delivered_orders'] }}</strong><span>Delivered</span></a>
                    <a href="{{ route('admin.customers.index') }}"><strong>{{ $storeCounts['customers'] }}</strong><span>Customers</span></a>
                    <a href="{{ route('admin.products.index') }}"><strong>{{ $storeCounts['products'] }}</strong><span>Products</span></a>
                    <a href="{{ route('admin.inventory.index') }}"><strong>{{ $storeCounts['inventory_alerts'] }}</strong><span>Stock alerts</span></a>
                    <a href="{{ route('admin.orders.index') }}"><strong>&#8377;{{ number_format($storeCounts['revenue']) }}</strong><span>Sales value</span></a>
                </div>

                <div class="admin-quick-actions">
                    <a class="button button--primary" href="{{ route('admin.orders.index') }}">Manage Orders</a>
                    <a class="button button--secondary" href="{{ route('admin.products.create') }}">Add Product</a>
                    <a class="button button--secondary" href="{{ route('admin.categories.create') }}">Add Category</a>
                    <a class="button button--secondary" href="{{ route('admin.inventory.index') }}">Inventory</a>
                    <a class="button button--secondary" href="{{ route('admin.customers.index') }}">Customers</a>
                </div>

                <section class="admin-panel admin-panel--premium">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Order Queue</p>
                            <h2>Orders needing action</h2>
                        </div>
                        <a href="{{ route('admin.orders.index') }}">View all</a>
                    </div>
                    @forelse ($recentOrders as $order)
                        <article class="admin-order-mini-card">
                            <div>
                                <h3>{{ $order->order_number }}</h3>
                                <p>{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
                            </div>
                            <span class="admin-status-pill admin-status-pill--{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                            <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                        </article>
                    @empty
                        <p>No orders yet. Checkout orders will appear here instantly.</p>
                    @endforelse
                </section>
            </section>
        </main>
    </div>
</x-layouts.admin>
