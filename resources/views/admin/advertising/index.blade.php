<x-layouts.admin title="Advertising">
    <x-admin.shell eyebrow="Revenue operations" title="Featured product advertising" subtitle="Review seller campaigns, payments, placements and performance.">
        <section class="admin-control-page">
            <div class="admin-control-metrics">
                <article><span>Revenue</span><strong>₹{{ number_format($campaigns->getCollection()->where('payment_status', 'paid')->sum('amount')) }}</strong></article>
                <article><span>Active campaigns</span><strong>{{ $campaigns->getCollection()->where('status', 'active')->count() }}</strong></article>
                <article><span>Impressions</span><strong>{{ number_format($campaigns->getCollection()->sum('impressions')) }}</strong></article>
                <article><span>Clicks</span><strong>{{ number_format($campaigns->getCollection()->sum('clicks')) }}</strong></article>
            </div>
            <div class="admin-enterprise-card admin-import-monitor__table-wrap"><table><thead><tr><th>Seller</th><th>Product</th><th>Payment</th><th>Campaign</th><th>Performance</th><th>Action</th></tr></thead><tbody>
                @forelse ($campaigns as $campaign)<tr><td>{{ $campaign->vendor?->store_display_name ?: $campaign->vendor?->business_name }}</td><td>{{ $campaign->product?->name }}</td><td>{{ str($campaign->payment_status)->title() }}<br><small>{{ $campaign->razorpay_payment_id }}</small></td><td>{{ str($campaign->status)->title() }}<br><small>{{ $campaign->ends_at?->format('d M Y H:i') }}</small></td><td>{{ number_format($campaign->impressions) }} / {{ number_format($campaign->clicks) }}</td><td><form method="POST" action="{{ route('admin.advertising.status.update', $campaign) }}">@csrf @method('PATCH')<select name="status"><option value="active">Active</option><option value="paused">Paused</option><option value="rejected">Rejected</option></select><button>Save</button></form></td></tr>@empty<tr><td colspan="6">No advertising campaigns yet.</td></tr>@endforelse
            </tbody></table></div>{{ $campaigns->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
