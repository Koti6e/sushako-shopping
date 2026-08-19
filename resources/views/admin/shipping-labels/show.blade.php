<x-layouts.admin title="Shipping Label {{ $label->label_number }} - Admin">
    @php
        $order = $label->order;
        $hasLocation = filled($mapsUrl);
        $hasCoordinates = filled($order->delivery_latitude) && filled($order->delivery_longitude);
        $locationState = $order->location_confirmed
            ? 'Location Confirmed'
            : ($hasLocation ? 'Location Available' : 'Location Not Available');
        $captureMethod = $order->location_capture_method
            ? str($order->location_capture_method)->replace('_', ' ')->title()
            : 'Not Captured';
        $generatedBy = $label->generatedBy?->name ?? 'System';
        $generatedAt = $label->generated_at?->format('d M Y, h:i A') ?? 'Not recorded';
        $versionLabel = 'v'.number_format($label->version ?? 1);
    @endphp

    <x-admin.shell eyebrow="Shipping Label" title="Shipping Label" subtitle="Order {{ $order->order_number }} · Label {{ $label->label_number }}">
        <x-slot:actions>
            <x-admin.action :href="route('admin.shipping-labels.index')" tone="ghost" icon="fa-solid fa-arrow-left">Labels</x-admin.action>
            <x-admin.action :href="route('admin.orders.show', $order->order_number)" tone="secondary" icon="fa-solid fa-receipt">View Order</x-admin.action>
            <x-admin.action :href="route('admin.shipping-labels.pdf', $label)" tone="outline" icon="fa-solid fa-file-pdf">Download PDF</x-admin.action>
            <form method="POST" action="{{ route('admin.shipping-labels.print', $label) }}" target="_blank">
                @csrf
                <input type="hidden" name="copies" value="1">
                <x-admin.action type="submit" tone="primary" icon="fa-solid fa-print">Print</x-admin.action>
            </form>
            <x-admin.action-menu>
                <form method="POST" action="{{ route('admin.shipping-labels.regenerate', $label) }}" data-confirm-submit="Regenerate this shipping label and create a new version?">
                    @csrf
                    <x-admin.action type="submit" tone="warning" icon="fa-solid fa-rotate">Regenerate</x-admin.action>
                </form>
            </x-admin.action-menu>
        </x-slot:actions>

        <section class="admin-dashboard-panel shipping-label-admin shipping-label-workspace">
            @if (session('status'))
                <div class="status-banner shipping-label-workspace__alert">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="status-banner status-banner--error shipping-label-workspace__alert">{{ $errors->first() }}</div>
            @endif

            <div class="shipping-label-detail-grid">
                <section class="shipping-label-preview-card" aria-label="Shipping label preview">
                    <header class="shipping-label-card-header">
                        <div>
                            <p class="eyebrow">Label Preview</p>
                            <h2>{{ $label->label_number }}</h2>
                        </div>
                        <div class="shipping-label-preview-tools" aria-label="Preview tools">
                            <span>{{ str($label->print_format ?? 'a6_thermal')->replace('_', ' ')->title() }}</span>
                            <span>Fit to Width</span>
                        </div>
                    </header>

                    <div class="shipping-label-preview-surface">
                        @include('admin.shipping-labels.template', [
                            'label' => $label,
                            'order' => $order,
                            'locationQr' => $locationQr,
                            'internalBarcode' => $internalBarcode,
                            'internalQr' => $internalQr,
                            'logoData' => asset('assets/brand/sushako-shopping-official-email.png'),
                        ])
                    </div>

                    <footer class="shipping-label-preview-footer">
                        <form method="POST" action="{{ route('admin.shipping-labels.print', $label) }}" target="_blank">
                            @csrf
                            <input type="hidden" name="copies" value="1">
                            <button class="button button--primary" type="submit"><i class="fa-solid fa-print" aria-hidden="true"></i> Print Label</button>
                        </form>
                        <a class="button button--secondary" href="{{ route('admin.shipping-labels.pdf', $label) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Download PDF</a>
                    </footer>
                </section>

                <aside class="shipping-label-panel" aria-label="Shipping label configuration">
                    <section class="shipping-label-workspace-card shipping-label-actions-card">
                        <header class="shipping-label-card-header">
                            <div>
                                <p class="eyebrow">Label Actions</p>
                                <h2>{{ $order->order_number }}</h2>
                                <span>{{ $order->customer_name }} · {{ $order->city }} {{ $order->pincode }}</span>
                            </div>
                            <span class="shipping-label-status shipping-label-status--{{ $label->status }}">{{ str($label->status)->replace('_', ' ')->title() }}</span>
                        </header>

                        <dl class="shipping-label-facts">
                            <div><dt>Generated</dt><dd>{{ $generatedAt }}</dd></div>
                            <div><dt>Generated By</dt><dd>{{ $generatedBy }}</dd></div>
                            <div><dt>Print Count</dt><dd>{{ number_format($label->print_count ?? 0) }}</dd></div>
                            <div><dt>Version</dt><dd>{{ $versionLabel }}</dd></div>
                        </dl>

                        <div class="shipping-label-button-row">
                            <form method="POST" action="{{ route('admin.shipping-labels.print', $label) }}" target="_blank">
                                @csrf
                                <input type="hidden" name="copies" value="1">
                                <button class="button button--primary" type="submit"><i class="fa-solid fa-print" aria-hidden="true"></i> Print Label</button>
                            </form>
                            <a class="button button--secondary" href="{{ route('admin.shipping-labels.pdf', $label) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Download PDF</a>
                            <form method="POST" action="{{ route('admin.shipping-labels.regenerate', $label) }}" data-confirm-submit="Regenerate this shipping label and create a new version?">
                                @csrf
                                <button class="button button--secondary" type="submit"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Regenerate Label</button>
                            </form>
                            <a class="button button--secondary" href="{{ route('admin.orders.show', $order->order_number) }}"><i class="fa-solid fa-receipt" aria-hidden="true"></i> View Order</a>
                            @if ($mapsUrl)
                                <a class="button button--secondary" href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Open Maps</a>
                            @endif
                        </div>
                    </section>

                    <form class="shipping-label-workspace-form" method="POST" action="{{ route('admin.shipping-labels.regenerate', $label) }}" data-confirm-submit="Save these settings and regenerate a new label version?">
                        @csrf

                        <section class="shipping-label-workspace-card">
                            <header class="shipping-label-card-header">
                                <div>
                                    <p class="eyebrow">Branding & Fulfilment</p>
                                    <h2>Label presentation</h2>
                                </div>
                            </header>

                            <div class="shipping-label-form-grid shipping-label-form-grid--three">
                                <label>Brand Mode
                                    <select name="brand_mode">
                                        <option value="sushako" @selected($label->brand_mode === 'sushako')>Sushako Branded</option>
                                        <option value="seller" @selected($label->brand_mode === 'seller')>Seller Branded</option>
                                        <option value="courier_neutral" @selected($label->brand_mode === 'courier_neutral')>Courier Neutral</option>
                                    </select>
                                    @error('brand_mode') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Fulfilment Mode
                                    <select name="fulfillment_type">
                                        <option value="fulfilled_by_sushako" @selected($label->fulfillment_type === 'fulfilled_by_sushako')>Fulfilled By Sushako</option>
                                        <option value="seller_direct" @selected($label->fulfillment_type === 'seller_direct')>Seller Direct</option>
                                        <option value="warehouse" @selected($label->fulfillment_type === 'warehouse')>Warehouse</option>
                                    </select>
                                    @error('fulfillment_type') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Print Format
                                    <select name="print_format">
                                        @foreach (['a6_thermal' => 'A6 Thermal', 'a5' => 'A5', 'a4_single' => 'A4 Single', 'a4_double' => 'A4 Double', 'a4_four' => 'A4 Four Labels'] as $value => $text)
                                            <option value="{{ $value }}" @selected($label->print_format === $value)>{{ $text }}</option>
                                        @endforeach
                                    </select>
                                    @error('print_format') <small>{{ $message }}</small> @enderror
                                </label>
                            </div>
                        </section>

                        <section class="shipping-label-workspace-card">
                            <header class="shipping-label-card-header">
                                <div>
                                    <p class="eyebrow">Package Details</p>
                                    <h2>Parcel and carrier</h2>
                                </div>
                            </header>

                            <div class="shipping-label-form-grid shipping-label-form-grid--four">
                                <label>Package Number
                                    <input name="package_index" type="number" min="1" max="99" value="{{ old('package_index', $label->package_index) }}">
                                    @error('package_index') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Total Packages
                                    <input name="package_count" type="number" min="1" max="99" value="{{ old('package_count', $label->package_count) }}">
                                    @error('package_count') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Shipping Weight
                                    <input name="weight_grams" type="number" min="1" value="{{ old('weight_grams', $label->weight_grams) }}">
                                    <em>Grams</em>
                                    @error('weight_grams') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Weight Unit
                                    <input value="Grams" readonly>
                                </label>
                                <label>Courier Code
                                    <input name="courier_code" value="{{ old('courier_code', $label->courier_code) }}">
                                    @error('courier_code') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Courier Name
                                    <input name="courier_name" value="{{ old('courier_name', $label->courier_name) }}">
                                    @error('courier_name') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Tracking Number
                                    <input value="{{ $order->tracking_number ?: 'Not assigned' }}" readonly>
                                </label>
                                <label>Warehouse
                                    <input name="warehouse_name" value="{{ old('warehouse_name', $label->warehouse_name) }}">
                                    @error('warehouse_name') <small>{{ $message }}</small> @enderror
                                </label>
                            </div>
                        </section>

                        <section class="shipping-label-workspace-card">
                            <header class="shipping-label-card-header">
                                <div>
                                    <p class="eyebrow">Seller & Sender Details</p>
                                    <h2>Sender identity</h2>
                                </div>
                            </header>

                            <div class="shipping-label-form-grid shipping-label-form-grid--two">
                                <label>Seller Name
                                    <input name="seller_name" value="{{ old('seller_name', $label->seller_name) }}">
                                    @error('seller_name') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Seller Contact
                                    <input name="seller_contact" value="{{ old('seller_contact', $label->seller_contact) }}">
                                    @error('seller_contact') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Seller GST
                                    <input name="seller_gst" value="{{ old('seller_gst', $label->seller_gst) }}">
                                    @error('seller_gst') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Seller Logo
                                    <input value="{{ $label->seller_logo_path ? basename($label->seller_logo_path) : 'No logo configured' }}" readonly>
                                </label>
                                <label class="shipping-label-field--full">Sender Address
                                    <textarea name="seller_address" rows="3">{{ old('seller_address', $label->seller_address) }}</textarea>
                                    @error('seller_address') <small>{{ $message }}</small> @enderror
                                </label>
                                <label class="shipping-label-field--full">Return Address
                                    <textarea name="seller_return_address" rows="3">{{ old('seller_return_address', $label->seller_return_address) }}</textarea>
                                    @error('seller_return_address') <small>{{ $message }}</small> @enderror
                                </label>
                                <label class="shipping-label-field--full">Warehouse Address
                                    <textarea name="warehouse_address" rows="3">{{ old('warehouse_address', $label->warehouse_address) }}</textarea>
                                    @error('warehouse_address') <small>{{ $message }}</small> @enderror
                                </label>
                            </div>
                        </section>

                        <footer class="shipping-label-form-footer">
                            <a class="button button--secondary" href="{{ route('admin.shipping-labels.index') }}">Cancel</a>
                            <button class="button button--primary" type="submit" data-submit-text="Regenerating..."><i class="fa-solid fa-rotate" aria-hidden="true"></i> Save & Regenerate</button>
                        </footer>
                    </form>

                    <form class="shipping-label-workspace-form" method="POST" action="{{ route('admin.shipping-labels.location.update', $order->order_number) }}">
                        @csrf
                        @method('PUT')

                        <section class="shipping-label-workspace-card shipping-label-location-card">
                            <header class="shipping-label-card-header">
                                <div>
                                    <p class="eyebrow">Delivery Location</p>
                                    <h2>QR destination</h2>
                                </div>
                                <span @class(['shipping-location-badge', 'is-confirmed' => $order->location_confirmed, 'is-missing' => ! $hasLocation])>{{ $locationState }}</span>
                            </header>

                            <div class="shipping-label-location-status">
                                <span>{{ $captureMethod }}</span>
                                <span>{{ $hasCoordinates ? 'Coordinates Captured' : 'Coordinates Missing' }}</span>
                                <span>{{ $mapsUrl ? 'Google Maps Ready' : 'No Maps URL' }}</span>
                            </div>

                            @unless ($hasLocation)
                                <div class="shipping-label-warning">
                                    Delivery location has not been provided. Add coordinates or a valid Google Maps URL before generating the location QR.
                                </div>
                            @endunless

                            <div class="shipping-label-form-grid shipping-label-location-grid">
                                <label>Latitude
                                    <input name="delivery_latitude" value="{{ old('delivery_latitude', $order->delivery_latitude) }}">
                                    @error('delivery_latitude') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Longitude
                                    <input name="delivery_longitude" value="{{ old('delivery_longitude', $order->delivery_longitude) }}">
                                    @error('delivery_longitude') <small>{{ $message }}</small> @enderror
                                </label>
                                <label class="shipping-label-field--full">Google Maps URL
                                    <input name="delivery_location_url" type="url" value="{{ old('delivery_location_url', $order->delivery_location_url) }}">
                                    @error('delivery_location_url') <small>{{ $message }}</small> @enderror
                                </label>
                                <label>Capture Method
                                    <select name="location_capture_method">
                                        <option value="admin_updated" @selected(old('location_capture_method', $order->location_capture_method) === 'admin_updated')>Admin Updated</option>
                                        <option value="gps" @selected(old('location_capture_method', $order->location_capture_method) === 'gps')>GPS</option>
                                        <option value="google_maps_pin" @selected(old('location_capture_method', $order->location_capture_method) === 'google_maps_pin')>Google Maps Pin</option>
                                        <option value="manual_entry" @selected(old('location_capture_method', $order->location_capture_method) === 'manual_entry')>Manual Entry</option>
                                    </select>
                                    @error('location_capture_method') <small>{{ $message }}</small> @enderror
                                </label>
                                <label class="shipping-label-toggle">
                                    <input type="checkbox" name="location_confirmed" value="1" @checked(old('location_confirmed', $order->location_confirmed))>
                                    <span>Location Confirmed</span>
                                </label>
                                <label class="shipping-label-field--full">Final QR Destination
                                    <input value="{{ $mapsUrl ?: 'Location QR is unavailable until a valid map URL or coordinates exist.' }}" readonly>
                                </label>
                            </div>

                            <footer class="shipping-label-form-footer">
                                <button class="button button--primary" type="submit" data-submit-text="Saving..."><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Update Location QR</button>
                                @if ($mapsUrl)
                                    <a class="button button--secondary" href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Open Google Maps</a>
                                @else
                                    <span class="button button--secondary is-disabled" aria-disabled="true"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Open Google Maps</span>
                                @endif
                            </footer>
                        </section>
                    </form>

                    <section class="shipping-label-workspace-card shipping-label-history">
                        <div class="section-heading">
                            <div>
                                <p class="eyebrow">Generation History</p>
                                <h2>Print and label audit trail</h2>
                            </div>
                        </div>
                        @forelse ($label->events as $event)
                            <article>
                                <strong>{{ str($event->event)->replace('_', ' ')->title() }}</strong>
                                <span>{{ $event->user?->name ?? 'System' }} · {{ $event->created_at->format('d M Y, h:i A') }}</span>
                                @if ($event->metadata)
                                    <code>{{ json_encode($event->metadata) }}</code>
                                @endif
                            </article>
                        @empty
                            <p class="lede">No audit events recorded for this label yet.</p>
                        @endforelse
                    </section>
                </aside>
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
