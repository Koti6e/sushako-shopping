<x-layouts.admin title="Inventory - Admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
            <nav aria-label="Admin navigation">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.categories.index') }}">Categories</a>
                <a href="{{ route('admin.products.index') }}">Products</a>
                <a href="{{ route('admin.inventory.index') }}">Inventory</a>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
                <a href="{{ route('admin.customers.index') }}">Customers</a>
                <a href="{{ route('admin.settings.company') }}"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <x-admin.side-meta />
        </aside>
        <main class="admin-main">
            <header class="admin-topbar"><x-brand.logo href="{{ route('admin.dashboard') }}" loading="eager" /><span>Inventory</span></header>
            <section class="admin-dashboard-panel">
                <p class="eyebrow">Inventory Management</p>
                <h1>Stock overview</h1>
                <div class="admin-table">
                    @foreach ($products as $product)
                        <article>
                            <img src="{{ $product['images'][0]['path'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            <div>
                                <h2>{{ $product['name'] }}</h2>
                                <p>{{ $product['stock_label'] }} · {{ count($product['variants']) }} variants</p>
                            </div>
                            <a class="button button--primary" href="{{ route('admin.products.edit', $product['slug']) }}">Manage</a>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>
    </div>
</x-layouts.admin>
