<nav class="admin-settings-tabs" aria-label="Settings sections">
    <a href="{{ route('admin.settings.company') }}" @class(['is-active' => request()->routeIs('admin.settings.company')])>Company</a>
    <a href="{{ route('admin.settings.invoice') }}" @class(['is-active' => request()->routeIs('admin.settings.invoice')])>Invoice</a>
    <a href="{{ route('admin.settings.tax') }}" @class(['is-active' => request()->routeIs('admin.settings.tax')])>Tax</a>
    <a href="{{ route('admin.settings.shipping') }}" @class(['is-active' => request()->routeIs('admin.settings.shipping')])>Shipping</a>
    <a href="{{ route('admin.settings.payments') }}" @class(['is-active' => request()->routeIs('admin.settings.payments')])>Payments</a>
    <a href="{{ route('admin.settings.placeholder', 'policies') }}" @class(['is-active' => request()->fullUrlIs(route('admin.settings.placeholder', 'policies'))])>Policies</a>
    <a href="{{ route('admin.settings.placeholder', 'notifications') }}" @class(['is-active' => request()->fullUrlIs(route('admin.settings.placeholder', 'notifications'))])>Notifications</a>
</nav>
