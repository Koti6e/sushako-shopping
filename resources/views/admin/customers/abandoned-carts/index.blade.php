<x-layouts.admin title="Abandoned Carts - Admin">
    <x-admin.shell eyebrow="Customer Management" title="Abandoned Carts" subtitle="Review registered-customer carts that have been inactive past the configured recovery window.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.index')" tone="secondary" icon="fa-solid fa-users">Customers</x-admin.action>
            <x-admin.action :href="route('admin.customers.export', ['export' => 'abandoned_cart_report'])" tone="primary" icon="fa-solid fa-file-export">Export</x-admin.action>
        </x-slot:actions>
        <section class="customer-admin">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            <div class="customer-kpi-grid customer-kpi-grid--six">
                @foreach ($summaryCards as $card)
                    <article class="customer-kpi-card">
                        <span><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
                        <div>
                            <p>{{ $card['label'] }}</p>
                            <strong>{{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</strong>
                            <small>{{ $card['support'] }}</small>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="customer-cart-queue">
                @forelse ($carts as $cart)
                    @php
                        $customer = $cart->user;
                        $orders = $customer?->orders?->count() ?? 0;
                        $cod = $customer?->orders?->where('payment_method', 'cod')->count() ?? 0;
                        $online = $customer?->orders?->where('payment_method', 'razorpay')->where('payment_status', 'paid')->count() ?? 0;
                    @endphp
                    <article class="customer-cart-card">
                        <div>
                            <h2>{{ $customer?->name ?? 'Registered Customer' }}</h2>
                            <p>{{ $customer?->phone ? substr($customer->phone, 0, 2).'******'.substr($customer->phone, -2) : 'No phone' }} · Registered · {{ $cart->items->count() }} item{{ $cart->items->count() === 1 ? '' : 's' }}</p>
                            <p>{{ $cart->items->pluck('product_name')->take(3)->implode(', ') }}</p>
                        </div>
                        <dl>
                            <div><dt>Cart value</dt><dd>Rs {{ number_format($cart->original_value) }}</dd></div>
                            <div><dt>Last activity</dt><dd>{{ $cart->last_activity_at?->diffForHumans() }}</dd></div>
                            <div><dt>Previous orders</dt><dd>{{ $orders }}</dd></div>
                            <div><dt>Payment preference</dt><dd>{{ $online > $cod ? 'Mostly Online' : ($cod > 0 ? 'Mostly COD' : 'New Customer') }}</dd></div>
                            <div><dt>Marketing consent</dt><dd>{{ $customer?->whatsapp_marketing_consent ? 'Available' : 'Not Available' }}</dd></div>
                            <div><dt>Status</dt><dd>{{ str($cart->status)->replace('_', ' ')->title() }}</dd></div>
                        </dl>
                        <div class="customer-card-actions">
                            @if ($customer)
                                <a class="customer-inline-action" href="{{ route('admin.customers.show', $customer) }}">View Customer</a>
                                <a class="customer-inline-action customer-inline-action--whatsapp" href="{{ route('admin.customers.whatsapp.compose', [$customer, 'purpose' => 'abandoned_cart_reminder']) }}">Open WhatsApp</a>
                            @endif
                            <a class="customer-inline-action" href="{{ route('admin.customers.abandoned-carts.show', $cart) }}">View Cart</a>
                            <button class="customer-muted-action" type="button" disabled>Create Coupon</button>
                            <form method="POST" action="{{ route('admin.customers.abandoned-carts.mark-recovered', $cart) }}">@csrf<x-admin.action type="submit" tone="secondary" icon="fa-solid fa-circle-check">Mark Recovered</x-admin.action></form>
                            <form method="POST" action="{{ route('admin.customers.abandoned-carts.dismiss', $cart) }}">@csrf<x-admin.action type="submit" tone="danger" icon="fa-solid fa-xmark">Dismiss</x-admin.action></form>
                        </div>
                    </article>
                @empty
                    <div class="customer-empty-state">
                        <h2>No abandoned carts</h2>
                        <p>Registered carts appear here after the configured inactivity window.</p>
                    </div>
                @endforelse
            </div>

            {{ $carts->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
