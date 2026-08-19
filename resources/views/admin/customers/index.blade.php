@php
    $maskPhone = fn (?string $phone) => $phone ? substr($phone, 0, 2).'******'.substr($phone, -2) : 'No phone';
    $customerRef = fn ($id) => 'CUS-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    $searchValue = $filters['q'] ?? $filters['name'] ?? $filters['phone'] ?? $filters['email'] ?? $filters['customer_id'] ?? $filters['order_number'] ?? '';
    $advancedFilters = collect(['activity', 'registered_from', 'registered_to', 'last_order_from', 'last_order_to'])
        ->filter(fn ($key) => filled($filters[$key] ?? null));
    $activeFilters = collect($filters)->reject(fn ($value, $key) => in_array($key, ['sort'], true) || blank($value));
    $moreFiltersLabel = 'More Filters'.($advancedFilters->isNotEmpty() ? ' ('.$advancedFilters->count().')' : '');
@endphp

<section class="customer-admin">
    @if (session('status'))
        <div class="status-banner">{{ session('status') }}</div>
    @endif

    <div class="customer-toolbar">
        <form class="customer-filter-card customer-filter-card--compact" method="GET" action="{{ route('admin.customers.index') }}">
            <div class="customer-filter-grid customer-filter-grid--compact">
                <label class="customer-search-field">
                    <span>Search</span>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ $searchValue }}" placeholder="Search customers by name, phone, email, customer ID or order number...">
                    @if (filled($searchValue))
                        <a href="{{ route('admin.customers.index', array_filter(['status' => $filters['status'] ?? null, 'payment' => $filters['payment'] ?? null, 'type' => $filters['type'] ?? null, 'sort' => $filters['sort'] ?? null, 'view' => $viewMode])) }}" aria-label="Clear search"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                    @endif
                </label>
            <label>Status
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked', 'suspended' => 'Suspended'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Payment
                <select name="payment">
                    <option value="">All Payments</option>
                    @foreach (['cod' => 'COD', 'online' => 'Online', 'mixed' => 'Mixed'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
                <label>Customer Type
                    <select name="type">
                        <option value="">All Types</option>
                        @foreach (['registered' => 'Registered', 'converted' => 'Converted', 'repeat' => 'Repeat'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Sort
                    <select name="sort" data-server-sort>
                        @foreach (['latest_activity' => 'Latest Activity', 'last_order' => 'Last Order', 'spent' => 'Highest Spend', 'orders' => 'Most Orders', 'oldest' => 'Oldest Customer'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest_activity') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <details class="customer-more-filters" @if($advancedFilters->isNotEmpty()) open @endif>
                    <summary>
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                        <span>{{ $moreFiltersLabel }}</span>
                    </summary>
                    <div class="customer-more-filters__panel">
                        <label>Activity
                            <select name="activity">
                                <option value="">Any activity</option>
                                @foreach (['new' => 'New this month', 'inactive_30' => 'Inactive 30 days', 'inactive_60' => 'Inactive 60 days', 'cancelled' => 'Cancelled orders', 'abandoned' => 'Abandoned carts'] as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['activity'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Registered From<input type="date" name="registered_from" value="{{ $filters['registered_from'] ?? '' }}"></label>
                        <label>Registered To<input type="date" name="registered_to" value="{{ $filters['registered_to'] ?? '' }}"></label>
                        <label>Last Order From<input type="date" name="last_order_from" value="{{ $filters['last_order_from'] ?? '' }}"></label>
                        <label>Last Order To<input type="date" name="last_order_to" value="{{ $filters['last_order_to'] ?? '' }}"></label>
                        <div class="customer-more-filters__actions">
                            <x-admin.action type="submit" tone="primary" icon="fa-solid fa-check">Apply</x-admin.action>
                        </div>
                    </div>
                </details>
            </div>

            <div class="customer-filter-card__actions customer-filter-card__actions--compact">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <x-admin.action type="submit" tone="primary" icon="fa-solid fa-magnifying-glass">Search</x-admin.action>
                @if ($activeFilters->isNotEmpty())
                    <x-admin.action :href="route('admin.customers.index', ['view' => $viewMode])" tone="ghost" icon="fa-solid fa-xmark">Clear</x-admin.action>
                @endif
            </div>
        </form>

        <div class="customer-results-bar">
            <span>{{ number_format($customers->total()) }} {{ str('customer')->plural($customers->total()) }}</span>
            <div class="customer-view-toggle" data-customer-view-toggle>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'cards']) }}" data-view-mode="cards" @class(['is-active' => $viewMode !== 'table'])><i class="fa-solid fa-grip" aria-hidden="true"></i><span>Cards</span></a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" data-view-mode="table" @class(['is-active' => $viewMode === 'table'])><i class="fa-solid fa-table-list" aria-hidden="true"></i><span>Table</span></a>
            </div>
        </div>
    </div>

    @if ($viewMode === 'table')
        <section class="customer-table-card">
            <div class="customer-table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total Orders</th>
                            <th>COD Orders</th>
                            <th>Online Paid</th>
                            <th>Cancelled</th>
                            <th>Abandoned Carts</th>
                            <th>Total Spent</th>
                            <th>Last Order</th>
                            <th>Last Activity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr onclick="window.location='{{ route('admin.customers.show', $customer) }}'">
                                <td><a href="{{ route('admin.customers.show', $customer) }}"><strong>{{ $customer->name }}</strong><small>{{ $customerRef($customer->id) }}</small></a></td>
                                <td>{{ $maskPhone($customer->phone) }}</td>
                                <td>{{ $customer->total_orders_count }}</td>
                                <td>{{ $customer->cod_orders_count }}</td>
                                <td>{{ $customer->online_paid_orders_count }}</td>
                                <td>{{ $customer->cancelled_orders_count }}</td>
                                <td>{{ $customer->abandoned_carts_count }}</td>
                                <td>Rs {{ number_format((int) $customer->total_spent) }}</td>
                                <td>{{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y') : 'No orders' }}</td>
                                <td>{{ ($customer->last_activity_at ?? $customer->last_login_at ?? $customer->created_at)?->format('d M Y, h:i A') }}</td>
                                <td><span class="customer-badge customer-badge--{{ $customer->status }}">{{ str($customer->status)->title() }}</span></td>
                                <td><a class="customer-inline-action" href="{{ route('admin.customers.whatsapp.compose', $customer) }}">WhatsApp</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="12">No customers match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="customer-card-grid">
            @forelse ($customers as $customer)
                <article class="customer-card">
                    <a class="customer-card__main" href="{{ route('admin.customers.show', $customer) }}">
                        <span class="customer-avatar">{{ str($customer->name)->substr(0, 2)->upper() }}</span>
                        <div>
                            <h2>{{ $customer->name }}</h2>
                            <p>{{ $customerRef($customer->id) }} · Joined {{ $customer->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="customer-badge customer-badge--{{ $customer->status }}">{{ str($customer->status)->title() }}</span>
                    </a>
                    <div class="customer-card__contact">
                        <span><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $maskPhone($customer->phone) }}</span>
                        <span><i class="fa-regular fa-envelope" aria-hidden="true"></i>{{ $customer->email }}</span>
                    </div>
                    <dl class="customer-card__metrics">
                        <div><dt>Orders</dt><dd>{{ $customer->total_orders_count }}</dd></div>
                        <div><dt>Spent</dt><dd>Rs {{ number_format((int) $customer->total_spent) }}</dd></div>
                        <div><dt>COD</dt><dd>{{ $customer->cod_orders_count }}</dd></div>
                        <div><dt>Online</dt><dd>{{ $customer->online_paid_orders_count }}</dd></div>
                        <div><dt>Cancelled</dt><dd>{{ $customer->cancelled_orders_count }}</dd></div>
                        <div><dt>Abandoned</dt><dd>{{ $customer->abandoned_carts_count }}</dd></div>
                    </dl>
                    <div class="customer-card__footer">
                        <p>Last activity <strong>{{ ($customer->last_activity_at ?? $customer->last_login_at ?? $customer->created_at)?->format('d M Y, h:i A') }}</strong></p>
                        <div>
                            <a class="customer-inline-action" href="{{ route('admin.customers.show', $customer) }}">View Profile</a>
                            @if ($customer->whatsapp_marketing_consent)
                                <a class="customer-inline-action customer-inline-action--whatsapp" href="{{ route('admin.customers.whatsapp.compose', $customer) }}">WhatsApp</a>
                            @else
                                <span class="customer-muted-action">No consent</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="customer-empty-state">
                    <h2>No customers found</h2>
                    <p>Try adjusting filters or wait for new customer registrations.</p>
                </div>
            @endforelse
        </section>
    @endif

    <div class="customer-pagination">
        {{ $customers->links() }}
    </div>
</section>
