@php
    $amount = (int) $plan['amount'];
    $cta = match ($plan['key']) {
        'free' => 'Activate Free Store',
        'growth' => 'Pay ₹999 Securely & Activate',
        'enterprise' => 'Pay ₹4,999 Securely & Activate',
        default => 'Activate Store',
    };
@endphp
<x-layouts.seller title="Review Seller Onboarding">
    <x-seller.header title="Review your seller activation" subtitle="Confirm your business details and selected plan before activation." :vendor="$vendor" />
    <section class="seller-content seller-onboarding seller-onboarding--elite">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <div class="seller-stepper seller-stepper--elite">
            @foreach ($steps as $number => $label)
                @php
                    $href = $number === 7 ? route('seller.onboarding.plan') : ($number === 8 ? route('seller.onboarding.review') : route('seller.onboarding', ['step' => $number]));
                @endphp
                <a href="{{ $href }}" @class(['is-active' => $number === 8, 'is-complete' => $number < 8])>
                    <span>{{ $number }}</span><strong>{{ $label }}</strong>
                </a>
            @endforeach
        </div>

        <form class="seller-form-card seller-form-card--elite" method="POST" action="{{ route('seller.onboarding.review.activate') }}">
            @csrf
            <h2>{{ $plan['name'] }} Plan selected</h2>
            <p>{{ $plan['supporting_text'] }}</p>

            <article class="seller-selected-plan-review">
                <div>
                    <span>Amount Due Today</span>
                    <strong>{{ $amount > 0 ? '₹'.number_format($amount) : '₹0' }}</strong>
                    <small>{{ $plan['billing_period'] === 'monthly' ? 'Monthly prepaid plan' : 'No monthly payment' }}</small>
                </div>
                <dl>
                    <div><dt>Plan name</dt><dd>{{ $plan['name'] }}</dd></div>
                    <div><dt>Price</dt><dd>{{ $amount > 0 ? '₹'.number_format($amount).'/month' : '₹0' }}</dd></div>
                    <div><dt>Billing period</dt><dd>{{ str($plan['billing_period'])->title() }}</dd></div>
                    <div><dt>Product limit</dt><dd>{{ $plan['product_limit'] }}</dd></div>
                    <div><dt>Commission rule</dt><dd>{{ $plan['commission'] }}</dd></div>
                    <div><dt>Plan validity</dt><dd>{{ $plan['validity_days'] ? 'One month' : 'No expiry' }}</dd></div>
                    <div><dt>Payment requirement</dt><dd>{{ $plan['is_paid'] ? 'Razorpay payment required' : 'Payment not required' }}</dd></div>
                    <div><dt>Renewal</dt><dd>{{ $plan['is_paid'] ? 'Manual renewal only. No automatic charge.' : 'No paid renewal required' }}</dd></div>
                    <div><dt>Grace period</dt><dd>{{ (int) $plan['grace_days'] > 0 ? $plan['grace_days'].' days' : 'None' }}</dd></div>
                </dl>
            </article>

            <div class="seller-review-grid">
                <article><span>Business</span><strong>{{ $vendor->business_name }}</strong><small>{{ $vendor->owner_name }} · {{ str($vendor->business_type)->replace('_',' ')->title() }}</small></article>
                <article><span>Store</span><strong>{{ $vendor->store_display_name }}</strong><small>{{ $vendor->slug }}</small></article>
                <article><span>GST</span><strong>{{ str($vendor->gst_status ?: 'pending')->replace('_',' ')->title() }}</strong><small>{{ $vendor->gstin ?: 'GSTIN not provided' }}</small></article>
                <article><span>Pickup</span><strong>{{ $vendor->city }}, {{ $vendor->postal_code }}</strong><small>{{ $vendor->pickup_address }}</small></article>
                <article><span>Delivery</span><strong>{{ str(data_get($vendor->delivery_settings, 'method', 'self_delivery'))->replace('_',' ')->title() }}</strong><small>Free within {{ data_get($vendor->delivery_settings, 'free_delivery_radius_km', 0) }} km</small></article>
                <article><span>Bank</span><strong>{{ $vendor->bank_name }}</strong><small>{{ $vendor->maskedBankAccount() }}</small></article>
            </div>

            <label class="seller-checkbox"><input type="checkbox" name="confirm_accuracy" value="1" required> I confirm that the provided information is accurate.</label>
            <label class="seller-checkbox"><input type="checkbox" name="seller_terms" value="1" required> I agree to Sushako seller terms.</label>
            <label class="seller-checkbox"><input type="checkbox" name="commission_rules" value="1" required> I understand the commission and plan rules.</label>
            <label class="seller-checkbox"><input type="checkbox" name="seller_responsibility" value="1" required> I am responsible for product, tax, shipment, and legal compliance.</label>

            <div class="seller-form-actions">
                <a href="{{ route('seller.onboarding.plan') }}">Change Plan</a>
                <button type="submit">{{ $cta }}</button>
            </div>
        </form>
    </section>
</x-layouts.seller>
