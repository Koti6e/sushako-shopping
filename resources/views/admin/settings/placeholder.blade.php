<x-layouts.admin title="{{ ucfirst($section) }} Settings - Admin">
    <div class="admin-shell">
        @include('admin.settings.partials.sidebar')
        <main class="admin-main">
            <header class="admin-topbar admin-topbar--premium"><div><span>Settings</span><strong>{{ ucfirst($section) }}</strong></div></header>
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                <p class="eyebrow">Future Module</p>
                <h1>{{ ucfirst($section) }} settings</h1>
                <p class="lede">This area is reserved for the later {{ $section }} phase. No live business logic is attached here yet.</p>
            </section>
        </main>
    </div>
</x-layouts.admin>
