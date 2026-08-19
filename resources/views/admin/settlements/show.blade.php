<x-layouts.admin title="Settlement Details">
    <x-admin.shell eyebrow="Finance" title="{{ $settlement->settlement_number }}" subtitle="{{ $settlement->vendor?->business_name }}">
        <section class="admin-dashboard">
            @if (session('status'))<div class="admin-alert admin-alert--success">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="admin-alert admin-alert--danger">{{ $errors->first() }}</div>@endif
            <div class="admin-metric-grid">
                @foreach (['gross_amount' => 'Gross Sales', 'eligible_quantity' => 'Products Sold', 'platform_fee_amount' => 'Platform Fee', 'refund_amount' => 'Refunds', 'return_amount' => 'Returns', 'adjustment_amount' => 'Adjustments', 'final_settlement_amount' => 'Final Settlement'] as $key => $label)
                    <article class="admin-metric-card"><span>{{ $label }}</span><strong>{{ $key === 'eligible_quantity' ? number_format($settlement->{$key}) : 'Rs '.number_format((float) $settlement->{$key}) }}</strong></article>
                @endforeach
            </div>
            <form class="admin-filter-card" method="POST" action="{{ route('admin.settlements.status.update', $settlement) }}">
                @csrf @method('PUT')
                <label>Status<select name="status"><option value="approved">Approve</option><option value="processing">Processing</option><option value="on_hold">On Hold</option><option value="failed">Failed</option><option value="cancelled">Cancelled</option></select></label>
                <label>Reason<input name="reason" placeholder="Required for hold, failed or cancelled"></label>
                <button type="submit" @disabled($settlement->status === 'paid')>Update Status</button>
            </form>
            <form class="admin-filter-card" method="POST" action="{{ route('admin.settlements.manual-payment.store', $settlement) }}">
                @csrf
                <label>Payment Date<input type="date" name="payment_date" value="{{ now()->toDateString() }}" required></label>
                <label>UTR / Bank Reference<input name="payment_reference" required></label>
                <label>Payment Mode<input name="payment_mode" value="Manual Bank Transfer" required></label>
                <label>Admin Note<input name="admin_note"></label>
                <button type="submit" @disabled($settlement->status === 'paid')>Mark Paid</button>
            </form>
            <form class="admin-filter-card" method="POST" action="{{ route('admin.settlements.adjustments.store', $settlement) }}">
                @csrf
                <label>Adjustment Type<input name="adjustment_type" required></label>
                <label>Amount<input type="number" step="0.01" name="amount" required></label>
                <label>Reason<input name="reason" required></label>
                <button type="submit" @disabled($settlement->status === 'paid')>Add Manual Adjustment</button>
            </form>
            <section class="admin-table-card">
                <h2>Included Orders</h2>
                @foreach ($settlement->items as $item)
                    <div class="admin-row-link"><span>{{ $item->orderItem?->order?->order_number }} · {{ $item->orderItem?->product_name }}</span><strong>Qty {{ $item->eligible_quantity }} · Gross Rs {{ number_format((float) $item->gross_amount) }} · Platform Fee Rs {{ number_format((float) $item->platform_fee_total) }} · Final Rs {{ number_format((float) $item->final_amount) }}</strong></div>
                @endforeach
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
