<x-layouts.admin title="Abandoned Cart #{{ $cart->id }} - Admin">
    <x-admin.shell eyebrow="Customer Management" title="Abandoned Cart #{{ $cart->id }}" subtitle="Cart snapshot, product availability, customer history and recovery link.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.abandoned-carts.index')" tone="ghost" icon="fa-solid fa-arrow-left">Abandoned Carts</x-admin.action>
            @if ($cart->user)
                <x-admin.action :href="route('admin.customers.show', $cart->user)" tone="secondary" icon="fa-solid fa-user">View Customer</x-admin.action>
                <x-admin.action :href="route('admin.customers.whatsapp.compose', [$cart->user, 'purpose' => 'abandoned_cart_reminder'])" tone="primary" icon="fa-brands fa-whatsapp">Open WhatsApp</x-admin.action>
            @endif
        </x-slot:actions>
        <section class="customer-admin">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            <div class="customer-profile-grid">
                <section class="customer-panel customer-panel--span-7">
                    <div class="customer-panel__head"><h2>Cart Products</h2><span class="customer-badge customer-badge--neutral">{{ str($cart->status)->replace('_', ' ')->title() }}</span></div>
                    <div class="customer-cart-items">
                        @foreach ($cart->items as $item)
                            @php
                                $currentPrice = (int) ($item->variant?->price ?: $item->product?->selling_price);
                                $available = $item->product?->is_published && $item->variant && $item->variant->stock > 0;
                            @endphp
                            <article>
                                @if ($item->image)
                                    <img src="{{ asset('storage/'.$item->image) }}" alt="">
                                @endif
                                <div>
                                    <strong>{{ $item->product_name }}</strong>
                                    <span>{{ $item->colour }} · {{ $item->size }} · Qty {{ $item->quantity }}</span>
                                    <small>Original Rs {{ number_format($item->line_total) }} · Current Rs {{ number_format($currentPrice * $item->quantity) }}</small>
                                </div>
                                <span class="customer-badge customer-badge--{{ $available ? 'active' : 'inactive' }}">{{ $available ? 'Available' : 'Unavailable' }}</span>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="customer-panel customer-panel--span-5">
                    <div class="customer-panel__head"><h2>Recovery Details</h2></div>
                    <dl class="customer-detail-list">
                        <div><dt>Customer</dt><dd>{{ $cart->user?->name ?? 'Unknown' }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ $cart->user?->phone ?? 'No phone' }}</dd></div>
                        <div><dt>Original value</dt><dd>Rs {{ number_format($cart->original_value) }}</dd></div>
                        <div><dt>Created</dt><dd>{{ $cart->created_at->format('d M Y, h:i A') }}</dd></div>
                        <div><dt>Updated</dt><dd>{{ $cart->updated_at->format('d M Y, h:i A') }}</dd></div>
                        <div><dt>Abandoned</dt><dd>{{ $cart->abandoned_at?->format('d M Y, h:i A') ?? 'Not marked' }}</dd></div>
                        <div><dt>Coupon</dt><dd>{{ $cart->coupon_used ?: 'None' }}</dd></div>
                        <div><dt>Recovery link</dt><dd>{{ $recoveryUrl ? 'Signed link available' : 'Not generated' }}</dd></div>
                    </dl>
                    <form method="POST" action="{{ route('admin.customers.abandoned-carts.mark-recovered', $cart) }}">@csrf<x-admin.action type="submit" tone="primary" icon="fa-solid fa-circle-check">Mark Recovered</x-admin.action></form>
                </section>

                <section class="customer-panel customer-panel--span-12">
                    <div class="customer-panel__head"><h2>Communication History</h2></div>
                    @forelse ($cart->communications as $communication)
                        <article class="customer-communication-row">
                            <strong>{{ str($communication->category)->replace('_', ' ')->title() }}</strong>
                            <span>{{ str($communication->status)->replace('_', ' ')->title() }} · {{ $communication->admin?->name ?? 'Admin' }} · {{ $communication->created_at->format('d M Y, h:i A') }}</span>
                        </article>
                    @empty
                        <p class="customer-empty-copy">No communication history for this cart.</p>
                    @endforelse
                </section>
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
