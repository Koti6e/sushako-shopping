<x-layouts.admin title="Category Requests">
    <x-admin.shell eyebrow="Catalog" title="Seller Category Requests" subtitle="Review requested marketplace categories from sellers.">
        <section class="admin-dashboard">
            @if (session('status'))<div class="admin-alert admin-alert--success">{{ session('status') }}</div>@endif
            <section class="admin-table-card">
                @forelse ($requests as $request)
                    <form class="admin-row-link" method="POST" action="{{ route('admin.category-requests.update', $request) }}">
                        @csrf @method('PUT')
                        <span>{{ $request->requested_category_name }} · {{ $request->vendor?->business_name }}</span>
                        <strong>{{ str($request->status)->replace('_', ' ')->title() }}</strong>
                        <small>{{ $request->description }} · Examples: {{ $request->example_products }}</small>
                        <select name="status"><option value="approved">Approve</option><option value="rejected">Reject</option></select>
                        <input name="admin_comment" placeholder="Admin comment" required>
                        <button type="submit">Save Review</button>
                    </form>
                @empty
                    <p>No category requests yet.</p>
                @endforelse
                {{ $requests->links() }}
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
