<x-layouts.seller title="Seller Account Status">
    <x-seller.header title="Seller Account Status" subtitle="Your seller dashboard access depends on the current account review state." :vendor="$vendor" />
    <section class="seller-content seller-success-page">
        <article class="seller-payment-card">
            <p class="eyebrow">Account access paused</p>
            <h2>{{ str($vendor->status)->replace('_',' ')->title() }}</h2>
            <p>{{ $vendor->rejection_reason ?: 'This seller account is not currently active. Contact Sushako support if you believe this needs review.' }}</p>
            <dl>
                <div><dt>Store</dt><dd>{{ $vendor->store_display_name ?: $vendor->business_name }}</dd></div>
                <div><dt>Onboarding</dt><dd>{{ str($vendor->onboarding_status)->title() }}</dd></div>
                <div><dt>Plan</dt><dd>{{ str($vendor->current_plan ?: 'not selected')->title() }}</dd></div>
                <div><dt>Payment</dt><dd>{{ str($vendor->payment_status ?: 'not started')->replace('_',' ')->title() }}</dd></div>
            </dl>
            <div class="seller-form-actions">
                <a href="mailto:{{ config('mail.from.address') }}">Contact Support</a>
                <form method="POST" action="{{ route('seller.logout') }}">@csrf<button type="submit">Logout</button></form>
            </div>
        </article>
    </section>
</x-layouts.seller>
