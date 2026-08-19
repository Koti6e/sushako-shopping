@php
    $items = [
        ['label' => 'Home', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard'], 'icon' => 'fa-solid fa-house'],
        ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => ['admin.orders.*', 'admin.shipping-labels.*'], 'icon' => 'fa-solid fa-receipt'],
        ['label' => 'Sellers', 'route' => 'admin.sellers.index', 'active' => ['admin.sellers.*', 'admin.approvals.*'], 'icon' => 'fa-solid fa-store'],
        ['label' => 'Customers', 'route' => 'admin.customers.index', 'active' => ['admin.customers.*'], 'icon' => 'fa-solid fa-users'],
        ['label' => 'More', 'route' => 'admin.settings.company', 'active' => ['admin.settings.*', 'admin.settlements.*'], 'icon' => 'fa-solid fa-ellipsis'],
    ];
@endphp

<nav class="admin-mobile-bottom-nav internal-mobile-nav" aria-label="Admin mobile navigation">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs(...$item['active'])])>
            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
