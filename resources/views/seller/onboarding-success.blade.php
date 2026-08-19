<x-layouts.seller title="Your Seller Account Is Active">
    <x-seller.header title="Your Seller Account Is Active" subtitle="Your seller account has been activated. Complete your store setup before publishing your storefront." :vendor="$vendor" />

    <section class="seller-content seller-success-page">
        <article class="seller-payment-card seller-payment-card--success">
            <p class="eyebrow">Seller activated</p>
            <h2>Your Seller Account Is Active</h2>
            <p>Your seller account has been activated. Complete your store setup before publishing your storefront.</p>
            <dl>
                <div><dt>Seller name</dt><dd>{{ $vendor->owner_name ?: auth()->user()->name }}</dd></div>
                <div><dt>Business/store</dt><dd>{{ $vendor->store_display_name ?: $vendor->business_name }}</dd></div>
                <div><dt>Selected plan</dt><dd>{{ str($vendor->current_plan ?: $vendor->selected_plan)->title() }}</dd></div>
                <div><dt>Activation date</dt><dd>{{ $vendor->plan_activated_at?->format('d M Y, h:i A') }}</dd></div>
                <div><dt>Plan expiry</dt><dd>{{ $vendor->plan_expires_at?->format('d M Y') ?? 'No expiry' }}</dd></div>
                <div><dt>Account status</dt><dd>{{ str($vendor->status)->replace('_',' ')->title() }}</dd></div>
                <div><dt>Store status</dt><dd>{{ $vendor->store_status === \App\Models\Vendor::STORE_LIVE ? 'Live' : 'Not Live' }}</dd></div>
            </dl>
            <div class="seller-form-actions">
                <a href="{{ route('seller.dashboard') }}">Enter Seller Dashboard</a>
                <a href="{{ route('seller.store-profile') }}">Complete Store Setup</a>
            </div>
        </article>
    </section>
</x-layouts.seller>
