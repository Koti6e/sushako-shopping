@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'badge' => null,
    'breadcrumbs' => null,
    'showSubtitle' => false,
])

<header class="internal-page-header admin-topbar admin-topbar--premium admin-page-header">
    <button class="admin-mobile-menu-button" type="button" data-admin-sidebar-open aria-label="Open admin navigation" aria-expanded="false">
        <x-admin.nav-icon name="menu" />
    </button>
    <div class="admin-topbar__copy admin-page-header__content">
        @php
            $crumbs = collect($breadcrumbs ?: request()->segments())
                ->reject(fn ($segment) => in_array($segment, ['admin'], true))
                ->reject(fn ($segment) => is_numeric($segment))
                ->map(fn ($segment) => str($segment)->replace('-', ' ')->title()->toString())
                ->values();
        @endphp
        @if ($crumbs->isNotEmpty())
            <nav class="admin-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Super Admin</a>
                @foreach ($crumbs as $crumb)
                    <span aria-hidden="true">/</span>
                    <span>{{ $crumb }}</span>
                @endforeach
            </nav>
        @endif
        <div>
            <strong>{{ $title }}</strong>
        </div>
        @if ($showSubtitle && $subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && trim((string) $actions) !== '')
        <div class="admin-topbar-actions admin-page-header__actions" role="toolbar" aria-label="Page actions">
            {{ $actions }}
        </div>
    @endif
</header>
