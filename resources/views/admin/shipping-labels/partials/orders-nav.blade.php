<nav class="admin-orders-subnav" aria-label="Orders">
    <a href="{{ route('admin.orders.index') }}">All Orders</a>
    <a href="{{ route('admin.orders.index') }}?status=placed">New Orders</a>
    <a href="{{ route('admin.orders.index') }}?status=packing">Processing</a>
    <a href="{{ route('admin.orders.index') }}?status=packing">Packed</a>
    <a href="{{ route('admin.orders.index') }}?status=shipped">Shipped</a>
    <a href="{{ route('admin.orders.index') }}?status=delivered">Delivered</a>
    <a href="{{ route('admin.orders.index') }}?status=cancelled">Cancelled</a>
    <a href="{{ route('admin.orders.index') }}?status=returns">Returns</a>
    <a href="{{ route('admin.shipping-labels.index') }}" @class(['is-active' => request()->routeIs('admin.shipping-labels.*')])>Labels</a>
    <a href="{{ route('admin.shipping-labels.index') }}#bulk-actions">Bulk Actions</a>
</nav>
