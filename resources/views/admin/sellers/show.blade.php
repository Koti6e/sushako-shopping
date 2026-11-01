<x-layouts.admin title="{{ $vendor->business_name }} - Seller Profile">
    <x-admin.shell eyebrow="Seller Management" :title="$vendor->business_name" :subtitle="$vendor->email.' · '.$vendor->phone" :badge="str($vendor->status)->replace('_', ' ')->title()">
        <x-slot:actions>
            <x-admin.action :href="route('admin.sellers.index')" tone="secondary" icon="fa-solid fa-arrow-left">All Sellers</x-admin.action>
            <x-admin.action :href="route('admin.settlements.index', ['seller' => $vendor->id])" tone="primary" icon="fa-solid fa-indian-rupee-sign">Settlement History</x-admin.action>
        </x-slot:actions>

        <section class="admin-control-page admin-seller-profile">
            <div class="admin-seller-profile__summary">
                <article>
                    <span><i class="fa-solid fa-store" aria-hidden="true"></i></span>
                    <div>
                        <p>Store Completion</p>
                        <strong>{{ $vendor->onboarding_status === 'complete' ? '100%' : 'In Progress' }}</strong>
                    </div>
                </article>
                <article>
                    <span><i class="fa-solid fa-box" aria-hidden="true"></i></span>
                    <div><p>Products</p><strong>{{ $vendor->products_count }}</strong></div>
                </article>
                <article>
                    <span><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
                    <div><p>Orders</p><strong>{{ $ordersCount }}</strong></div>
                </article>
                <article>
                    <span><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
                    <div><p>Revenue</p><strong>Rs {{ number_format($grossRevenue) }}</strong></div>
                </article>
            </div>

            <div class="admin-seller-profile__grid">
                <article class="admin-enterprise-card admin-seller-profile__business-card">
                    <h2>Business Details</h2>
                    <dl>
                        <div><dt>Legal Name</dt><dd>{{ $vendor->legal_name ?: 'Not submitted' }}</dd></div>
                        <div><dt>Contact Person</dt><dd>{{ $vendor->owner_name ?: $vendor->user?->name ?: 'Not provided' }}</dd></div>
                        <div><dt>Business Email</dt><dd>{{ $vendor->email ?: $vendor->user?->email ?: 'Not provided' }}</dd></div>
                        <div><dt>Mobile</dt><dd>{{ $vendor->phone ?: 'Not provided' }}</dd></div>
                        <div><dt>WhatsApp</dt><dd>{{ $vendor->store_support_number ?: 'Not provided' }}</dd></div>
                        <div><dt>Website</dt><dd>@if($vendor->website)<a href="{{ $vendor->website }}" target="_blank" rel="noopener">{{ $vendor->website }}</a>@else Not provided @endif</dd></div>
                        <div><dt>Public Store</dt><dd><a href="{{ route('stores.show', $vendor) }}" target="_blank" rel="noopener">{{ route('stores.show', $vendor) }}</a></dd></div>
                        <div><dt>GSTIN</dt><dd>{{ $vendor->gstin ?: 'Not submitted' }}</dd></div>
                        <div><dt>PAN</dt><dd>{{ $vendor->pan_number ? '••••••'.substr($vendor->pan_number, -4) : 'Not submitted' }}</dd></div>
                        <div><dt>Address</dt><dd>{{ collect([$vendor->address_line_1, $vendor->address_line_2, $vendor->city, $vendor->state, $vendor->postal_code])->filter()->join(', ') ?: 'Not submitted' }}</dd></div>
                        <div><dt>Recent Login</dt><dd>{{ $vendor->user?->last_login_at?->format('d M Y, h:i A') ?: 'No login recorded' }}</dd></div>
                    </dl>
                </article>

                <article class="admin-enterprise-card admin-seller-profile__subscription-card">
                    <h2>Seller Access & Verification</h2>
                    <dl>
                        <div><dt>Marketplace access</dt><dd>Free seller access</dd></div>
                        <div><dt>Commission</dt><dd>2% on successful orders</dd></div>
                        <div><dt>Bank</dt><dd>{{ str($vendor->bank_verification_status ?: 'not submitted')->replace('_', ' ')->title() }}</dd></div>
                        <div><dt>Vacation Mode</dt><dd>{{ $vendor->vacation_starts_at ? 'Scheduled' : 'Off' }}</dd></div>
                    </dl>
                </article>

                <article class="admin-enterprise-card admin-seller-profile__quick-card">
                    <h2>Quick Actions</h2>
                    <p class="lede">Pause selling keeps the seller account accessible while hiding its products. Suspension also blocks Seller OS access.</p>
                    <div class="admin-seller-restriction-actions">
                        @if($vendor->status === \App\Models\Vendor::STATUS_ACTIVE && $vendor->store_status === \App\Models\Vendor::STORE_LIVE)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="action" value="pause">
                                <input type="text" name="reason" required minlength="5" maxlength="240" placeholder="Reason for pausing" aria-label="Reason for pausing">
                                <x-ui.button type="submit" variant="secondary">Pause Selling</x-ui.button>
                            </form>
                        @elseif($vendor->status === \App\Models\Vendor::STATUS_ACTIVE && $vendor->store_status === \App\Models\Vendor::STORE_TEMPORARILY_CLOSED)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="action" value="resume">
                                <input type="text" name="reason" required minlength="5" maxlength="240" placeholder="Reason for resuming" aria-label="Reason for resuming">
                                <x-ui.button type="submit" variant="primary">Resume Selling</x-ui.button>
                            </form>
                        @endif
                        @if($vendor->status === \App\Models\Vendor::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="action" value="suspend">
                                <input type="text" name="reason" required minlength="5" maxlength="240" placeholder="Reason for suspension" aria-label="Reason for suspension">
                                <x-ui.button type="submit" variant="danger">Suspend Account</x-ui.button>
                            </form>
                        @elseif($vendor->status === \App\Models\Vendor::STATUS_SUSPENDED)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="action" value="reinstate">
                                <input type="text" name="reason" required minlength="5" maxlength="240" placeholder="Reason for reinstatement" aria-label="Reason for reinstatement">
                                <x-ui.button type="submit" variant="primary">Reinstate Account</x-ui.button>
                            </form>
                        @endif
                    </div>
                </article>

                <article class="admin-enterprise-card admin-seller-profile__contact-card">
                    <h2>Contact Actions</h2>
                    <div class="admin-seller-contact-actions">
                        @if($vendor->phone)<a href="tel:{{ $vendor->phone }}">Call seller</a>@endif
                        @if($vendor->store_support_number)<a href="https://wa.me/{{ preg_replace('/\D+/', '', $vendor->store_support_number) }}" target="_blank" rel="noopener">WhatsApp</a>@endif
                        @if($vendor->email)<a href="mailto:{{ $vendor->email }}">Email</a>@endif
                        @if(!$vendor->phone && !$vendor->store_support_number && !$vendor->email)<span>Not provided</span>@endif
                    </div>
                </article>

                <article class="admin-enterprise-card">
                    <h2>Recent Products</h2>
                    @forelse ($vendor->products as $product)
                        <a class="admin-enterprise-row" href="{{ route('admin.products.edit', $product->slug) }}">
                            <strong>{{ $product->name }}</strong>
                            <span>{{ str($product->seller_status ?: 'active')->replace('_', ' ')->title() }} · Rs {{ number_format($product->selling_price) }}</span>
                        </a>
                    @empty
                        <p>No products added yet.</p>
                    @endforelse
                </article>

                <article class="admin-enterprise-card">
                    <h2>Settlement History</h2>
                    @forelse ($vendor->settlements as $settlement)
                        <a class="admin-enterprise-row" href="{{ route('admin.settlements.show', $settlement) }}">
                            <strong>{{ $settlement->settlement_number }}</strong>
                            <span>{{ str($settlement->status)->replace('_', ' ')->title() }} · Rs {{ number_format((float) $settlement->final_settlement_amount) }}</span>
                        </a>
                    @empty
                        <p>No settlement batches yet.</p>
                    @endforelse
                </article>

                <article class="admin-enterprise-card">
                    <h2>Audit Trail</h2>
                    @forelse ($vendor->audits as $audit)
                        <div class="admin-enterprise-row">
                            <strong>{{ str($audit->action)->replace('_', ' ')->title() }}</strong>
                            <span>{{ $audit->reason ?: 'No reason' }} · {{ $audit->created_at?->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p>No admin actions recorded yet.</p>
                    @endforelse
                </article>
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
