<x-layouts.admin title="Order {{ $order->order_number }} - Admin">
    @php
        $address = collect([$order->address_line_1, $order->address_line_2, $order->city, $order->pincode])->filter()->join(', ');
        $providerLabel = $order->shipping_provider === 'others'
            ? $order->shipping_provider_other
            : ($shippingProviders[$order->shipping_provider] ?? 'Not selected');
        $whatsAppText = rawurlencode("Hello {$order->customer_name},\n\nSushako Shopping order update:\nOrder ID: {$order->order_number}\nStatus: ".ucfirst($order->status)."\nCourier: {$providerLabel}\nTracking: ".($order->tracking_number ?: 'Will be updated soon')."\nTotal: INR {$order->total_amount}\n\nYou can track this order from the Sushako Shopping order tracking page.");
    @endphp

    <x-admin.shell eyebrow="Orders / {{ $order->order_number }}" title="Order Details" :badge="$order->order_number" subtitle="Review customer, products, payment and shipment details.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.orders.index')" tone="ghost" icon="fa-solid fa-arrow-left">Order Queue</x-admin.action>
            <x-admin.action :href="route('admin.orders.invoice', $order->order_number)" tone="outline" icon="fa-solid fa-file-pdf" target="_blank" rel="noopener noreferrer">Invoice PDF</x-admin.action>
            @if ($order->latestShippingLabel)
                <x-admin.action :href="route('admin.shipping-labels.show', $order->latestShippingLabel)" tone="primary" icon="fa-solid fa-tags">Shipping Label</x-admin.action>
            @endif
        </x-slot:actions>

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
                                <a href="{{ $order->delivery_location_url }}" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>
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

                <section class="admin-shipment-panel">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Shipping Label</p>
                            <h3>Label generation and delivery location</h3>
                        </div>
                        <span>{{ $order->latestShippingLabel?->label_number ?? 'Pending' }}</span>
                    </div>

                    <form method="POST" action="{{ route('admin.shipping-labels.generate', $order->order_number) }}" class="admin-shipping-form">
                        @csrf
                        <label>Brand Mode
                            <select name="brand_mode">
                                <option value="sushako">Sushako Branded</option>
                                <option value="seller">Seller Branded</option>
                                <option value="courier_neutral">Courier Neutral</option>
                            </select>
                        </label>
                        <label>Fulfillment
                            <select name="fulfillment_type">
                                <option value="fulfilled_by_sushako">Fulfilled By Sushako</option>
                                <option value="seller_direct">Seller Direct</option>
                                <option value="warehouse">Warehouse</option>
                            </select>
                        </label>
                        <label>Print Format
                            <select name="print_format">
                                <option value="a6_thermal">A6 Thermal</option>
                                <option value="a5">A5</option>
                                <option value="a4_single">A4 Single</option>
                                <option value="a4_double">A4 Double</option>
                                <option value="a4_four">A4 Four Labels</option>
                            </select>
                        </label>
                        <label>Package Count<input name="package_count" type="number" min="1" max="99" value="1"></label>
                        <label>Weight Grams<input name="weight_grams" type="number" min="1" placeholder="Optional"></label>
                        <button class="button button--primary" type="submit">{{ $order->latestShippingLabel ? 'Generate New Label' : 'Generate Label' }}</button>
                    </form>

                    <form method="POST" action="{{ route('admin.shipping-labels.location.update', $order->order_number) }}" class="admin-shipping-form">
                        @csrf
                        @method('PUT')
                        <label>Latitude<input name="delivery_latitude" value="{{ old('delivery_latitude', $order->delivery_latitude) }}"></label>
                        <label>Longitude<input name="delivery_longitude" value="{{ old('delivery_longitude', $order->delivery_longitude) }}"></label>
                        <label>Google Maps URL<input name="delivery_location_url" type="url" value="{{ old('delivery_location_url', $order->delivery_location_url) }}"></label>
                        <label>Capture Method
                            <select name="location_capture_method">
                                <option value="admin_updated">Admin Updated</option>
                                <option value="gps">GPS</option>
                                <option value="google_maps_pin">Google Maps Pin</option>
                                <option value="manual_entry">Manual Entry</option>
                            </select>
                        </label>
                        <label class="admin-check-row"><input name="location_confirmed" type="checkbox" value="1" @checked($order->location_confirmed)> Location Confirmed</label>
                        <button class="button button--secondary" type="submit">Save Delivery Location</button>
                    </form>
                </section>
            </section>
    </x-admin.shell>
</x-layouts.admin>
