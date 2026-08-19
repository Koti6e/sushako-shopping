@props(['data'])
@php
    $vendor = $data['vendor'];
    $user = $vendor?->user ?? auth()->user();
    $name = $vendor?->store_display_name ?: $vendor?->business_name ?: 'Sushako Seller';
    $status = str($vendor?->store_status ?: 'setup_required')->replace('_', ' ')->title()->toString();
    $plan = str($vendor?->current_plan ?: $vendor?->selected_plan ?: 'free')->title()->toString();
    $initial = str($name)->trim()->substr(0, 1)->upper();
    $badge = fn (?int $count): ?string => $count ? ($count > 99 ? '99+' : (string) $count) : null;
    $isActive = fn (array $patterns): bool => collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
    $profileRoute = $data['setupComplete'] ? route('seller.store-profile') : route('seller.onboarding');
@endphp

<aside class="seller-elite-sidebar" data-seller-sidebar id="seller-sidebar">
    <div class="seller-elite-sidebar__scroll">
        <div class="seller-elite-sidebar__brand">
            <x-brand.logo context="seller" href="{{ route('seller.dashboard') }}" loading="eager" />
            <x-brand.logo context="icon" class="seller-sidebar-brand-icon" href="{{ route('seller.dashboard') }}" loading="eager" />
            <button class="seller-elite-sidebar__collapse" type="button" data-sidebar-collapse aria-label="Collapse seller sidebar">
                <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
            </button>
        </div>

        <section class="seller-quick-actions" data-seller-quick-actions>
            <button class="seller-quick-actions__trigger" type="button" aria-haspopup="menu" aria-expanded="false" data-quick-action-trigger title="Quick Action">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Quick Action</span>
            </button>
            <div class="seller-quick-actions__menu" role="menu" data-quick-action-menu hidden>
                @foreach ($data['quickActions'] as $action)
                    @if ($action['enabled'])
                        <a href="{{ route($action['route']) }}" role="menuitem" @if ($action['external'] ?? false) target="_blank" rel="noreferrer" @endif>
                            <i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i>
                            <span><strong>{{ $action['label'] }}</strong><small>{{ $action['description'] ?? '' }}</small></span>
                        </a>
                    @else
                        <a href="{{ route($action['route']) }}" role="menuitem" class="is-priority">
                            <i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i>
                            <span><strong>{{ $action['label'] }}</strong><small>{{ $action['description'] ?? 'Required before selling' }}</small></span>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <nav class="seller-elite-nav" aria-label="Seller portal navigation">
            @foreach ($data['navigation'] as $group => $items)
                <section class="seller-elite-nav__group" aria-label="{{ $group }}">
                    <p>{{ strtoupper($group) }}</p>
                    @foreach ($items as $item)
                        @php
                            $active = $isActive($item['activeRoutes']);
                            $countLabel = $badge($item['count']);
                        @endphp
                        @if ($item['enabled'])
                            <a href="{{ route($item['route']) }}" @class(['is-active' => $active]) @if ($active) aria-current="page" @endif title="{{ $item['label'] }}">
                                <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $item['label'] }}</span>
                                @if ($countLabel)<b>{{ $countLabel }}</b>@endif
                            </a>
                        @else
                            <span class="is-disabled" tabindex="0" title="{{ $item['disabledReason'] }}">
                                <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $item['label'] }}</span>
                            </span>
                        @endif
                    @endforeach
                </section>
            @endforeach
        </nav>
    </div>

    <section class="seller-account-menu" data-seller-account>
        <button type="button" aria-haspopup="menu" aria-expanded="false" data-account-trigger>
            <span class="seller-account-menu__avatar">{{ str($user?->name ?: $name)->substr(0, 1)->upper() }}</span>
            <span class="seller-account-menu__copy">
                <strong>{{ $user?->name ?: $name }}</strong>
                <small>{{ $user?->email }}</small>
            </span>
            <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
        </button>
        <div class="seller-account-menu__panel" role="menu" data-account-menu hidden>
            @foreach ($data['accountActions'] as $action)
                @if ($action['enabled'])
                    <a href="{{ route($action['route']) }}" role="menuitem"><i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i>{{ $action['label'] }}</a>
                @else
                    <span class="is-disabled" role="menuitem" aria-disabled="true"><i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i>{{ $action['label'] }}</span>
                @endif
            @endforeach
            <form method="POST" action="{{ route('seller.logout') }}">
                @csrf
                <button type="submit" role="menuitem"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</button>
            </form>
        </div>
    </section>
</aside>
