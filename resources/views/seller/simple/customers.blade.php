@php
    $filters = $filters ?? [];
    $activeFilters = collect($filters)->reject(fn ($value, $key) => $key === 'sort' || blank($value));
@endphp

<x-layouts.seller title="Seller Customers">
    <x-seller.header title="Customers" :vendor="$vendor" />

    <section class="seller-content seller-customer-workspace">
        <form class="seller-customer-toolbar" method="GET" action="{{ route('seller.customers.index') }}">
            <label class="seller-customer-search">
                <span>Search</span>
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by customer name or mobile number...">
                @if (filled($filters['q'] ?? null))
                    <a href="{{ route('seller.customers.index', array_filter(['activity' => $filters['activity'] ?? null, 'sort' => $filters['sort'] ?? null])) }}" aria-label="Clear search"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                @endif
            </label>

            <label>Recent Activity
                <select name="activity">
                    <option value="">Any time</option>
                    <option value="recent_30" @selected(($filters['activity'] ?? '') === 'recent_30')>Last 30 days</option>
                    <option value="inactive_60" @selected(($filters['activity'] ?? '') === 'inactive_60')>Inactive 60 days</option>
                </select>
            </label>

            <label>Sort
                <select name="sort">
                    <option value="last_order" @selected(($filters['sort'] ?? 'last_order') === 'last_order')>Last order</option>
                    <option value="orders" @selected(($filters['sort'] ?? '') === 'orders')>Most orders</option>
                    <option value="spent" @selected(($filters['sort'] ?? '') === 'spent')>Highest spend</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                </select>
            </label>

            <button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button>
            @if ($activeFilters->isNotEmpty())
                <a class="seller-customer-clear" href="{{ route('seller.customers.index') }}">Clear</a>
            @endif
        </form>

        <div class="seller-customer-results-bar">
            <span>{{ number_format($customers->total()) }} {{ str('customer')->plural($customers->total()) }}</span>
        </div>

        <section class="seller-table-card seller-customer-list">
            @forelse ($customers as $customer)
                @php
                    $digits = preg_replace('/\D+/', '', (string) $customer->customer_phone);
                    $customerKey = $customer->user_id ? 'user-'.$customer->user_id : 'phone-'.$digits;
                    $tel = \App\Support\CustomerContactIntents::tel($customer->customer_phone);
                    $email = \App\Support\CustomerContactIntents::email($customer->customer_email);
                @endphp
                <a class="seller-row-link" href="{{ route('seller.customers.show', $customerKey) }}">
                    <span>
                        <strong>{{ $customer->customer_name }}</strong>
                        <small>
                            @if ($tel)
                                <em>{{ $customer->customer_phone }}</em>
                            @endif
                            @if ($email)
                                <em>{{ $customer->customer_email }}</em>
                            @endif
                        </small>
                    </span>
                    <span>{{ $customer->orders_count }} orders</span>
                    <span>Rs {{ number_format($customer->total_spend) }}</span>
                    <span>{{ \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y') }}</span>
                </a>
            @empty
                <p>No seller customers match this search.</p>
            @endforelse

            {{ $customers->links() }}
        </section>
    </section>
</x-layouts.seller>
