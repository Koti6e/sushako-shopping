@props(['title', 'subtitle' => null, 'vendor' => null, 'breadcrumbs' => null, 'showSubtitle' => false])
<header class="internal-page-header seller-topbar">
    <button class="seller-mobile-menu-button" type="button" data-seller-menu-open aria-label="Open seller navigation">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>
    <div class="seller-topbar__copy">
        @php
            $crumbs = collect($breadcrumbs ?: request()->segments())
                ->reject(fn ($segment) => in_array($segment, ['seller'], true))
                ->reject(fn ($segment) => is_numeric($segment))
                ->map(fn ($segment) => str($segment)->replace('-', ' ')->title()->toString())
                ->values();
        @endphp
        @if ($crumbs->isNotEmpty())
            <nav class="seller-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('seller.dashboard') }}">Seller</a>
                @foreach ($crumbs as $crumb)
                    <span aria-hidden="true">/</span>
                    <span>{{ $crumb }}</span>
                @endforeach
            </nav>
        @endif
        <h1>{{ $title }}</h1>
        @if ($showSubtitle && $subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    <div class="seller-topbar__actions">
        <button class="seller-sidebar-collapse-button" type="button" data-sidebar-collapse aria-label="Collapse seller sidebar">
            <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
        </button>
        <span class="seller-pill">{{ str($vendor?->store_status ?: 'setup_required')->replace('_', ' ')->title() }}</span>
        <span class="seller-pill seller-pill--plan">{{ str($vendor?->current_plan ?: 'free')->title() }}</span>
        @if ($vendor?->onboarding_status === 'complete' && $vendor?->dashboard_access_enabled)
            <a href="{{ route('seller.products.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add Product</a>
        @endif
    </div>
</header>
