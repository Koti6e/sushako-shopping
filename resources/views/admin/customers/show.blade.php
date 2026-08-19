@php
    $customerRef = 'CUS-'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT);
    $lastActive = ($customer->last_activity_at ?? $customer->last_login_at ?? $customer->created_at)?->format('d M Y, h:i A');
@endphp

<x-layouts.admin title="{{ $customer->name }} - Customer">
    <x-admin.shell eyebrow="Super Admin / Customers" :title="$customer->name" :subtitle="$customerRef.' · Marketplace Customer 360°'">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.index')" tone="ghost" icon="fa-solid fa-arrow-left">Customers</x-admin.action>
            <x-admin.action :href="route('admin.customers.export', ['customer_id' => $customer->id, 'export' => 'selected_customer'])" tone="primary" icon="fa-solid fa-file-export">Export</x-admin.action>
        </x-slot:actions>

        <section class="customer-profile customer-workspace">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            <article class="customer-identity-card">
                <span class="customer-avatar customer-avatar--large">{{ str($customer->name)->substr(0, 2)->upper() }}</span>
                <div class="customer-identity-card__main">
                    <div class="customer-identity-card__title">
                        <div>
                            <h2>{{ $customer->name }}</h2>
                            <p>Marketplace Customer 360 · {{ $customerRef }} · Joined {{ $customer->created_at->format('d M Y') }} · Last active {{ $lastActive }}</p>
                        </div>
                        <span class="customer-badge customer-badge--{{ $customer->status }}">{{ str($customer->status)->title() }}</span>
                    </div>

                    <div class="customer-contact-list">
                        @if ($contactActions['tel'])
                            <a href="{{ $contactActions['tel'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $customer->phone }}</a>
                        @elseif ($customer->phone)
                            <span><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $customer->phone }}</span>
                        @endif

                        @if ($contactActions['email'])
                            <a href="{{ $contactActions['email'] }}"><i class="fa-regular fa-envelope" aria-hidden="true"></i>{{ $customer->email }}</a>
                        @elseif ($customer->email)
                            <span><i class="fa-regular fa-envelope" aria-hidden="true"></i>{{ $customer->email }}</span>
                        @endif

                        @if ($contactActions['maps'])
                            <a href="{{ $contactActions['maps'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>Open in Google Maps</a>
                        @endif
                    </div>

                    <div class="customer-quick-actions">
                        @if ($contactActions['whatsapp'])
                            <a class="customer-inline-action customer-inline-action--whatsapp" href="{{ $contactActions['whatsapp'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                        @endif
                        @if ($customer->whatsapp_marketing_consent)
                            <a class="customer-inline-action" href="{{ route('admin.customers.whatsapp.compose', $customer) }}"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>Compose</a>
                        @endif
                    </div>

                    @if ($latestAddress)
                        <div class="customer-address-strip">
                            <span>{{ $latestAddress }}</span>
                            <button type="button" data-copy-text="{{ $latestAddress }}">Copy Address</button>
                        </div>
                    @endif
                </div>
            </article>

            <section class="customer-panel">
                <div class="customer-panel__head">
                    <h2>Customer Summary</h2>
                </div>
                <div class="customer-mini-grid customer-mini-grid--primary">
                    @foreach ($summary as $metric)
                        <div>
                            <span>{{ $metric['label'] }}</span>
                            <strong>{{ $metric['value'] }}</strong>
                            <small>{{ $metric['support'] }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="customer-panel">
                <div class="customer-panel__head">
                    <h2>Purchase Across Sushako</h2>
                </div>
                <dl class="customer-detail-list customer-detail-list--compact">
                    <div><dt>Stores Purchased From</dt><dd>{{ number_format($marketplace['stores_purchased_from']) }}</dd></div>
                    <div><dt>Last Order</dt><dd>{{ $marketplace['last_order'] ?: 'No orders yet' }}</dd></div>
                    <div>
                        <dt>Most Purchased Store</dt>
                        <dd>
                            @if ($marketplace['most_purchased_store'] && $marketplace['most_purchased_store_id'])
                                <a class="customer-text-link" href="{{ route('admin.sellers.show', $marketplace['most_purchased_store_id']) }}">{{ $marketplace['most_purchased_store'] }}</a>
                            @else
                                Not enough data
                            @endif
                        </dd>
                    </div>
                    <div><dt>Most Purchased Category</dt><dd>{{ $marketplace['most_purchased_category'] ?: 'Not enough data' }}</dd></div>
                </dl>
            </section>

            <section class="customer-panel customer-panel--orders">
                <div class="customer-panel__head">
                    <h2>Order History</h2>
                    <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}">View complete history</a>
                </div>
                <div class="customer-order-table" role="table" aria-label="Customer order history">
                    <div class="customer-order-table__head" role="row">
                        <span>Order ID</span>
                        <span>Seller</span>
                        <span>Products</span>
                        <span>Amount</span>
                        <span>Payment</span>
                        <span>Status</span>
                        <span>Date</span>
                    </div>
                    @forelse ($orders as $order)
                        @php
                            $vendors = $order->items->pluck('vendor')->filter()->unique('id');
                            $date = ($order->placed_at ?? $order->created_at)->format('d M Y');
                        @endphp
                        <a class="customer-order-row" href="{{ route('admin.orders.show', $order->order_number) }}" role="row">
                            <strong>{{ $order->order_number }}</strong>
                            <span>
                                @forelse ($vendors as $vendor)
                                    <em>{{ $vendor->business_name ?: $vendor->store_display_name ?: 'Seller #'.$vendor->id }}</em>
                                @empty
                                    <em>Sushako</em>
                                @endforelse
                            </span>
                            <span>{{ $order->items->pluck('product_name')->filter()->take(2)->join(', ') ?: $order->items->count().' products' }}</span>
                            <span>Rs {{ number_format((int) $order->total_amount) }}</span>
                            <span>{{ str($order->payment_method)->replace('_', ' ')->title() }} · {{ str($order->payment_status)->title() }}</span>
                            <span><b class="customer-badge customer-badge--{{ $order->status }}">{{ str($order->status)->replace('_', ' ')->title() }}</b></span>
                            <small>{{ $date }}</small>
                        </a>
                    @empty
                        <p class="customer-empty-copy">No orders yet.</p>
                    @endforelse
                </div>
                {{ $orders->links() }}
            </section>

            <div class="customer-secondary-grid">
                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Payment Behaviour</h2><span class="customer-badge customer-badge--neutral">{{ $payment['badge'] }}</span></div>
                    <dl class="customer-detail-list customer-detail-list--compact">
                        <div><dt>Preferred method</dt><dd>{{ $payment['preferred'] }}</dd></div>
                        <div><dt>Average order value</dt><dd>{{ $secondaryMetrics['average_order_value'] }}</dd></div>
                        <div><dt>Payment failures</dt><dd>{{ $secondaryMetrics['payment_failures'] }}</dd></div>
                        <div><dt>COD success rate</dt><dd>{{ $payment['cod_success_rate'] }}</dd></div>
                    </dl>
                </section>

                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Communication Preferences</h2></div>
                    <form class="customer-stack-form customer-stack-form--compact" method="POST" action="{{ route('admin.customers.consent.update', $customer) }}">
                        @csrf
                        @method('PUT')
                        <label class="customer-toggle-row"><span>Order Updates</span><input type="checkbox" name="whatsapp_order_updates" value="1" @checked($customer->whatsapp_order_updates)><b>{{ $customer->whatsapp_order_updates ? 'Allowed' : 'Not Allowed' }}</b></label>
                        <label class="customer-toggle-row"><span>Marketing WhatsApp</span><input type="checkbox" name="whatsapp_marketing_consent" value="1" @checked($customer->whatsapp_marketing_consent)><b>{{ $customer->whatsapp_marketing_consent ? 'Allowed' : 'Not Allowed' }}</b></label>
                        <label>Consent source<input name="source" value="{{ $customer->whatsapp_marketing_consent_source ?? 'Admin verified consent' }}" required maxlength="120"></label>
                        <x-admin.action type="submit" tone="primary" icon="fa-solid fa-shield-check">Save</x-admin.action>
                    </form>
                </section>

                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Customer Status</h2></div>
                    <form class="customer-stack-form customer-stack-form--compact" method="POST" action="{{ route('admin.customers.status.update', $customer) }}">
                        @csrf
                        @method('PUT')
                        <label>Status
                            <select name="status" required>
                                <option value="active" @selected($customer->status === 'active')>Active</option>
                                <option value="inactive" @selected($customer->status === 'inactive')>Inactive</option>
                                <option value="blocked" @selected($customer->status === 'blocked')>Blocked</option>
                                <option value="suspended" @selected($customer->status === 'suspended')>Suspended</option>
                            </select>
                        </label>
                        <label>Audit reason<textarea name="reason" required maxlength="240" placeholder="Required audit reason"></textarea></label>
                        <x-admin.action type="submit" tone="warning" icon="fa-solid fa-user-lock">Update</x-admin.action>
                    </form>
                </section>
            </div>

            <div class="customer-secondary-grid customer-secondary-grid--wide">
                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Abandoned Carts</h2><a href="{{ route('admin.customers.abandoned-carts.index') }}">Open Queue</a></div>
                    @forelse ($abandonedCarts as $cart)
                        <a class="customer-cart-row" href="{{ route('admin.customers.abandoned-carts.show', $cart) }}">
                            <strong>Cart #{{ $cart->id }}</strong>
                            <span>{{ $cart->items->count() }} items · Rs {{ number_format((int) $cart->original_value) }}</span>
                            <small>{{ $cart->abandoned_at?->diffForHumans() ?? 'Not abandoned' }}</small>
                        </a>
                    @empty
                        <p class="customer-empty-copy">No abandoned carts.</p>
                    @endforelse
                </section>

                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Activity Timeline</h2></div>
                    <div class="customer-timeline">
                        @forelse ($activities as $activity)
                            <article><span></span><div><strong>{{ $activity->title }}</strong><p>{{ str($activity->type)->replace('_', ' ')->title() }} · {{ $activity->occurred_at->format('d M Y, h:i A') }}</p></div></article>
                        @empty
                            <p class="customer-empty-copy">No activity recorded yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Communication History</h2><a href="{{ route('admin.customers.communications.index') }}">View Logs</a></div>
                    @forelse ($communications as $communication)
                        <article class="customer-communication-row">
                            <strong>{{ str($communication->category)->replace('_', ' ')->title() }}</strong>
                            <span>{{ str($communication->status)->replace('_', ' ')->title() }} · {{ $communication->created_at->format('d M Y, h:i A') }}</span>
                        </article>
                    @empty
                        <p class="customer-empty-copy">No communication history.</p>
                    @endforelse
                </section>

                <section class="customer-panel">
                    <div class="customer-panel__head"><h2>Saved Addresses</h2></div>
                    <div class="customer-address-grid">
                        @forelse ($addresses as $address)
                            @php
                                $savedAddress = collect([$address->address_line_1, $address->address_line_2, $address->landmark ? 'Landmark: '.$address->landmark : null, $address->city, $address->pincode])->filter()->join(', ');
                            @endphp
                            <article>
                                <strong>{{ $address->label ?: 'Saved Address' }} @if($address->is_default)<span>Default</span>@endif</strong>
                                <p>{{ $address->recipient_name }} · {{ $address->phone }}</p>
                                <p>{{ $savedAddress }}</p>
                                <div class="customer-quick-actions">
                                    @if (\App\Support\CustomerContactIntents::maps($address->delivery_location_url))
                                        <a class="customer-inline-action" href="{{ \App\Support\CustomerContactIntents::maps($address->delivery_location_url) }}" target="_blank" rel="noopener noreferrer">Maps</a>
                                    @endif
                                    @if ($savedAddress)
                                        <button type="button" data-copy-text="{{ $savedAddress }}">Copy Address</button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="customer-empty-copy">No saved addresses.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
