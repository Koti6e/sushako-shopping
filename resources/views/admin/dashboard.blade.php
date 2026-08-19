<x-layouts.admin title="Admin Dashboard - Sushako Shopping">
    <x-admin.shell title="Dashboard">
        <x-slot:actions>
            <x-admin.action :href="route('admin.approvals.index')" tone="secondary" icon="fa-solid fa-inbox">Approval Center</x-admin.action>
            <x-admin.action :href="route('admin.orders.index')" tone="primary" icon="fa-solid fa-receipt">Open Order Queue</x-admin.action>
        </x-slot:actions>

        <section class="admin-control-dashboard">
            <span class="sr-only">Marketplace Dashboard</span>

            <div class="admin-kpi-grid">
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__icon" style="background: rgba(46, 160, 105, 0.1); color: #2ea069;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div class="admin-kpi-card__data">
                        <small>Total Revenue</small>
                        <strong>Rs {{ number_format($storeCounts['revenue']) }}</strong>
                    </div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div class="admin-kpi-card__data">
                        <small>Pending Orders</small>
                        <strong>{{ number_format($storeCounts['pending_orders']) }}</strong>
                    </div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="admin-kpi-card__data">
                        <small>Inventory Alerts</small>
                        <strong>{{ number_format($storeCounts['inventory_alerts']) }}</strong>
                    </div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="admin-kpi-card__data">
                        <small>Customers</small>
                        <strong>{{ number_format($storeCounts['customers']) }}</strong>
                    </div>
                </article>
            </div>

            <div class="admin-control-dashboard__grid">
                <section class="admin-enterprise-card admin-enterprise-card--wide">
                    <div class="admin-enterprise-card__head">
                        <div>
                            <p class="eyebrow">Action Required</p>
                            <h2>Today's Priorities</h2>
                        </div>
                        <a href="{{ route('admin.approvals.index') }}" class="btn-link">Go to inbox &rarr;</a>
                    </div>
                    <div class="admin-priority-list">
                        @forelse ($priorities as $priority)
                            <a href="{{ $priority['url'] }}" class="priority-item">
                                <span class="priority-count">{{ $priority['count'] }}</span>
                                <span class="priority-details">
                                    <strong>{{ $priority['label'] }}</strong>
                                    <small>{{ $priority['description'] }}</small>
                                </span>
                                <i class="fa-solid fa-chevron-right text-muted"></i>
                            </a>
                        @empty
                            <div class="empty-state-minimal">
                                <i class="fa-solid fa-check-circle" style="color: #2ea069;"></i>
                                <p>No urgent marketplace priorities right now. You are all caught up.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="admin-enterprise-card admin-enterprise-card--wide">
                    <div class="admin-enterprise-card__head">
                        <div>
                            <p class="eyebrow">Order Queue</p>
                            <h2>Recent Customer Orders</h2>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="btn-link">View all orders &rarr;</a>
                    </div>
                    <div class="admin-order-queue-list">
                        @forelse ($recentOrders as $order)
                            <a href="{{ route('admin.orders.show', $order->order_number) }}" class="order-list-card">
                                <div class="order-list-card__main">
                                    <strong>{{ $order->order_number }}</strong>
                                    <small>{{ $order->customer_name }} &bull; {{ $order->customer_phone }}</small>
                                </div>
                                <div class="order-list-card__status">
                                    <span class="status-badge status-{{ $order->status }}">{{ str($order->status)->replace('_', ' ')->title() }}</span>
                                </div>
                                <div class="order-list-card__amount">
                                    <b>Rs {{ number_format($order->total_amount) }}</b>
                                </div>
                            </a>
                        @empty
                            <div class="empty-state-minimal">
                                <p>No recent orders. They will appear here instantly when placed.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
                
                <section class="admin-enterprise-card">
                    <div class="admin-enterprise-card__head">
                        <div>
                            <p class="eyebrow">Approval Center</p>
                            <h2>Pending Reviews</h2>
                        </div>
                    </div>
                    <div class="admin-approval-strip">
                        @foreach ($approvals as $queue)
                            <a href="{{ $queue['url'] }}" class="approval-strip-item">
                                <strong>{{ $queue['count'] }}</strong>
                                <span>{{ $queue['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
