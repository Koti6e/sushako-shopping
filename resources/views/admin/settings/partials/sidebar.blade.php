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
    </nav>
    <x-admin.side-meta />
</aside>
