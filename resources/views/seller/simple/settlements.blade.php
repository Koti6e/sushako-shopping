<x-layouts.seller title="Settlements">
    <x-seller.header title="Settlements" subtitle="Manage bank details, settlement preferences, and payout enquiries." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <form class="seller-form-card seller-form-card--sectioned" method="POST" action="{{ route('seller.settlements.update') }}">
            @csrf
            @method('PUT')
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
            <label class="seller-field--wide">Settlement enquiry<textarea name="settlement_enquiry" placeholder="Ask about payout schedule, statement mismatch, bank verification, or settlement hold.">{{ old('settlement_enquiry', $vendor->pending_settlement_notice) }}</textarea></label>
            <div class="seller-form-actions"><button type="submit">Save Settlement Details</button></div>
        </form>

        <div class="seller-dashboard-grid">
            <section class="seller-panel">
                <h2>Settlement Statements</h2>
                @forelse ($settlements as $settlement)
                    <div class="seller-row-link"><span>{{ $settlement->settlement_number }}</span><strong>{{ str($settlement->status)->title() }} · Rs {{ number_format($settlement->net_payable) }}</strong><small>Expected {{ $settlement->expected_settlement_date?->format('d M Y') }}</small></div>
                @empty
                    <p>No settlement statements yet.</p>
                @endforelse
            </section>
            <section class="seller-panel">
                <h2>Upcoming Payables</h2>
                @forelse ($items as $item)
                    <div class="seller-row-link"><span>{{ $item->order?->order_number }} · {{ $item->product_name }}</span><strong>Rs {{ number_format((int) $item->seller_earning) }}</strong></div>
                @empty
                    <p>No upcoming settlement items.</p>
                @endforelse
            </section>
        </div>
    </section>
</x-layouts.seller>
