<x-layouts.seller title="Earnings">
    <x-seller.header title="Earnings" subtitle="Gross sales, product units, platform fees, deductions, and net seller earnings." :vendor="$vendor" />
    <section class="seller-content">
        <form class="seller-form-card" method="GET">
            <label>Period<select name="period"><option value="today" @selected(($filters['period'] ?? '') === 'today')>Today</option><option value="week" @selected(($filters['period'] ?? '') === 'week')>This Week</option><option value="month" @selected(($filters['period'] ?? 'month') === 'month')>This Month</option><option value="custom" @selected(($filters['period'] ?? '') === 'custom')>Custom</option><option value="all" @selected(($filters['period'] ?? '') === 'all')>All</option></select></label>
            <label>Start Date<input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"></label>
            <label>End Date<input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"></label>
            <label>Settlement Status<input name="settlement_status" value="{{ $filters['settlement_status'] ?? '' }}"></label>
            <label>Order Status<input name="order_status" value="{{ $filters['order_status'] ?? '' }}"></label>
            <div class="seller-form-actions"><a href="{{ route('seller.earnings.index') }}">Reset</a><button type="submit">Apply Filters</button></div>
        </form>

        <div class="seller-card-grid">
            @foreach ($summary as $label => $value)
                <article class="seller-metric-card"><span>{{ str($label)->replace('_', ' ')->title() }}</span><strong>{{ str_contains($label, 'sold') ? number_format($value) : 'Rs '.number_format($value) }}</strong></article>
            @endforeach
        </div>

        @if (($vendor->current_plan ?: 'free') === 'free')
            <section class="seller-panel">
                <h2>Platform Fee: Rs 1 per product sold</h2>
                <div class="seller-key-value"><span>Products Sold</span><strong>{{ number_format($summary['products_sold']) }}</strong></div>
                <div class="seller-key-value"><span>Platform Fee Rate</span><strong>Rs 1 per product</strong></div>
                <div class="seller-key-value"><span>Sushako Platform Fee</span><strong>Rs {{ number_format($summary['platform_fees']) }}</strong></div>
                <div class="seller-key-value"><span>Your Net Earnings</span><strong>Rs {{ number_format($summary['net_earnings']) }}</strong></div>
            </section>
        @endif

        <section class="seller-table-card">
            <h2>Transactions</h2>
            @forelse ($items as $item)
                <a class="seller-row-link" href="{{ route('seller.orders.show', $item->order?->order_number) }}">
                    <span>{{ $item->order?->created_at?->format('d M Y') }} · {{ $item->order?->order_number }} · {{ $item->order?->customer_name }} · {{ $item->product_name }}</span>
                    <strong>Qty {{ $item->eligible_quantity ?: $item->quantity }} · Gross Rs {{ number_format((float) $item->gross_line_amount) }} · Platform Fee Rs {{ number_format((float) $item->platform_fee_total) }} · Net Rs {{ number_format((float) $item->seller_earning) }}</strong>
                    <small>{{ str($item->settlement_status)->replace('_', ' ')->title() }}</small>
                </a>
            @empty
                <p>No earning transactions for the selected filters.</p>
            @endforelse
            {{ $items->links() }}
        </section>
    </section>
</x-layouts.seller>
