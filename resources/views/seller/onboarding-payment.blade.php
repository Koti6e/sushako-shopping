<x-layouts.seller title="Activate Your Seller Account">
    <x-seller.header title="Activate Your Seller Account" subtitle="Complete the secure payment to activate your selected seller plan and access your dashboard." :vendor="$vendor" />

    <section class="seller-content seller-payment-page">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <article class="seller-payment-card">
            <p class="eyebrow">Razorpay secured payment</p>
            <h2>Activate Your Seller Account</h2>
            <p>Complete the secure payment to activate your selected seller plan and access your dashboard.</p>
            <dl>
                <div><dt>Selected plan</dt><dd>{{ $plan['name'] }}</dd></div>
                <div><dt>Amount</dt><dd>₹{{ number_format((int) $payment->amount) }}</dd></div>
                <div><dt>Billing</dt><dd>{{ str($plan['billing_period'])->replace('_', ' ')->title() }}</dd></div>
                <div><dt>Product limit</dt><dd>{{ is_numeric($plan['product_limit']) ? number_format((int) $plan['product_limit']) : $plan['product_limit'] }}</dd></div>
                <div><dt>Commission</dt><dd>{{ $plan['commission'] }}</dd></div>
                <div><dt>Validity</dt><dd>{{ (int) ($plan['validity_days'] ?? 30) }} days</dd></div>
                <div><dt>Grace</dt><dd>{{ (int) ($plan['grace_days'] ?? 0) > 0 ? $plan['grace_days'].' days' : 'No grace period' }}</dd></div>
                <div><dt>Order ID</dt><dd>{{ $payment->provider_order_id }}</dd></div>
                <div><dt>Status</dt><dd>{{ str($payment->status)->title() }}</dd></div>
            </dl>

            @if ($payment->status === 'failed')
                <div class="status-banner status-banner--error">Your payment could not be completed. Your onboarding details and selected plan are safely saved.</div>
            @elseif ($payment->status === 'pending')
                <div class="status-banner">Payment verification is pending until Razorpay confirms capture.</div>
            @endif

            <div class="seller-form-actions">
                <button type="button" id="seller-rzp-button" @disabled(! $razorpayConfigured)>Pay Securely & Activate</button>
                <form method="POST" action="{{ route('seller.onboarding.payment.retry') }}">@csrf<button type="submit">Retry Payment</button></form>
                <a href="{{ route('seller.onboarding', ['change_plan' => 1]) }}">Change Plan</a>
                <a href="{{ route('seller.onboarding', ['change_plan' => 1]) }}">Switch to Free</a>
            </div>

            @unless ($razorpayConfigured)
                <p class="seller-note">Razorpay live keys are not configured. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET before accepting paid seller plans.</p>
            @endunless
        </article>
    </section>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const sellerPayButton = document.getElementById('seller-rzp-button');
        if (sellerPayButton && window.Razorpay) {
            const checkout = new Razorpay({
                key: @json(config('services.razorpay.key')),
                amount: @json((int) $payment->amount * 100),
                currency: @json($payment->currency),
                name: 'Sushako Shopping',
                description: @json('Sushako '.$plan['name'].' Seller Plan - 1 Month'),
                order_id: @json($payment->provider_order_id),
                prefill: { name: @json($vendor->owner_name ?: auth()->user()->name), email: @json($vendor->email), contact: @json($vendor->phone) },
                handler: async (response) => {
                    const result = await fetch(@json(route('seller.onboarding.payment.confirm')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
                        body: JSON.stringify(Object.assign({ payment_id: @json($payment->id) }, response))
                    });
                    const data = await result.json();
                    window.location.href = data.redirect_url || @json(route('seller.onboarding.payment'));
                }
            });
            sellerPayButton.addEventListener('click', () => checkout.open());
            checkout.on('payment.failed', () => window.location.href = @json(route('seller.onboarding.payment')));
        }
    </script>
</x-layouts.seller>
