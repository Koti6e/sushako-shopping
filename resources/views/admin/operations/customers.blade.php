<x-layouts.admin title="Customers - Admin">
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
            <header class="admin-topbar"><x-brand.logo href="{{ route('admin.dashboard') }}" loading="eager" /><span>Customers</span></header>
            <section class="admin-dashboard-panel">
                <p class="eyebrow">Customer Management</p>
                <h1>Customers</h1>
                <div class="admin-table">
                    @forelse ($customers as $customer)
                        <article>
                            <div></div>
                            <div>
                                <h2>{{ $customer->name }}</h2>
                                <p>{{ $customer->email }} · {{ $customer->phone ?: 'No phone' }} · {{ $customer->status }}</p>
                            </div>
                            <span>{{ $customer->created_at?->format('d M Y') }}</span>
                        </article>
                    @empty
                        <p>No customers yet.</p>
                    @endforelse
                </div>
                {{ $customers->links() }}
            </section>
        </main>
    </div>
</x-layouts.admin>
