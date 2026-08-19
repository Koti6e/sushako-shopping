<x-layouts.admin title="{{ ucfirst($section) }} Settings - Admin">
    <x-admin.shell eyebrow="Settings" title="{{ ucfirst($section) }}" subtitle="Reserved for a later {{ $section }} phase.">
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                <p class="eyebrow">Future Module</p>
                <h1>{{ ucfirst($section) }} settings</h1>
                <p class="lede">This area is reserved for the later {{ $section }} phase. No live business logic is attached here yet.</p>
            </section>
    </x-admin.shell>
</x-layouts.admin>
