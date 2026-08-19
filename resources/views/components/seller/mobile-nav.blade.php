@php
    $items = [
        ['label' => 'Home', 'route' => 'seller.dashboard', 'active' => ['seller.dashboard'], 'icon' => 'fa-solid fa-house'],
        ['label' => 'Orders', 'route' => 'seller.orders.index', 'active' => ['seller.orders.*'], 'icon' => 'fa-solid fa-receipt'],
        ['label' => 'Products', 'route' => 'seller.products.index', 'active' => ['seller.products.*'], 'icon' => 'fa-solid fa-box'],
        ['label' => 'Earnings', 'route' => 'seller.earnings.index', 'active' => ['seller.earnings.*', 'seller.settlements.*'], 'icon' => 'fa-solid fa-chart-line'],
        ['label' => 'More', 'route' => 'seller.settings.index', 'active' => ['seller.settings.*', 'seller.shipping.*', 'seller.customers.*', 'seller.support.*', 'seller.store-profile', 'seller.plans.*'], 'icon' => 'fa-solid fa-ellipsis'],
    ];
@endphp

<nav class="seller-mobile-bottom-nav internal-mobile-nav" aria-label="Seller mobile navigation">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs(...$item['active'])])>
            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
