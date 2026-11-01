<x-layouts.seller title="Promote Products">
    <x-seller.header title="Promote Products" subtitle="Sponsor one active product for seven days across the marketplace." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
        <section class="seller-panel"><h2>Weekly Featured Product</h2><p>₹99 · 7 days · one campaign per rolling seven-day period · no automatic renewal.</p><p class="muted">Payment is verified on the server before activation. Products remain subject to marketplace visibility and availability rules.</p></section>
        <div class="seller-card-grid">
            @forelse ($products as $product)
                <article class="seller-panel">
                    <h3>{{ $product->name }}</h3>
                    <p>₹{{ number_format($product->selling_price) }} · {{ $product->totalStock() }} in stock</p>
                    <form method="POST" action="{{ route('seller.promotions.create') }}" data-promotion-form>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit">Pay ₹99 &amp; Promote</button>
                    </form>
                </article>
            @empty
                <x-ui.empty-state title="No eligible products" description="Publish an active product before purchasing a featured campaign." />
            @endforelse
        </div>
        <section class="seller-panel"><h2>Campaign history</h2>
            @forelse ($campaigns as $campaign)
                <div class="seller-key-value"><span>{{ $campaign->product?->name }} · {{ str($campaign->status)->title() }}</span><strong>{{ $campaign->starts_at?->format('d M Y') ?: 'Payment pending' }}{{ $campaign->ends_at ? ' – '.$campaign->ends_at->format('d M Y') : '' }}</strong></div>
            @empty <p class="muted">No campaigns yet.</p> @endforelse
            {{ $campaigns->links() }}
        </section>
    </section>
</x-layouts.seller>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.querySelectorAll('[data-promotion-form]').forEach((form) => form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('button');
    button.disabled = true;
    try {
        const response = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: new FormData(form) });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Unable to start promotion payment.');
        const checkout = new Razorpay({ key: data.key, amount: data.amount, currency: data.currency, name: 'Sushako Shopping', description: 'Weekly Featured Product', order_id: data.order_id, handler: async (payment) => {
            const verify = new FormData();
            Object.entries(payment).forEach(([key, value]) => verify.append(key, value));
            const result = await fetch(`/seller/promote-products/${data.campaign_id}/verify`, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: verify });
            if (!result.ok) throw new Error('Payment was received but verification is still pending.');
            window.location.reload();
        }, modal: { ondismiss: () => { button.disabled = false; } } });
        checkout.on('payment.failed', () => { button.disabled = false; });
        checkout.open();
    } catch (error) { button.disabled = false; window.alert(error.message); }
}));
</script>
