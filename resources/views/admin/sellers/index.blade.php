@inject('accounts', 'App\Services\SellerAccountService')
@php
    $money = fn ($amount): string => '₹'.number_format((float) $amount, 0);
    $label = fn ($value): string => str((string) ($value ?: 'not set'))->replace('_', ' ')->title()->toString();
    $query = fn (array $changes = []): array => array_filter(array_merge(request()->query(), $changes), fn ($value) => filled($value));
    $statusTone = [
        \App\Models\Vendor::STATUS_ACTIVE => 'success',
        \App\Models\Vendor::STATUS_SUSPENDED => 'danger',
        \App\Models\Vendor::STATUS_REJECTED => 'danger',
        \App\Models\Vendor::STATUS_INACTIVE => 'warning',
        \App\Models\Vendor::STATUS_CLOSED => 'neutral',
    ];
@endphp

<x-layouts.admin title="Sellers - Admin">
    <x-admin.shell eyebrow="Seller Platform" title="Seller Management" subtitle="Review seller readiness, plan health, payments, settlements and store activation from one operations surface.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.sellers.commissions.index')" tone="secondary" icon="fa-solid fa-percent">Commission Rules</x-admin.action>
            <x-admin.action :href="route('admin.sellers.banners.index')" tone="primary" icon="fa-solid fa-images">Seller Banners</x-admin.action>
        </x-slot:actions>

        <section class="admin-control-page admin-sellers-workspace">
            <div class="admin-control-metrics admin-sellers-metrics">
                <a href="{{ route('admin.sellers.index') }}"><span>Total sellers</span><strong>{{ number_format($summary['total']) }}</strong></a>
                <a href="{{ route('admin.sellers.index', ['filter' => 'approval_pending']) }}"><span>Awaiting approval</span><strong>{{ number_format($summary['approval_pending']) }}</strong></a>
                <a href="{{ route('admin.sellers.index', ['filter' => 'onboarding_incomplete']) }}"><span>Setup incomplete</span><strong>{{ number_format($summary['setup_incomplete']) }}</strong></a>
                <a href="{{ route('admin.sellers.index', ['filter' => 'bank_pending']) }}"><span>Bank review</span><strong>{{ number_format($summary['bank_pending']) }}</strong></a>
                <a href="{{ route('admin.sellers.index', ['status' => \App\Models\Vendor::STATUS_ACTIVE]) }}"><span>Active stores</span><strong>{{ number_format($summary['active']) }}</strong></a>
                <a href="{{ route('admin.sellers.index', ['filter' => 'payment_pending']) }}"><span>Payment attention</span><strong>{{ number_format($summary['payment_attention']) }}</strong></a>
            </div>

            <form class="admin-seller-toolbar" method="GET" action="{{ route('admin.sellers.index') }}">
                <input type="hidden" name="view" value="{{ $view }}">
                <label class="admin-seller-toolbar__search">
                    <span>Search sellers</span>
                    <input name="search" value="{{ $filters['search'] }}" placeholder="Business, owner, email, phone or seller ID">
                </label>
                <label>
                    <span>Status</span>
                    <select name="status">
                        <option value="">Any status</option>
                        @foreach ([\App\Models\Vendor::STATUS_ACTIVE, \App\Models\Vendor::STATUS_INACTIVE, \App\Models\Vendor::STATUS_SUSPENDED, \App\Models\Vendor::STATUS_REJECTED, \App\Models\Vendor::STATUS_CLOSED] as $option)
                            <option value="{{ $option }}" @selected($filters['status'] === $option)>{{ $label($option) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Plan</span>
                    <select name="plan">
                        <option value="">Any plan</option>
                        @foreach ([\App\Models\Vendor::PLAN_FREE, \App\Models\Vendor::PLAN_GROWTH, \App\Models\Vendor::PLAN_ENTERPRISE] as $option)
                            <option value="{{ $option }}" @selected($filters['plan'] === $option)>{{ $label($option) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Store state</span>
                    <select name="store_status">
                        <option value="">Any state</option>
                        @foreach ([\App\Models\Vendor::STORE_SETUP_REQUIRED, \App\Models\Vendor::STORE_READY, \App\Models\Vendor::STORE_LIVE, \App\Models\Vendor::STORE_TEMPORARILY_CLOSED, \App\Models\Vendor::STORE_SUSPENDED] as $option)
                            <option value="{{ $option }}" @selected($filters['store_status'] === $option)>{{ $label($option) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Location</span>
                    <input name="location" value="{{ $filters['location'] }}" placeholder="City, state or pincode">
                </label>
                <label>
                    <span>Sort</span>
                    <select name="sort">
                        <option value="latest" @selected($sort === 'latest')>Newest first</option>
                        <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                        <option value="sales" @selected($sort === 'sales')>Sales high to low</option>
                        <option value="products" @selected($sort === 'products')>Products high to low</option>
                        <option value="completion" @selected($sort === 'completion')>Setup progress</option>
                    </select>
                </label>
                <div class="admin-seller-toolbar__actions">
                    <button type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i>Apply</button>
                    <a href="{{ route('admin.sellers.index') }}">Clear</a>
                </div>
            </form>

            <div class="admin-seller-viewbar">
                <div>
                    <strong>{{ number_format($vendors->total()) }} sellers</strong>
                    <span>Showing {{ number_format($vendors->firstItem() ?? 0) }}-{{ number_format($vendors->lastItem() ?? 0) }}</span>
                </div>
                <nav aria-label="Seller view mode">
                    <a href="{{ route('admin.sellers.index', $query(['view' => 'cards'])) }}" @class(['is-active' => $view === 'cards'])><i class="fa-solid fa-grip" aria-hidden="true"></i>Cards</a>
                    <a href="{{ route('admin.sellers.index', $query(['view' => 'list'])) }}" @class(['is-active' => $view === 'list'])><i class="fa-solid fa-table-list" aria-hidden="true"></i>List</a>
                </nav>
            </div>

            @if($vendors->isEmpty())
                <x-ui.empty-state icon="fa-solid fa-users-slash" title="No sellers found" description="Adjust the filters or clear search to review all marketplace sellers." />
            @elseif($view === 'list')
                <section class="admin-seller-list" aria-label="Seller list">
                    <div class="admin-seller-list__row admin-seller-list__row--head">
                        <span>Seller</span>
                        <span>Location</span>
                        <span>Plan</span>
                        <span>Status</span>
                        <span>Products</span>
                        <span>Sales</span>
                        <span>Settlement</span>
                        <span>Actions</span>
                    </div>
                    @foreach ($vendors as $vendor)
                        @php
                            $readiness = $accounts->readiness($vendor);
                            $snapshot = $vendor->selected_plan_snapshot ?: [];
                            $planName = $vendor->selectedSellerPlan?->name ?: data_get($snapshot, 'name', $label($vendor->selected_plan_slug ?: $vendor->selected_plan ?: $vendor->current_plan));
                            $locationText = collect([$vendor->city, $vendor->state, $vendor->postal_code])->filter()->join(', ') ?: 'Location missing';
                        @endphp
                        <article class="admin-seller-list__row">
                            <a class="admin-seller-identity" href="{{ route('admin.sellers.show', $vendor) }}">
                                <span>{{ str($vendor->business_name ?: 'S')->substr(0, 1)->upper() }}</span>
                                <strong>{{ $vendor->business_name ?: 'Unnamed seller' }}<small>{{ $vendor->user?->name ?: 'Owner missing' }} · #{{ $vendor->id }}</small></strong>
                            </a>
                            <span>{{ $locationText }}</span>
                            <span>{{ $planName }}</span>
                            <span><x-ui.badge :status="$statusTone[$vendor->status] ?? 'neutral'">{{ $label($vendor->status) }}</x-ui.badge><small>{{ $readiness['percentage'] }}% setup</small></span>
                            <span>{{ number_format($vendor->products_count) }}<small>{{ number_format($vendor->active_products_count) }} active</small></span>
                            <span>{{ $money($vendor->gross_sales) }}<small>{{ $money($vendor->commission_total) }} commission</small></span>
                            <span>{{ $money($vendor->pending_settlement_total) }}</span>
                            <span class="admin-seller-row-actions">
                                <a href="{{ route('admin.sellers.show', $vendor) }}" aria-label="View {{ $vendor->business_name }}">View</a>
                            </span>
                        </article>
                    @endforeach
                </section>
            @else
                <section class="admin-seller-grid" aria-label="Seller cards">
                    @foreach ($vendors as $vendor)
                        @php
                            $payment = $vendor->onboardingPayments->first();
                            $snapshot = $vendor->selected_plan_snapshot ?: [];
                            $planName = $vendor->selectedSellerPlan?->name ?: data_get($snapshot, 'name', $label($vendor->selected_plan_slug ?: $vendor->selected_plan ?: $vendor->current_plan));
                            $readiness = $accounts->readiness($vendor);
                            $missing = array_slice($readiness['missing_mandatory'], 0, 3);
                            $locationText = collect([$vendor->city, $vendor->state, $vendor->postal_code])->filter()->join(', ') ?: 'Location missing';
                        @endphp
                        <article class="admin-seller-card">
                            <header>
                                <a class="admin-seller-identity" href="{{ route('admin.sellers.show', $vendor) }}">
                                    <span>{{ str($vendor->business_name ?: 'S')->substr(0, 1)->upper() }}</span>
                                    <strong>{{ $vendor->business_name ?: 'Unnamed seller' }}<small>{{ $vendor->user?->name ?: 'Owner missing' }} · #{{ $vendor->id }}</small></strong>
                                </a>
                                <x-ui.badge :status="$statusTone[$vendor->status] ?? 'neutral'">{{ $label($vendor->status) }}</x-ui.badge>
                            </header>

                            <div class="admin-seller-card__progress">
                                <p><span>Setup completion</span><strong>{{ $readiness['percentage'] }}%</strong></p>
                                <div><span style="width: {{ $readiness['percentage'] }}%"></span></div>
                                <small>{{ $readiness['publish_eligible'] ? 'Eligible for activation' : 'Missing required setup items' }}</small>
                            </div>

                            <dl class="admin-seller-card__facts">
                                <div><dt>Location</dt><dd>{{ $locationText }}</dd></div>
                                <div><dt>Plan</dt><dd>{{ $planName }}</dd></div>
                                <div><dt>Store</dt><dd>{{ $label($vendor->store_status) }}</dd></div>
                                <div><dt>Approval</dt><dd>{{ $label($vendor->approval_status) }}</dd></div>
                                <div><dt>Products</dt><dd>{{ number_format($vendor->products_count) }} total · {{ number_format($vendor->active_products_count) }} active</dd></div>
                                <div><dt>Sales</dt><dd>{{ $money($vendor->gross_sales) }}</dd></div>
                                <div><dt>Settlement due</dt><dd>{{ $money($vendor->pending_settlement_total) }}</dd></div>
                                <div><dt>Last payment</dt><dd>{{ $payment?->status ? $label($payment->status) : $label($vendor->payment_status ?: 'not started') }}</dd></div>
                            </dl>

                            @if(count($missing) > 0)
                                <div class="admin-seller-card__missing" aria-label="Missing requirements">
                                    @foreach($missing as $item)
                                        <span>{{ $item }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <footer>
                                <a href="{{ route('admin.sellers.show', $vendor) }}">View profile</a>
                                @if($vendor->status === \App\Models\Vendor::STATUS_ACTIVE)
                                    <button type="button" data-open-dialog="suspend-modal-{{ $vendor->id }}">Suspend</button>
                                @elseif($readiness['publish_eligible'])
                                    <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_ACTIVE }}">
                                        <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_LIVE }}">
                                        <input type="hidden" name="reason" value="Admin activated eligible seller">
                                        <button type="submit">Activate</button>
                                    </form>
                                @else
                                    <button type="button" disabled>Activate</button>
                                @endif
                                @if($vendor->status !== \App\Models\Vendor::STATUS_REJECTED && $vendor->status !== \App\Models\Vendor::STATUS_CLOSED)
                                    <button type="button" class="is-danger" data-open-dialog="reject-modal-{{ $vendor->id }}">Reject</button>
                                @endif
                            </footer>
                        </article>
                    @endforeach
                </section>
            @endif

            <div class="admin-seller-pagination">
                {{ $vendors->links() }}
            </div>
        </section>

        @foreach ($vendors as $vendor)
            @if($vendor->status === \App\Models\Vendor::STATUS_ACTIVE)
                <dialog id="suspend-modal-{{ $vendor->id }}" class="admin-seller-dialog">
                    <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_SUSPENDED }}">
                        <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_SUSPENDED }}">
                        <h2>Suspend {{ $vendor->business_name }}</h2>
                        <p>The seller store will stop accepting marketplace operations until restored.</p>
                        <label>Reason<input type="text" name="reason" required minlength="5" placeholder="Policy violation, inactive operations, etc."></label>
                        <div>
                            <button type="button" data-close-dialog>Cancel</button>
                            <button type="submit" class="is-danger">Confirm suspend</button>
                        </div>
                    </form>
                </dialog>
            @endif

            @if($vendor->status !== \App\Models\Vendor::STATUS_REJECTED && $vendor->status !== \App\Models\Vendor::STATUS_CLOSED)
                <dialog id="reject-modal-{{ $vendor->id }}" class="admin-seller-dialog">
                    <form method="POST" action="{{ route('admin.sellers.status.update', $vendor) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ \App\Models\Vendor::STATUS_REJECTED }}">
                        <input type="hidden" name="store_status" value="{{ \App\Models\Vendor::STORE_SUSPENDED }}">
                        <h2>Reject {{ $vendor->business_name }}</h2>
                        <p>Rejecting this seller will halt onboarding and keep the storefront unavailable.</p>
                        <label>Reason<input type="text" name="reason" required minlength="5" placeholder="Incomplete documents, invalid business, etc."></label>
                        <div>
                            <button type="button" data-close-dialog>Cancel</button>
                            <button type="submit" class="is-danger">Confirm reject</button>
                        </div>
                    </form>
                </dialog>
            @endif
        @endforeach

        <script>
            document.addEventListener('click', (event) => {
                const opener = event.target.closest('[data-open-dialog]');
                if (opener) {
                    document.getElementById(opener.dataset.openDialog)?.showModal();
                    return;
                }

                if (event.target.closest('[data-close-dialog]')) {
                    event.target.closest('dialog')?.close();
                }
            });
        </script>
    </x-admin.shell>
</x-layouts.admin>
