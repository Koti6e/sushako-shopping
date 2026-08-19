@php
    $controlCenter = app(\App\Services\AdminControlCenterService::class)->sidebar();
    $badge = fn (?int $count): ?string => $count ? ($count > 99 ? '99+' : (string) $count) : null;
    $isActive = fn (array $patterns): bool => collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
@endphp

<button class="admin-sidebar-backdrop admin-control-backdrop" type="button" data-admin-sidebar-close aria-label="Close admin navigation" hidden></button>

<aside class="admin-sidebar admin-control-sidebar" data-admin-sidebar aria-label="Super Admin navigation">
    <div class="admin-control-sidebar__mobile-head">
        <strong>Control Center</strong>
        <button type="button" data-admin-sidebar-close aria-label="Close admin navigation">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="admin-control-sidebar__brand">
        <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
        <x-brand.logo context="icon" class="admin-sidebar-brand-icon" href="{{ route('admin.dashboard') }}" loading="eager" />
        <button class="admin-control-sidebar__collapse" type="button" data-admin-sidebar-collapse aria-label="Collapse admin sidebar">
            <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
        </button>
    </div>


    <nav class="admin-control-nav" aria-label="Marketplace control center">
        @foreach ($controlCenter['sections'] as $section)
            @php
                $sectionActive = $isActive($section['active'] ?? []);
                $sectionCount = collect($section['children'] ?? [])->sum(fn ($child) => (int) ($child['count'] ?? 0));
            @endphp

            @if (isset($section['children']))
                <details class="admin-control-nav__section" @if($sectionActive) open @endif>
                    <summary @class(['is-active' => $sectionActive]) title="{{ $section['label'] }}">
                        <i class="fa-solid {{ $section['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $section['label'] }}</span>
                        @if ($badge($sectionCount))
                            <b>{{ $badge($sectionCount) }}</b>
                        @endif
                        <em><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></em>
                    </summary>
                    <div>
                        @foreach ($section['children'] as $child)
                            @php
                                $childActive = $isActive($child['active'] ?? []);
                                $url = route($child['route'], $child['params'] ?? []);
                            @endphp
                            <a href="{{ $url }}" @class(['is-active' => $childActive]) @if($childActive) aria-current="page" @endif @if($child['external'] ?? false) target="_blank" rel="noreferrer" @endif>
                                <span>{{ $child['label'] }}</span>
                                @if ($badge($child['count'] ?? null))
                                    <b>{{ $badge($child['count']) }}</b>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </details>
            @else
                @php
                    $active = $isActive($section['active'] ?? []);
                @endphp
                <a class="admin-control-nav__direct @if($active) is-active @endif" href="{{ route($section['route']) }}" title="{{ $section['label'] }}" @if($active) aria-current="page" @endif>
                    <i class="fa-solid {{ $section['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $section['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="admin-control-sidebar__account">
        <button type="button" data-admin-account-trigger aria-haspopup="menu" aria-expanded="false">
            <span>{{ str(auth()->user()?->name ?: 'A')->substr(0, 1)->upper() }}</span>
            <strong>{{ auth()->user()?->name ?: 'Super Admin' }}</strong>
            <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
        </button>
        <div class="admin-control-sidebar__account-menu" role="menu" data-admin-account-menu hidden>
            <a href="{{ route('admin.settings.company') }}" role="menuitem"><i class="fa-solid fa-building" aria-hidden="true"></i>Company Settings</a>
            <a href="{{ route('admin.approvals.index') }}" role="menuitem"><i class="fa-solid fa-inbox" aria-hidden="true"></i>Approval Center</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" role="menuitem"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</button>
            </form>
        </div>
    </div>
</aside>
