@php
    use App\Models\Vendor;

    $currentPlan = $vendor->current_plan ?: $vendor->selected_plan_slug ?: $vendor->selected_plan ?: Vendor::PLAN_FREE;
    $pendingPlan = in_array($vendor->payment_status, [Vendor::PAYMENT_PENDING, Vendor::PAYMENT_FAILED], true) ? ($vendor->selected_plan_slug ?: $vendor->selected_plan) : null;
    $remainingDays = $vendor->plan_expires_at ? max(0, now()->startOfDay()->diffInDays($vendor->plan_expires_at->copy()->startOfDay(), false)) : null;
    $brandingByPlan = [
        Vendor::PLAN_FREE => ['label' => 'Sushako Branding', 'feature' => 'Sushako-branded labels', 'note' => 'Sushako platform branding remains visible on supported seller and customer commerce surfaces.'],
        Vendor::PLAN_GROWTH => ['label' => 'Sushako Labelling', 'feature' => 'Sushako labelling', 'note' => 'Growth keeps Sushako platform labelling and does not include own-brand labels.'],
        Vendor::PLAN_ENTERPRISE => ['label' => 'Own Branding Label', 'feature' => 'Own branding labels', 'note' => 'Enterprise can use the seller business branding on supported labels.'],
    ];
    $gatewayNotice = 'Payment gateway charges (Razorpay) apply separately to online transactions on all plans.';
@endphp
<x-layouts.seller title="Subscription">
    <x-seller.header title="Subscription" subtitle="Select a seller plan, manage renewals, and continue payment from one workspace." :vendor="$vendor" />
    <section class="seller-content seller-subscription-page">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif

        <section class="seller-subscription-overview" aria-label="Current subscription summary">
            <article class="seller-subscription-current">
                <div>
                    <p class="eyebrow">Current Plan</p>
                    <h2>{{ str($currentPlan)->replace('_', ' ')->title() }}</h2>
                    <p>{{ $vendor->plan_status === Vendor::PLAN_ACTIVE ? 'Your seller benefits are active.' : 'Choose a paid plan below to activate premium selling benefits.' }}</p>
                </div>
                <dl>
                    <div><dt>Status</dt><dd>{{ str($vendor->plan_status ?: 'active')->replace('_', ' ')->title() }}</dd></div>
                    <div><dt>Remaining</dt><dd>{{ is_null($remainingDays) ? 'Unlimited' : $remainingDays.' days' }}</dd></div>
                    <div><dt>Activated</dt><dd>{{ $vendor->plan_activated_at?->format('d M Y') ?? 'Not active' }}</dd></div>
                    <div><dt>Expiry</dt><dd>{{ $vendor->plan_expires_at?->format('d M Y') ?? 'Unlimited' }}</dd></div>
                </dl>
            </article>

            <aside class="seller-subscription-note" aria-label="Payment note">
                <span>Secure Payment</span>
                <strong>Razorpay checkout</strong>
                <p>Paid plans are created as pending payment orders and activated only after verified payment confirmation.</p>
                <p>{{ $gatewayNotice }} Applicable gateway charges may also affect online refunds as disclosed during refund requests.</p>
            </aside>
        </section>

        <section class="seller-plan-options" aria-label="Seller plan options">
            @foreach ($plans as $slug => $plan)
                @php
                    $isCurrent = $currentPlan === $slug && $vendor->plan_status === Vendor::PLAN_ACTIVE;
                    $isPending = $pendingPlan === $slug;
                    $isPaid = (bool) ($plan['is_paid'] ?? ((int) $plan['amount'] > 0));
                    $priceText = $isPaid ? 'Rs '.number_format((int) $plan['amount']) : 'Free';
                    $billingText = $isPaid ? '30 days validity' : 'Unlimited validity';
                    $productLimit = $plan['product_limit'] ?? 'Unlimited';
                    $orderLimit = $plan['order_limit'] ?? 'Unlimited';
                    $ctaText = $isPending ? 'Continue Payment' : ($isCurrent ? 'Renew Plan' : 'Select & Pay');
                    $branding = $brandingByPlan[$slug] ?? $brandingByPlan[Vendor::PLAN_FREE];
                @endphp

                <article @class(['seller-plan-option-card', 'is-current' => $isCurrent, 'is-pending' => $isPending, 'is-featured' => $slug === Vendor::PLAN_GROWTH])>
                    <header>
                        <div>
                            <span>{{ $isCurrent ? 'Active plan' : ($isPending ? 'Payment pending' : ($slug === Vendor::PLAN_GROWTH ? 'Recommended' : 'Seller plan')) }}</span>
                            <h2>{{ $plan['name'] }}</h2>
                        </div>
                        <strong>{{ $priceText }}</strong>
                    </header>

                    <p>{{ $plan['supporting_text'] ?? 'Professional seller access for Sushako Shopping.' }}</p>

                    <dl>
                        <div><dt>Billing</dt><dd>{{ $billingText }}</dd></div>
                        <div><dt>Products</dt><dd>{{ is_numeric($productLimit) ? number_format((int) $productLimit) : $productLimit }}</dd></div>
                        <div><dt>Orders</dt><dd>{{ $orderLimit }}</dd></div>
                        <div><dt>Commission</dt><dd>{{ $plan['commission'] ?? 'Seller commission policy applies' }}</dd></div>
                        <div><dt>Branding</dt><dd>{{ $branding['label'] }}</dd></div>
                        <div><dt>Gateway charges</dt><dd>Razorpay applicable</dd></div>
                    </dl>

                    <ul>
                        <li>{{ $branding['feature'] }}</li>
                        @foreach (array_slice($plan['features'] ?? [], 0, 6) as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    <footer>
                        <p class="seller-plan-branding-note">{{ $branding['note'] }}</p>
                        <p class="seller-plan-gateway-note">{{ $gatewayNotice }}</p>
                        @if ($isPaid)
                            <form method="POST" action="{{ route('seller.plans.renew') }}">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $slug }}">
                                <button type="submit">{{ $ctaText }}</button>
                            </form>
                        @else
                            <button type="button" disabled>{{ $isCurrent ? 'Current Free Plan' : 'No Payment Required' }}</button>
                        @endif
                    </footer>
                </article>
            @endforeach
        </section>

        <section class="seller-panel seller-subscription-history">
            <div class="seller-section-heading">
                <div>
                    <p class="eyebrow">Billing Ledger</p>
                    <h2>Payment History</h2>
                </div>
            </div>
            @forelse ($payments as $payment)
                <div class="seller-row-link">
                    <span>{{ str($payment->plan)->title() }} · {{ $payment->activated_at?->format('d M Y') ?? $payment->created_at?->format('d M Y') }}</span>
                    <strong>Rs {{ number_format((int) $payment->amount) }} · {{ str($payment->status)->title() }}</strong>
                </div>
            @empty
                <p>No previous seller plan payments yet.</p>
            @endforelse

            {{ $payments->links() }}
        </section>
    </section>
</x-layouts.seller>
