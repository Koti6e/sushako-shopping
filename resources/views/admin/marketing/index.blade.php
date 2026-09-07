<x-layouts.admin title="Content Creator - Admin">
    <x-admin.shell eyebrow="Marketing" title="Content Creator" subtitle="Schedule customer-facing homepage content from one controlled workspace.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.content.collections.index')" tone="secondary" icon="fa-solid fa-layer-group">Collections</x-admin.action>
            <x-admin.action :href="route('admin.content.create')" tone="primary" icon="fa-solid fa-plus">Create Content</x-admin.action>
        </x-slot:actions>

        <section class="admin-dashboard-panel">
            @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
            <form class="admin-filter-bar" method="GET">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search content">
                <select name="type"><option value="">All content types</option>@foreach($contentTypes as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                <select name="status"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option></select>
                <button class="button button--secondary" type="submit">Filter</button>
            </form>

            <div class="admin-content-table">
                <div class="admin-content-table__head"><span>Content</span><span>Placement</span><span>Destination</span><span>Schedule</span><span>Status</span><span>Actions</span></div>
                @forelse($contents as $content)
                    @php
                        $destination = $content->category?->name ?: $content->product?->name ?: $content->productCollection?->name ?: ($content->destination_type === 'url' ? 'Custom URL' : 'None');
                    @endphp
                    <article class="admin-content-table__row">
                        <div class="admin-content-cell__title"><span class="admin-content-type-icon"><i class="fa-solid {{ $content->type === 'announcement' ? 'fa-bullhorn' : ($content->type === 'campaign' ? 'fa-calendar-days' : 'fa-panorama') }}" aria-hidden="true"></i></span><div><strong>{{ $content->title }}</strong><small>{{ str($content->type)->replace('_', ' ')->title() }}</small></div></div>
                        <span>{{ str($content->placement)->replace('_', ' ')->title() }}</span>
                        <span>{{ $destination }}</span>
                        <span>{{ $content->starts_at?->format('d M Y') ?: 'Now' }} - {{ $content->ends_at?->format('d M Y') ?: 'No end' }}</span>
                        <span class="admin-status-badge {{ $content->is_active ? 'is-active' : 'is-inactive' }}">{{ $content->is_active ? 'Active' : 'Inactive' }}</span>
                        <div class="admin-content-cell__actions"><a class="button button--secondary" href="{{ route('admin.content.edit', $content) }}">Edit</a><form method="POST" action="{{ route('admin.content.status.update', $content) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $content->is_active ? 0 : 1 }}"><button class="button button--ghost" type="submit">{{ $content->is_active ? 'Pause' : 'Activate' }}</button></form></div>
                    </article>
                @empty
                    <x-ui.empty-state title="No content yet" description="Create a hero, promotion, campaign, or announcement when it is ready for customers." />
                @endforelse
            </div>
            {{ $contents->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
