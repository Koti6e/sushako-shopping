@inject('labelService', 'App\Services\ShippingLabelService')

<x-layouts.admin title="Labels - Sushako Admin">
    <x-admin.shell eyebrow="Warehouse Dispatch" title="Labels" subtitle="Generate, print and manage shipping labels.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.orders.index')" tone="secondary" icon="fa-solid fa-receipt">Order Queue</x-admin.action>
        </x-slot:actions>

            <section class="shipping-label-console" data-shipping-label-console>
                @include('admin.shipping-labels.partials.orders-nav')

                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <header class="shipping-label-hero">
                    <div>
                        <p class="eyebrow">Admin / Orders / Labels</p>
                        <h1>Labels</h1>
                        <p>Generate, print and manage shipping labels for warehouse dispatch.</p>
                    </div>
                    <div class="shipping-label-hero__actions" data-shipping-label-bulk data-bulk-url="{{ route('admin.shipping-labels.bulk') }}">
                        <button type="button" data-bulk-action="generate"><i class="fa-solid fa-tags" aria-hidden="true"></i> Generate Labels</button>
                        <button type="button" data-bulk-action="print"><i class="fa-solid fa-print" aria-hidden="true"></i> Bulk Print</button>
                        <button type="button" data-bulk-action="download_zip"><i class="fa-solid fa-file-zipper" aria-hidden="true"></i> Download PDFs</button>
                        <a href="{{ route('admin.shipping-labels.index') }}"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Refresh Queue</a>
                    </div>
                </header>

                <div class="shipping-label-kpis">
                    @foreach ($cards as $card)
                        <a class="shipping-label-kpi shipping-label-kpi--{{ $card['tone'] }}" href="{{ $card['url'] }}" data-filter-link>
                            <span>{{ $card['label'] }}</span>
                            <strong>{{ number_format($card['value']) }}</strong>
                            <small>{{ $card['description'] }}</small>
                        </a>
                    @endforeach
                </div>

                <form class="shipping-label-filters" method="GET" action="{{ route('admin.shipping-labels.index') }}" data-live-filters>
                    <label>Search Order<input name="search_order" value="{{ $filters['search_order'] ?? '' }}" placeholder="Order number"></label>
                    <label>Search Customer<input name="search_customer" value="{{ $filters['search_customer'] ?? '' }}" placeholder="Name, phone, city, PIN"></label>
                    <label>Seller<input name="seller" value="{{ $filters['seller'] ?? '' }}" placeholder="Seller name"></label>
                    <label>Warehouse<input name="warehouse" value="{{ $filters['warehouse'] ?? '' }}" placeholder="Warehouse"></label>
                    <label>Courier
                        <select name="courier">
                            <option value="">Any</option>
                            @foreach ($shippingProviders as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['courier'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Payment
                        <select name="payment">
                            <option value="">Any</option>
                            <option value="prepaid" @selected(($filters['payment'] ?? '') === 'prepaid')>Prepaid</option>
                            <option value="cod" @selected(($filters['payment'] ?? '') === 'cod')>COD</option>
                        </select>
                    </label>
                    <label>Status
                        <select name="status">
                            <option value="">Any</option>
                            @foreach (['pending', 'generated', 'printed', 'cancelled', 'regenerated', 'archived'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Print
                        <select name="print_status">
                            <option value="">Any</option>
                            <option value="unprinted" @selected(($filters['print_status'] ?? '') === 'unprinted')>Not Printed</option>
                            <option value="printed" @selected(($filters['print_status'] ?? '') === 'printed')>Printed</option>
                        </select>
                    </label>
                    <label>Date From<input name="date_from" type="date" value="{{ $filters['date_from'] ?? ($filters['date'] ?? '') }}"></label>
                    <label>Date To<input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></label>
                    <input type="hidden" name="location" value="{{ $filters['location'] ?? '' }}">
                    <a class="shipping-label-reset" href="{{ route('admin.shipping-labels.index') }}" data-filter-link><i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i> Reset Filters</a>
                </form>

                <div class="shipping-label-layout">
                    <div class="shipping-label-workbench">
                        <section class="shipping-label-bulk" data-shipping-label-bulk data-bulk-url="{{ route('admin.shipping-labels.bulk') }}" hidden>
                            <div>
                                <strong><span data-selected-count>0</span> Orders</strong>
                                <span data-bulk-status>Selected</span>
                            </div>
                            <div>
                                <button type="button" data-bulk-action="generate"><i class="fa-solid fa-tags" aria-hidden="true"></i> Generate</button>
                                <button type="button" data-bulk-action="print"><i class="fa-solid fa-print" aria-hidden="true"></i> Print</button>
                                <button type="button" data-bulk-action="download_zip"><i class="fa-solid fa-download" aria-hidden="true"></i> Download</button>
                                <button type="button" data-bulk-action="mark_printed"><i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark Printed</button>
                                <button type="button" data-clear-selection><i class="fa-solid fa-xmark" aria-hidden="true"></i> Clear Selection</button>
                            </div>
                        </section>

                        <div class="shipping-label-table-wrap">
                            <table class="shipping-label-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" data-select-all-labels aria-label="Select all visible orders"></th>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Seller</th>
                                        <th>Payment</th>
                                        <th>Package</th>
                                        <th>Weight</th>
                                        <th>Location</th>
                                        <th>Label Status</th>
                                        <th>Print Status</th>
                                        <th>Generated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($orders as $order)
                                        @php
                                            $label = $order->latestShippingLabel;
                                            $courier = $label?->courier_name ?: ($order->shipping_provider_other ?: ($shippingProviders[$order->shipping_provider] ?? 'Not selected'));
                                            $payment = $order->payment_method === 'cod' ? 'COD' : 'PREPAID';
                                            $hasLocation = filled($label?->location_qr_url) || filled($order->delivery_location_url) || (filled($order->delivery_latitude) && filled($order->delivery_longitude));
                                            $mapsUrl = $label?->location_qr_url ?: $labelService->mapsUrl($order);
                                            $itemCount = $order->items->sum('quantity');
                                            $packageCount = $label?->package_count ?? 1;
                                            $weight = $label?->weight_grams;
                                            $priority = in_array($order->shipping_provider, ['rapido', 'uber'], true) ? 'express' : ($order->status === 'placed' ? 'standard' : 'scheduled');
                                            $printTone = ! $label || $label->print_count === 0 ? 'none' : ($label->print_count === 1 ? 'once' : 'multiple');
                                            $generatedLabel = $label?->generatedBy?->name ?? 'Not generated';
                                            $generatedTime = $label?->generated_at?->format('d M, h:i A') ?? '-';
                                        @endphp
                                        <tr data-row-href="{{ route('admin.orders.show', $order->order_number) }}">
                                            <td><input type="checkbox" value="{{ $order->id }}" data-label-order-checkbox aria-label="Select {{ $order->order_number }}"></td>
                                            <td>
                                                <a class="shipping-order-cell" href="{{ route('admin.orders.show', $order->order_number) }}">
                                                    <strong>{{ $order->order_number }}</strong>
                                                    <span>{{ $order->placed_at?->format('d M Y, h:i A') ?? $order->created_at->format('d M Y, h:i A') }}</span>
                                                    <em class="shipping-priority shipping-priority--{{ $priority }}">{{ strtoupper($priority) }}</em>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="shipping-stack">
                                                    <strong>{{ $order->customer_name }}</strong>
                                                    <span>{{ $order->customer_phone }}</span>
                                                    <span>{{ $order->city ?: 'City missing' }} · {{ $order->pincode ?: 'PIN missing' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="shipping-seller-cell">
                                                    @if ($label?->seller_logo_path)
                                                        <img src="{{ asset('storage/'.$label->seller_logo_path) }}" alt="">
                                                    @else
                                                        <span aria-hidden="true"><i class="fa-solid fa-store"></i></span>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $label?->seller_name ?: 'Sushako Shopping' }}</strong>
                                                        <small>{{ $label?->warehouse_name ?: 'Sushako Dispatch' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="shipping-payment-badge shipping-payment-badge--{{ strtolower($payment) }}">{{ $payment }}</span>
                                                @if ($payment === 'COD')
                                                    <small class="shipping-money">₹{{ number_format($order->total_amount, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="shipping-stack">
                                                    <strong>{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }}</strong>
                                                    <span>{{ $packageCount }} package{{ $packageCount === 1 ? '' : 's' }}</span>
                                                    <span>Dimensions: Not set</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="shipping-stack">
                                                    <strong>{{ $weight ? number_format($weight / 1000, 2).' kg' : 'Not set' }}</strong>
                                                    <span>{{ $weight ? number_format(($weight + 100) / 1000, 2).' kg shipping' : 'Shipping not set' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($hasLocation)
                                                    <div class="shipping-location-cell">
                                                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                                        <span>{{ $label?->location_qr_url ? 'QR Ready' : 'Map Ready' }}</span>
                                                        <small>{{ $order->location_confirmed ? 'Confirmed' : 'Needs Confirm' }}</small>
                                                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Open maps for {{ $order->order_number }}"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Open</a>
                                                    </div>
                                                @else
                                                    <div class="shipping-location-cell shipping-location-cell--missing">
                                                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                                        <span>Location Missing</span>
                                                        <small>QR unavailable</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><span class="shipping-label-status shipping-label-status--{{ $label?->status ?? 'pending' }}">{{ str($label?->status ?? 'pending')->replace('_', ' ')->title() }}</span></td>
                                            <td><span class="shipping-print-status shipping-print-status--{{ $printTone }}">{{ ! $label || $label->print_count === 0 ? 'Not Printed' : ($label->print_count === 1 ? 'Printed Once' : 'Printed Multiple Times') }} · {{ $label?->print_count ?? 0 }}</span></td>
                                            <td>
                                                <div class="shipping-stack">
                                                    <strong>{{ $generatedLabel }}</strong>
                                                    <span>{{ $generatedTime }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="shipping-label-actions">
                                                    @if ($label)
                                                        <a href="{{ route('admin.shipping-labels.show', $label) }}" title="View" aria-label="View {{ $label->label_number }}"><i class="fa-regular fa-eye"></i></a>
                                                        <button type="button" title="Preview" aria-label="Preview {{ $label->label_number }}" data-preview-label="label-preview-{{ $label->id }}"><i class="fa-solid fa-qrcode"></i></button>
                                                        <form method="POST" action="{{ route('admin.shipping-labels.print', $label) }}" target="_blank">@csrf<button type="submit" title="Print" aria-label="Print {{ $label->label_number }}"><i class="fa-solid fa-print"></i></button></form>
                                                        <a href="{{ route('admin.shipping-labels.pdf', $label) }}" title="Download PDF" aria-label="Download PDF {{ $label->label_number }}"><i class="fa-solid fa-file-pdf"></i></a>
                                                        @if ($mapsUrl)
                                                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" title="Open Maps" aria-label="Open maps {{ $label->label_number }}"><i class="fa-solid fa-map-location-dot"></i></a>
                                                        @endif
                                                        <details class="shipping-more-menu">
                                                            <summary aria-label="More actions"><i class="fa-solid fa-ellipsis-vertical"></i></summary>
                                                            <form method="POST" action="{{ route('admin.shipping-labels.regenerate', $label) }}">@csrf<button type="submit">Regenerate</button></form>
                                                        </details>
                                                    @else
                                                        <form method="POST" action="{{ route('admin.shipping-labels.generate', $order->order_number) }}">
                                                            @csrf
                                                            <input type="hidden" name="brand_mode" value="sushako">
                                                            <input type="hidden" name="fulfillment_type" value="fulfilled_by_sushako">
                                                            <button type="submit" title="Generate" aria-label="Generate label for {{ $order->order_number }}"><i class="fa-solid fa-tags"></i></button>
                                                        </form>
                                                        @if ($mapsUrl)
                                                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" title="Open Maps" aria-label="Open maps {{ $order->order_number }}"><i class="fa-solid fa-map-location-dot"></i></a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="12">No orders match the selected label filters.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="shipping-label-mobile-list">
                            @foreach ($orders as $order)
                                @php
                                    $label = $order->latestShippingLabel;
                                    $payment = $order->payment_method === 'cod' ? 'COD' : 'PREPAID';
                                    $hasLocation = filled($label?->location_qr_url) || filled($order->delivery_location_url) || (filled($order->delivery_latitude) && filled($order->delivery_longitude));
                                @endphp
                                <article>
                                    <label><input type="checkbox" value="{{ $order->id }}" data-label-order-checkbox> {{ $order->order_number }}</label>
                                    <strong>{{ $order->customer_name }}</strong>
                                    <span>{{ $order->customer_phone }} · {{ $order->city }} {{ $order->pincode }}</span>
                                    <div>
                                        <span class="shipping-payment-badge shipping-payment-badge--{{ strtolower($payment) }}">{{ $payment }}</span>
                                        <span class="shipping-label-status shipping-label-status--{{ $label?->status ?? 'pending' }}">{{ str($label?->status ?? 'pending')->replace('_', ' ')->title() }}</span>
                                        <span class="shipping-print-status shipping-print-status--{{ ! $label || $label->print_count === 0 ? 'none' : ($label->print_count === 1 ? 'once' : 'multiple') }}">{{ $label?->print_count ?? 0 }} prints</span>
                                    </div>
                                    <small>{{ $hasLocation ? 'Location available' : 'Location Missing' }}</small>
                                </article>
                            @endforeach
                        </div>

                        {{ $orders->links() }}
                    </div>

                    <aside class="shipping-label-summary">
                        <header>
                            <span>Today's Progress</span>
                            <strong>{{ $summary['warehouseEfficiency'] }}%</strong>
                        </header>
                        <dl>
                            <div><dt>Labels Generated</dt><dd>{{ number_format($summary['labelsGenerated']) }}</dd></div>
                            <div><dt>Labels Printed</dt><dd>{{ number_format($summary['labelsPrinted']) }}</dd></div>
                            <div><dt>Pending Dispatch</dt><dd>{{ number_format($summary['pendingDispatch']) }}</dd></div>
                            <div><dt>Average Processing Time</dt><dd>{{ $summary['averageProcessingTime'] }} min</dd></div>
                            <div><dt>Warehouse Efficiency</dt><dd>{{ $summary['warehouseEfficiency'] }}%</dd></div>
                        </dl>
                        <section>
                            <h2>Recent Activity</h2>
                            @forelse ($summary['recentActivity'] as $activity)
                                <article>
                                    <strong>{{ $activity->label_number }}</strong>
                                    <span>{{ str($activity->status)->replace('_', ' ')->title() }} · {{ $activity->order?->order_number }}</span>
                                    <small>{{ $activity->updated_at->diffForHumans() }}</small>
                                </article>
                            @empty
                                <p>No label activity yet.</p>
                            @endforelse
                        </section>
                    </aside>
                </div>

                <aside class="shipping-label-drawer" data-label-preview-drawer aria-hidden="true">
                    <div class="shipping-label-drawer__panel" role="dialog" aria-modal="true" aria-label="Shipping label preview">
                        <header>
                            <div>
                                <span>Label Preview</span>
                                <strong data-label-preview-title>Ready</strong>
                            </div>
                            <button type="button" data-close-label-preview aria-label="Close preview"><i class="fa-solid fa-xmark"></i></button>
                        </header>
                        <div class="shipping-label-drawer__toolbar">
                            <button type="button" data-label-zoom="out" aria-label="Zoom out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                            <button type="button" data-label-zoom="in" aria-label="Zoom in"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                            <form method="POST" action="#" target="_blank" data-label-print-form>
                                @csrf
                                <button type="submit"><i class="fa-solid fa-print"></i> Print</button>
                            </form>
                            <a data-label-download href="#"><i class="fa-solid fa-file-pdf"></i> Download</a>
                            <a data-label-maps href="#" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot"></i> Maps</a>
                        </div>
                        <div class="shipping-label-drawer__canvas" data-label-preview-canvas></div>
                    </div>
                </aside>

                <div hidden>
                    @foreach ($orders as $order)
                        @php
                            $label = $order->latestShippingLabel;
                            $mapsUrl = $label?->location_qr_url ?: $labelService->mapsUrl($order);
                        @endphp
                        @if ($label)
                            <template id="label-preview-{{ $label->id }}" data-title="{{ $label->label_number }}" data-print="{{ route('admin.shipping-labels.print', $label) }}" data-download="{{ route('admin.shipping-labels.pdf', $label) }}" data-maps="{{ $mapsUrl }}">
                                @include('admin.shipping-labels.template', [
                                    'label' => $label,
                                    'order' => $order,
                                    'mapsUrl' => $mapsUrl,
                                    'locationQr' => $label->location_qr_url ? $labelService->codeSvgDataUri($label->location_qr_url, 'qr') : null,
                                    'internalBarcode' => $labelService->codeSvgDataUri($label->internal_lookup_code, 'barcode'),
                                    'internalQr' => $labelService->codeSvgDataUri($label->internal_lookup_code, 'qr'),
                                    'logoData' => null,
                                ])
                            </template>
                        @endif
                    @endforeach
                </div>
            </section>
    </x-admin.shell>
</x-layouts.admin>
