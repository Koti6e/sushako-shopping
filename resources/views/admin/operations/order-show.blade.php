<x-layouts.admin title="Order {{ $order->order_number }} - Admin">
    @php
        $address = collect([$order->address_line_1, $order->address_line_2, $order->city, $order->pincode])->filter()->join(', ');
        $providerLabel = $order->shipping_provider === 'others'
            ? $order->shipping_provider_other
            : ($shippingProviders[$order->shipping_provider] ?? 'Not selected');
        $whatsAppText = rawurlencode("Hello {$order->customer_name},\n\nSushako Shopping order update:\nOrder ID: {$order->order_number}\nStatus: ".ucfirst($order->status)."\nCourier: {$providerLabel}\nTracking: ".($order->tracking_number ?: 'Will be updated soon')."\nTotal: INR {$order->total_amount}\n\nPlease login to your Sushako account to track this order.");
    @endphp

    <section class="admin-shell">
        <aside class="admin-sidebar">
            <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
            <nav>
                <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                <a href="{{ route('admin.products.index') }}"><i class="fa-solid fa-box"></i> Products</a>
                <a href="{{ route('admin.inventory.index') }}"><i class="fa-solid fa-warehouse"></i> Inventory</a>
                <a href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-receipt"></i> Orders</a>
                <a href="{{ route('admin.customers.index') }}"><i class="fa-solid fa-users"></i> Customers</a>
                <a href="{{ route('admin.settings.company') }}"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <x-admin.side-meta />
        </aside>

        <main class="admin-main">
            <header class="admin-topbar admin-topbar--premium">
                <div>
                    <span>Order Details</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
                <div class="admin-topbar-actions">
                    <a class="button button--secondary" href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Order Queue</a>
                    <a class="button button--secondary" href="{{ route('admin.orders.invoice', $order->order_number) }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Invoice PDF</a>
                    <form class="admin-global-logout admin-global-logout--top" method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</button>
                    </form>
                </div>
            </header>

            <section class="admin-dashboard-panel">
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Complete Details</p>
                        <h1>{{ $order->customer_name }}</h1>
                        <p class="lede">Review customer, address, products and payment before updating shipment manually.</p>
                    </div>
                    <span class="admin-status-pill admin-status-pill--{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>

                <section class="admin-order-details-panel">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Order Information</p>
                            <h3>Customer and delivery</h3>
                        </div>
                        <strong>&#8377;{{ number_format($order->total_amount) }}</strong>
                    </div>

                    <div class="admin-order-card__grid">
                        <section>
                            <h3>Customer</h3>
                            <p>{{ $order->customer_name }}</p>
                            <p>{{ $order->customer_phone }}{{ $order->customer_email ? ' · '.$order->customer_email : ' · No email' }}</p>
                            <p>{{ $address }}</p>
                            @if ($order->landmark)
                                <p>Landmark: {{ $order->landmark }}</p>
                            @endif
                            @if ($order->delivery_location_url)
                                <a href="{{ $order->delivery_location_url }}" target="_blank" rel="noopener noreferrer">Open shared map location</a>
                            @endif
                            <a class="button button--secondary" href="https://wa.me/91{{ preg_replace('/\D+/', '', $order->customer_phone) }}?text={{ $whatsAppText }}" target="_blank" rel="noopener noreferrer">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp Customer
                            </a>
                        </section>

                        <section>
                            <h3>Products</h3>
                            @foreach ($order->items as $item)
                                <article class="admin-order-line-item">
                                    @if ($item->image)
                                        <img src="{{ $item->image }}" alt="{{ $item->product_name }}" loading="lazy">
                                    @endif
                                    <div>
                                        <p>{{ $item->product_name }}</p>
                                        <span>{{ $item->colour }} / {{ $item->size }} · Qty {{ $item->quantity }}</span>
                                    </div>
                                    <strong>&#8377;{{ number_format($item->line_total) }}</strong>
                                </article>
                            @endforeach
                        </section>
                    </div>
                </section>

                <section class="admin-shipment-panel">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Shipment</p>
                            <h3>Manual shipping workflow</h3>
                        </div>
                        <span>{{ $providerLabel }}</span>
                    </div>

                    <div class="admin-fulfillment-timeline">
                        <span class="@if($order->placed_at) is-done @endif">Placed</span>
                        <span class="@if($order->packed_at) is-done @endif">Packed</span>
                        <span class="@if($order->shipped_at) is-done @endif">Shipped</span>
                        <span class="@if($order->delivered_at) is-done @endif">Delivered</span>
                    </div>

                    <form method="POST" action="{{ route('admin.orders.workflow.update', $order->order_number) }}" class="admin-shipping-form">
                        @csrf
                        @method('PUT')
                        <label>Status
                            <select name="status" required>
                                @foreach (['placed' => 'Backlog / Placed', 'packing' => 'Packing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'] as $value => $label)
                                    <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Shipping Partner
                            <select name="shipping_provider">
                                <option value="">Select partner</option>
                                @foreach ($shippingProviders as $value => $label)
                                    <option value="{{ $value }}" @selected($order->shipping_provider === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Other Partner
                            <input name="shipping_provider_other" value="{{ old('shipping_provider_other', $order->shipping_provider_other) }}" placeholder="Mention courier name">
                        </label>
                        <label>Tracking / Reference Number
                            <input name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" placeholder="Manual tracking ID">
                        </label>
                        <button class="button button--primary" type="submit">Update Shipment</button>
                    </form>
                </section>
            </section>
        </main>
    </section>
</x-layouts.admin>
