<x-layouts.admin title="Products - Admin">
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
                <a href="{{ route('home') }}">Storefront</a>
            </nav>
            <x-admin.side-meta />
        </aside>
        <main class="admin-main">
            <header class="admin-topbar">
                <x-brand.logo href="{{ route('admin.dashboard') }}" loading="eager" />
                <span>Product Management</span>
            </header>
            <section class="admin-dashboard-panel">
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif
                <p class="eyebrow">Product Management</p>
                <h1>Own-store products</h1>
                <p class="lede">Review Sushako products, pricing, stock status and quick actions from one focused catalog view.</p>
                <a class="button button--primary" href="{{ route('admin.products.create') }}">Add Product</a>
                <div class="admin-table">
                    @foreach ($products as $product)
                        <article>
                            <img src="{{ $product['images'][0]['path'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            <div>
                                <h2>{{ $product['name'] }}</h2>
                                <p>{{ $product['category'] }} / {{ $product['subcategory'] }} · {{ $product['stock_label'] }}</p>
                                <strong>&#8377;{{ number_format($product['selling_price']) }}</strong>
                            </div>
                            <a class="button button--primary" href="{{ route('admin.products.edit', $product['slug']) }}">Edit</a>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>
    </div>
</x-layouts.admin>
