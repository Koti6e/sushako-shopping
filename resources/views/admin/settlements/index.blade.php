<x-layouts.admin title="Settlements & Reconciliation">
    <x-admin.shell eyebrow="Finance" title="Settlements & Reconciliation" subtitle="Manual settlement batching with immutable fee snapshots.">
        <section class="admin-dashboard">
            @if (session('status'))<div class="admin-alert admin-alert--success">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="admin-alert admin-alert--danger">{{ $errors->first() }}</div>@endif
            <div class="admin-metric-grid">
                @foreach ($summary as $label => $value)
                    <article class="admin-metric-card"><span>{{ str($label)->replace('_', ' ')->title() }}</span><strong>Rs {{ number_format($value) }}</strong></article>
                @endforeach
            </div>
            <form class="admin-filter-card" method="GET" action="{{ route('admin.settlements.preview') }}">
                <label>Seller<select name="vendor_id" required><option value="">Select seller</option>@foreach ($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->business_name ?: $vendor->store_display_name }}</option>@endforeach</select></label>
                <label>Start Date<input type="date" name="start_date"></label>
                <label>End Date<input type="date" name="end_date"></label>
                <button type="submit">Preview Settlement</button>
            </form>
            <form class="admin-filter-card" method="GET">
                <label>Seller<select name="seller"><option value="">All sellers</option>@foreach ($vendors as $vendor)<option value="{{ $vendor->id }}" @selected(request('seller') == $vendor->id)>{{ $vendor->business_name ?: $vendor->store_display_name }}</option>@endforeach</select></label>
                <label>Status<input name="status" value="{{ request('status') }}"></label>
                <button type="submit">Filter</button>
                <a href="{{ route('admin.settlements.index') }}">Reset</a>
            </form>
            <section class="admin-table-card">
                <h2>Settlement Records</h2>
                @forelse ($settlements as $settlement)
                    <a class="admin-row-link" href="{{ route('admin.settlements.show', $settlement) }}">
                        <span>{{ $settlement->settlement_number }} · {{ $settlement->vendor?->business_name }}</span>
                        <strong>{{ str($settlement->status)->replace('_', ' ')->title() }} · Gross Rs {{ number_format((float) $settlement->gross_amount) }} · Fee Rs {{ number_format((float) $settlement->platform_fee_amount) }} · Payable Rs {{ number_format((float) $settlement->final_settlement_amount) }}</strong>
                        <small>Expected {{ $settlement->expected_settlement_date?->format('d M Y') ?: 'Pending' }} · Ref {{ $settlement->payment_reference ?: 'Manual Settlement' }}</small>
                    </a>
                @empty
                    <p>No settlement records yet.</p>
                @endforelse
                {{ $settlements->links() }}
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
