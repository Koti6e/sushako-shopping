<x-layouts.seller title="Settlements">
    <x-seller.header title="Settlements" subtitle="Settlement history, deductions, bank details, and payment references." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <div class="seller-card-grid">
            @foreach ($summary as $label => $value)
                <article class="seller-metric-card"><span>{{ str($label)->replace('_', ' ')->title() }}</span><strong>Rs {{ number_format($value) }}</strong></article>
            @endforeach
        </div>

        <form class="seller-form-card seller-form-card--sectioned" method="POST" action="{{ route('seller.settlements.update') }}">
            @csrf @method('PUT')
            <h2>Bank and Settlement Details</h2>
            <label>Account holder name<input name="bank_account_holder_name" value="{{ old('bank_account_holder_name', $vendor->bank_account_holder_name) }}" required></label>
            <label>Bank name<input name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" required></label>
            <label>Account number<input name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="{{ $vendor->maskedBankAccount() }}" required></label>
            <label>Confirm account number<input name="bank_account_number_confirmation" value="{{ old('bank_account_number_confirmation') }}" required></label>
            <label>IFSC<input name="bank_ifsc" value="{{ old('bank_ifsc') }}" required></label>
            <label>Branch<input name="bank_branch_name" value="{{ old('bank_branch_name', $vendor->bank_branch_name) }}"></label>
            <label>Account type<select name="bank_account_type"><option value="">Select type</option><option value="savings" @selected(old('bank_account_type', $vendor->bank_account_type) === 'savings')>Savings</option><option value="current" @selected(old('bank_account_type', $vendor->bank_account_type) === 'current')>Current</option></select></label>
            <label>UPI ID<input name="bank_upi_id" value="{{ old('bank_upi_id') }}"></label>
            <label>Settlement cycle<select name="settlement_cycle" required><option value="weekly" @selected(old('settlement_cycle', $vendor->settlement_cycle) === 'weekly')>Weekly</option><option value="biweekly" @selected(old('settlement_cycle', $vendor->settlement_cycle) === 'biweekly')>Biweekly</option><option value="monthly" @selected(old('settlement_cycle', $vendor->settlement_cycle) === 'monthly')>Monthly</option></select></label>
            <label>Minimum settlement amount<input type="number" step="0.01" name="minimum_settlement_amount" value="{{ old('minimum_settlement_amount', $vendor->minimum_settlement_amount) }}"></label>
            <label class="seller-field--wide">Settlement enquiry<textarea name="settlement_enquiry">{{ old('settlement_enquiry', $vendor->pending_settlement_notice) }}</textarea></label>
            <div class="seller-form-actions"><button type="submit">Save Settlement Details</button></div>
        </form>

        <section class="seller-table-card">
            <h2>Upcoming Earnings</h2>
            @if ($upcomingItems->isNotEmpty())
                <table class="seller-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Gross</th>
                            <th>Fee</th>
                            <th>Seller earning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingItems as $item)
                            <tr>
                                <td>{{ $item->order?->order_number ?? 'Pending' }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rs {{ number_format((float) ($item->gross_line_amount ?: $item->line_total)) }}</td>
                                <td>Rs {{ number_format((float) $item->platform_fee_total) }}</td>
                                <td>Rs {{ number_format((float) $item->seller_earning) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No upcoming earnings yet.</p>
            @endif
        </section>

        <section class="seller-table-card">
            <h2>Settlement Statements</h2>
            @forelse ($settlements as $settlement)
                <a class="seller-row-link" href="{{ route('seller.settlements.show', $settlement) }}">
                    <span>{{ $settlement->settlement_number }} · {{ $settlement->settlement_period_start?->format('d M Y') ?? $settlement->period_start?->format('d M Y') }} - {{ $settlement->settlement_period_end?->format('d M Y') ?? $settlement->period_end?->format('d M Y') }}</span>
                    <strong>{{ str($settlement->status)->replace('_', ' ')->title() }} · Rs {{ number_format((float) ($settlement->final_settlement_amount ?: $settlement->net_payable)) }}</strong>
                    <small>Ref {{ $settlement->payment_reference ?: 'Not paid yet' }} · Expected {{ $settlement->expected_settlement_date?->format('d M Y') ?: 'Pending' }}</small>
                </a>
            @empty
                <p>No settlement statements yet.</p>
            @endforelse
            {{ $settlements->links() }}
        </section>
    </section>
</x-layouts.seller>
