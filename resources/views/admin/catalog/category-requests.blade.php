<x-layouts.admin title="Category Requests - Admin">
    <x-admin.shell eyebrow="Catalog" title="Category Requests" subtitle="Review missing-category requests without blocking normal seller listings.">
        <x-slot:actions><x-admin.action :href="route('admin.categories.index')" tone="secondary" icon="fa-solid fa-folder-tree">Master Categories</x-admin.action></x-slot:actions>
        <section class="admin-dashboard-panel">
            @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
            <form class="admin-filter-bar" method="GET"><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search a request or seller"><select name="status"><option value="">All statuses</option><option value="pending_approval" @selected(($filters['status'] ?? '') === 'pending_approval')>Pending</option><option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option><option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option></select><button class="button button--secondary" type="submit">Filter</button></form>
            <div class="admin-request-list">
                @forelse ($requests as $categoryRequest)
                    <article class="admin-request-card">
                        <div class="admin-request-card__summary"><div><p class="eyebrow">{{ $categoryRequest->vendor?->business_name ?: 'Seller' }}</p><h2>{{ $categoryRequest->requested_category_name }}</h2></div><span class="admin-status-badge {{ $categoryRequest->status === 'approved' ? 'is-active' : ($categoryRequest->status === 'rejected' ? 'is-inactive' : 'is-pending') }}">{{ str($categoryRequest->status)->replace('_', ' ')->title() }}</span></div>
                        @if ($categoryRequest->description)<p>{{ $categoryRequest->description }}</p>@endif
                        <dl><div><dt>Suggested parent</dt><dd>{{ $categoryRequest->suggestedParent?->name ?: $categoryRequest->suggested_parent_category ?: 'Not specified' }}</dd></div><div><dt>Example products</dt><dd>{{ $categoryRequest->example_products ?: 'Not provided' }}</dd></div></dl>
                        @if ($categoryRequest->status === \App\Models\SellerCategoryRequest::STATUS_PENDING)
                            <form class="admin-request-card__review" method="POST" action="{{ route('admin.category-requests.update', $categoryRequest) }}">@csrf @method('PUT')
                                <label>Decision<select name="status"><option value="approved">Approve</option><option value="rejected">Reject</option></select></label>
                                <label>Existing Category<select name="resolved_category_id"><option value="">Create or choose below</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>@endforeach</select></label>
                                <label>New Category Name<input name="new_category_name" value="{{ old('new_category_name', $categoryRequest->requested_category_name) }}" maxlength="120"></label>
                                <label>Parent<select name="parent_id"><option value="">Top level</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($categoryRequest->suggested_parent_id === $category->id)>{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>@endforeach</select></label>
                                <label class="admin-field-wide">Review Note<textarea name="admin_comment" required minlength="3" maxlength="500"></textarea></label><button class="button button--primary" type="submit">Save Review</button>
                            </form>
                        @else
                            <p class="admin-request-card__outcome">{{ $categoryRequest->admin_comment }} @if($categoryRequest->resolvedCategory) <a href="{{ route('admin.categories.edit', $categoryRequest->resolvedCategory) }}">View category</a>@endif</p>
                        @endif
                    </article>
                @empty
                    <x-ui.empty-state title="No category requests" description="Seller requests will appear here when no suitable master category exists." />
                @endforelse
            </div>
            {{ $requests->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
