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
                <article class="admin-enterprise-card">
                    <h2>Business Details</h2>
                    <dl>
                        <div><dt>Legal Name</dt><dd>{{ $vendor->legal_name ?: 'Not submitted' }}</dd></div>
                        <div><dt>GSTIN</dt><dd>{{ $vendor->gstin ?: 'Not submitted' }}</dd></div>
                        <div><dt>PAN</dt><dd>{{ $vendor->pan_number ?: 'Not submitted' }}</dd></div>
                        <div><dt>Address</dt><dd>{{ collect([$vendor->address_line_1, $vendor->address_line_2, $vendor->city, $vendor->state, $vendor->postal_code])->filter()->join(', ') ?: 'Not submitted' }}</dd></div>
                        <div><dt>Recent Login</dt><dd>{{ $vendor->user?->last_login_at?->format('d M Y, h:i A') ?: 'No login recorded' }}</dd></div>
                    </dl>
                </article>

                <article class="admin-enterprise-card">
                    <h2>Subscription & Verification</h2>
                    <dl>
                        <div><dt>Plan</dt><dd>{{ str($vendor->current_plan ?: $vendor->selected_plan ?: 'free')->title() }}</dd></div>
                        <div><dt>Plan Status</dt><dd>{{ str($vendor->plan_status ?: 'pending')->replace('_', ' ')->title() }}</dd></div>
                        <div><dt>Payment</dt><dd>{{ str($vendor->payment_status ?: 'not started')->replace('_', ' ')->title() }}</dd></div>
                        <div><dt>Bank</dt><dd>{{ str($vendor->bank_verification_status ?: 'not submitted')->replace('_', ' ')->title() }}</dd></div>
                        <div><dt>Vacation Mode</dt><dd>{{ $vendor->vacation_starts_at ? 'Scheduled' : 'Off' }}</dd></div>
                    </dl>
                </article>

                <article class="admin-enterprise-card">
                    <h2>Quick Actions</h2>
                    @php
                        $accounts = app(\App\Services\SellerAccountService::class);
                        $readiness = $accounts->readiness($vendor);
                    @endphp
                    
                    @if(!$readiness['publish_eligible'])
                        <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg text-sm border border-red-100">
                            <strong>Missing Activation Requirements:</strong>
                            <ul class="list-disc pl-5 mt-1">
                                @foreach($readiness['missing_mandatory'] as $missing)
                                    <li>{{ $missing }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @if($vendor->status !== \App\Models\Vendor::STATUS_ACTIVE)
                            @if($readiness['publish_eligible'])
                                <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_ACTIVE }}">
                                    <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_LIVE }}">
                                    <input type="hidden" name="reason" value="Admin activated eligible seller from profile">
                                    <x-ui.button type="submit" variant="primary">Activate Seller & Store</x-ui.button>
                                </form>
                            @else
                                <x-ui.button type="button" variant="primary" disabled title="Missing mandatory requirements">Activate Seller & Store</x-ui.button>
                            @endif
                        @endif

                        @if($vendor->status === \App\Models\Vendor::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_SUSPENDED }}">
                                <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_SUSPENDED }}">
                                <input type="text" name="reason" required minlength="5" placeholder="Reason for suspension..." class="border rounded px-2 py-1 text-sm inline-block w-48 mr-2">
                                <x-ui.button type="submit" variant="danger">Suspend Seller</x-ui.button>
                            </form>
                        @endif
                        
                        @if($vendor->status !== \App\Models\Vendor::STATUS_REJECTED && $vendor->status !== \App\Models\Vendor::STATUS_CLOSED)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}" class="inline ml-auto">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_REJECTED }}">
                                <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_SUSPENDED }}">
                                <input type="text" name="reason" required minlength="5" placeholder="Reason for rejection..." class="border rounded px-2 py-1 text-sm inline-block w-48 mr-2">
                                <x-ui.button type="submit" variant="danger">Reject Application</x-ui.button>
                            </form>
                        @endif

                        @if($vendor->status === \App\Models\Vendor::STATUS_REJECTED)
                            <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}" class="inline ml-auto">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_INACTIVE }}">
                                <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_SETUP_REQUIRED }}">
                                <input type="hidden" name="reason" value="Admin reopened rejected application">
                                <x-ui.button type="submit" variant="secondary">Reopen Application</x-ui.button>
                            </form>
                        @endif
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
