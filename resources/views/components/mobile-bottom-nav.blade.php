@php
    $user = auth()->user();
    $isCustomer = $user?->role === \App\Models\User::ROLE_CUSTOMER;
    $isAdmin = $user?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN);

    $ordersHref = $isAdmin ? route('admin.orders.index') : route('orders.track');
    $accountHref = route('orders.track');
    $accountLabel = 'Track';

    if ($isCustomer) {
        $accountHref = route('account.show');
        $accountLabel = 'Account';
    } elseif ($isAdmin) {
        $accountHref = route('admin.dashboard');
        $accountLabel = 'Admin';
    }

    $items = [
        [
            'label' => 'Home',
            'href' => route('home'),
            'active' => request()->routeIs('home'),
            'icon' => 'home',
        ],
        [
            'label' => 'Categories',
            'href' => route('products.index'),
            'active' => request()->routeIs('shop', 'products.*', 'department.show', 'category.show', 'search'),
            'icon' => 'grid',
        ],
        [
            'label' => 'Wishlist',
            'href' => route('shop').'?wishlist=1',
            'active' => request()->query('wishlist'),
            'icon' => 'heart',
        ],
        [
            'label' => 'Orders',
            'href' => $ordersHref,
            'active' => request()->routeIs('orders.*', 'order.*'),
            'icon' => 'receipt',
        ],
        [
            'label' => $accountLabel,
            'href' => $accountHref,
            'active' => request()->routeIs('account.*', 'profile.*', 'orders.track', 'admin.dashboard'),
            'icon' => 'user',
        ],
    ];
@endphp

<nav class="mobile-bottom-nav" aria-label="Mobile primary navigation">
    @foreach ($items as $item)
        <a
            class="mobile-bottom-nav__item @if ($item['active']) is-active @endif"
            href="{{ $item['href'] }}"
            aria-label="{{ $item['label'] }}"
            @if ($item['active']) aria-current="page" @endif
        >
            @switch($item['icon'])
                @case('home')
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="m3 10.8 9-7.2 9 7.2"></path>
                        <path d="M5.2 9.5V20h4.9v-5.7h3.8V20h4.9V9.5"></path>
                    </svg>
                    @break

                @case('grid')
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M4 4h6v6H4z"></path>
                        <path d="M14 4h6v6h-6z"></path>
                        <path d="M4 14h6v6H4z"></path>
                        <path d="M14 14h6v6h-6z"></path>
                    </svg>
                    @break

                @case('heart')
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M20.8 4.8a5.4 5.4 0 0 0-7.7 0L12 5.9l-1.1-1.1a5.4 5.4 0 0 0-7.7 7.7L12 21l8.8-8.5a5.4 5.4 0 0 0 0-7.7Z"></path>
                    </svg>
                    @break

                @case('receipt')
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M6.5 3.8h11v16.4l-2-1.2-1.8 1.2-1.7-1.2-1.7 1.2L8.5 19l-2 1.2V3.8Z"></path>
                        <path d="M9.2 8h5.6"></path>
                        <path d="M9.2 12h5.6"></path>
                        <path d="M9.2 16h3.3"></path>
                    </svg>
                    @break

                @case('user')
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M12 12.2a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"></path>
                        <path d="M4.8 20.2a7.2 7.2 0 0 1 14.4 0"></path>
                    </svg>
                    @break
            @endswitch
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
