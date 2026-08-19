@php
    $title = str($module)->replace('_', ' ')->title();
    $cards = match ($module) {
        'reports' => ['Today\'s Sales' => 'Rs 0', 'Weekly Sales' => 'Rs 0', 'Monthly Sales' => 'Rs 0', 'Visitors' => '0', 'Product Views' => '0', 'Conversion %' => '0%'],
        'support' => ['Support Tickets' => '0 Open', 'Announcements' => 'No new updates', 'FAQs' => 'Available', 'Contact Support' => config('mail.from.address')],
        'marketing' => ['Campaigns' => 'Coming soon', 'Top Categories' => 'No data', 'Best Selling Products' => 'No data'],
        'labels' => ['Self Shipping' => 'Enabled', 'Label Format' => 'Sushako Standard', 'KYC Verification' => 'Reserved'],
        default => ['Current Plan' => str($vendor->current_plan ?: 'free')->title(), 'Store Status' => str($vendor->store_visibility ?: 'draft')->title(), 'Approval Status' => str($vendor->approval_status ?: 'registered')->replace('_', ' ')->title()],
    };
@endphp
<x-layouts.seller title="{{ $title }}">
    <x-seller.header :title="$title" subtitle="Professional seller tools scoped to your store." :vendor="$vendor" />
    <section class="seller-content">
        @if ($module === 'shipping_policy')
            <section class="seller-panel"><h2>Sushako Return Policy</h2><p>Return and refund rules are controlled by Sushako and displayed read-only for sellers.</p></section>
        @endif
        <section class="seller-card-grid">
            @foreach ($cards as $label => $value)
                <article class="seller-metric-card"><span>{{ $label }}</span><strong>{{ $value }}</strong></article>
            @endforeach
        </section>
        @if ($module === 'support')
            <section class="seller-panel"><h2>Contact Sushako Support</h2><p>Use this center for seller tickets, announcements, FAQs, and support contact. Ticket workflows are ready for the next service integration.</p></section>
        @elseif ($module === 'reports')
            <section class="seller-panel"><h2>Revenue Graph</h2><p>Analytics cards are connected to seller-safe metrics. Visitor and graph integrations are reserved for the analytics collector.</p></section>
        @else
            <section class="seller-panel"><h2>{{ $title }}</h2><p>This section is prepared for future expansion without exposing customer/admin data outside your seller scope.</p></section>
        @endif
    </section>
</x-layouts.seller>
