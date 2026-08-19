<x-layouts.seller title="Settlement Details">
    <x-seller.header title="{{ $settlement->settlement_number }}" subtitle="Read-only settlement statement with item-level platform fee calculations." :vendor="$vendor" />
    <section class="seller-content">
        <div class="seller-card-grid">
            @foreach (['gross_amount' => 'Gross Sales', 'eligible_quantity' => 'Products Sold', 'platform_fee_amount' => 'Platform Fee', 'refund_amount' => 'Refunds', 'return_amount' => 'Returns', 'adjustment_amount' => 'Adjustments', 'final_settlement_amount' => 'Final Settlement'] as $key => $label)
                <article class="seller-metric-card"><span>{{ $label }}</span><strong>{{ $key === 'eligible_quantity' ? number_format($settlement->{$key}) : 'Rs '.number_format((float) $settlement->{$key}) }}</strong></article>
            @endforeach
        </div>
        <section class="seller-panel">
            <h2>Payment Reference</h2>
            <div class="seller-key-value"><span>Status</span><strong>{{ str($settlement->status)->replace('_', ' ')->title() }}</strong></div>
            <div class="seller-key-value"><span>Paid Date</span><strong>{{ $settlement->paid_at?->format('d M Y') ?: 'Not paid' }}</strong></div>
            <div class="seller-key-value"><span>UTR / Reference</span><strong>{{ $settlement->payment_reference ?: 'Pending' }}</strong></div>
            <div class="seller-key-value"><span>Hold / Failure Reason</span><strong>{{ $settlement->hold_reason ?: $settlement->failure_reason ?: 'None' }}</strong></div>
            <a href="{{ route('seller.settlements.statement', $settlement) }}">Download statement</a>
        </section>
        <section class="seller-table-card">
            <h2>Included Orders</h2>
            @foreach ($settlement->items as $item)
                <a class="seller-row-link" href="{{ route('seller.orders.show', $item->orderItem?->order?->order_number) }}">
                    <span>{{ $item->orderItem?->order?->order_number }} · {{ $item->orderItem?->product_name }}</span>
                    <strong>Qty {{ $item->eligible_quantity }} · Gross Rs {{ number_format((float) $item->gross_amount) }} · Platform Fee Rs {{ number_format((float) $item->platform_fee_total) }} · Final Rs {{ number_format((float) $item->final_amount) }}</strong>
                </a>
            @endforeach
        </section>
    </section>
</x-layouts.seller>
