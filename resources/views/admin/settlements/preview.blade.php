<x-layouts.admin title="Settlement Preview">
    <x-admin.shell eyebrow="Finance" title="Settlement Preview" subtitle="{{ $vendor->business_name ?: $vendor->store_display_name }}">
        <section class="admin-dashboard">
            <div class="admin-metric-grid">
                @foreach ($summary as $label => $value)
                    @if ($label !== 'items_count')
                        <article class="admin-metric-card"><span>{{ str($label)->replace('_', ' ')->title() }}</span><strong>{{ str_contains($label, 'quantity') ? number_format($value) : 'Rs '.number_format($value) }}</strong></article>
                    @endif
                @endforeach
            </div>
            <form class="admin-filter-card" method="POST" action="{{ route('admin.settlements.generate') }}">
                @csrf
                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                <button type="submit">Generate Settlement Batch</button>
                <a href="{{ route('admin.settlements.index') }}">Back</a>
            </form>
            <section class="admin-table-card">
                <h2>Eligible Item-Level Calculations</h2>
                @forelse ($items as $item)
                    <div class="admin-row-link">
                        <span>{{ $item->order?->order_number }} · {{ $item->product_name }}</span>
                        <strong>Qty {{ $item->eligible_quantity ?: $item->quantity }} · Gross Rs {{ number_format((float) $item->gross_line_amount) }} · Platform Fee Rs {{ number_format((float) $item->platform_fee_total) }} · Seller Rs {{ number_format((float) $item->seller_earning) }}</strong>
                    </div>
                @empty
                    <p>No eligible delivered order items for this period.</p>
                @endforelse
                {{ $items->links() }}
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
