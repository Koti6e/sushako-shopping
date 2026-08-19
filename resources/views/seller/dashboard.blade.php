@php
    $bankPending = blank($vendor->bank_account_number);
    $hasNoProducts = $vendor->products()->doesntExist();
@endphp

<x-layouts.seller title="Seller Dashboard">
    <x-seller.header title="Dashboard" :vendor="$vendor" />

    <section class="seller-content seller-dashboard seller-dashboard--simple">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif

        @if ($hasNoProducts)
            <article class="seller-bank-reminder seller-first-product-callout">
                <div>
                    <p class="eyebrow">My Shop</p>
                    <h2>Welcome to {{ $vendor->business_name }}</h2>
                    <p>Your shop is ready. Let's add your first product.</p>
                </div>
                <div class="seller-callout-actions">
                    <a href="{{ route('seller.products.create') }}">+ Add Your First Product</a>
                    <a href="{{ route('stores.show', $vendor->slug) }}" target="_blank" rel="noreferrer">View My Store ↗</a>
                </div>
            </article>
        @endif

        @if ($bankPending)
            <article class="seller-bank-reminder">
                <div>
                    <p class="eyebrow">Settlements</p>
                    <h2>Add your bank details to receive settlements.</h2>
                    <p>You can keep listing products and managing orders. Bank details are needed before payout.</p>
                </div>
                <a href="{{ route('seller.settlements.index') }}">Add Bank Details</a>
            </article>
        @endif

        <div class="seller-card-grid seller-card-grid--dashboard">
            @foreach ($cards as $card)
                <a href="{{ $card['url'] }}" class="seller-metric-card">
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                </a>
            @endforeach
        </div>

        <section class="seller-dashboard-actions" aria-labelledby="seller-dashboard-actions-title">
            <div class="seller-section-heading">
                <div>
                    <p class="eyebrow">Quick Actions</p>
                    <h2 id="seller-dashboard-actions-title">Daily Work</h2>
                </div>
            </div>
            <div class="seller-dashboard-actions__grid">
                @foreach ($quickActions as $action)
                    <a class="seller-dashboard-action-card" href="{{ $action['url'] }}">
                        <i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i>
                        <span>
                            <strong>{{ $action['title'] }}</strong>
                            <small>{{ $action['copy'] }}</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="seller-dashboard-grid">
            <section class="seller-panel">
                <h2>Needs Attention</h2>
                @forelse ($priorityActions as $action)
                    <a class="seller-row-link" href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                @empty
                    <p>No urgent work right now.</p>
                @endforelse
            </section>

            <section class="seller-panel">
                <h2>Settlement Summary</h2>
                @foreach ($settlementSummary as $label => $value)
                    <div class="seller-key-value"><span>{{ str($label)->replace('_', ' ')->title() }}</span><strong>{{ is_numeric($value) ? 'Rs '.number_format($value) : $value }}</strong></div>
                @endforeach
            </section>

            <section class="seller-panel seller-panel--wide">
                <h2>Recent Orders</h2>
                @forelse ($recentOrders as $order)
                    <a class="seller-row-link" href="{{ route('seller.orders.show', $order->order_number) }}">
                        {{ $order->order_number }} · {{ $order->customer_name }} · Rs {{ number_format($order->total_amount) }} · {{ str($order->seller_order_status)->replace('_', ' ')->title() }}
                    </a>
                @empty
                    <p>No seller orders yet.</p>
                @endforelse
            </section>
        </div>
    </section>
</x-layouts.seller>
