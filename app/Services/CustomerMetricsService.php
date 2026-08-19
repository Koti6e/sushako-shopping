<?php

namespace App\Services;

use App\Models\CustomerCart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerMetricsService
{
    public const VALUE_STATUSES = ['placed', 'packing', 'shipped', 'delivered'];

    public function customerQuery(array $filters = []): Builder
    {
        $hasCustomerCarts = $this->hasCustomerCarts();
        $counts = [
            'orders as total_orders_count',
            'orders as cod_orders_count' => fn (Builder $query) => $query->where('payment_method', 'cod'),
            'orders as online_paid_orders_count' => fn (Builder $query) => $query->where('payment_method', 'razorpay')->where('payment_status', 'paid'),
            'orders as cancelled_orders_count' => fn (Builder $query) => $query->where('status', 'cancelled'),
        ];

        if ($hasCustomerCarts) {
            $counts['customerCarts as abandoned_carts_count'] = fn (Builder $query) => $query->whereIn('status', $this->abandonedStatuses());
        }

        $query = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->withCount($counts)
            ->withSum([
                'orders as total_spent' => fn (Builder $query) => $this->valuableOrders($query),
            ], 'total_amount')
            ->addSelect([
                'last_order_at' => Order::query()
                    ->selectRaw('MAX(COALESCE(placed_at, created_at))')
                    ->whereColumn('orders.user_id', 'users.id'),
            ]);

        if (! $hasCustomerCarts) {
            $query->addSelect(['abandoned_carts_count' => DB::raw('0')]);
        }

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? 'latest_activity');

        return $query;
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->customerQuery($filters)->paginate(18)->withQueryString();
    }

    public function summaries(array $baseFilters = []): array
    {
        $customers = User::query()->where('role', User::ROLE_CUSTOMER);
        $total = (clone $customers)->count();
        $repeat = (clone $customers)->whereHas('orders', fn (Builder $query) => $query->select('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1'))->count();
        $active = (clone $customers)->where('status', User::STATUS_ACTIVE)->count();
        $new = (clone $customers)->where('created_at', '>=', now()->startOfMonth())->count();
        $online = (clone $customers)->whereHas('orders', fn (Builder $query) => $query->where('payment_method', 'razorpay')->where('payment_status', 'paid'))->count();
        $cod = (clone $customers)->whereHas('orders', fn (Builder $query) => $query->where('payment_method', 'cod'))->count();
        $cancelled = (clone $customers)->whereHas('orders', fn (Builder $query) => $query->where('status', 'cancelled'))->count();
        $abandoned = $this->hasCustomerCarts()
            ? (clone $customers)->whereHas('customerCarts', fn (Builder $query) => $query->whereIn('status', $this->abandonedStatuses()))->count()
            : 0;

        $onlineRevenue = Order::query()->where('payment_method', 'razorpay')->where('payment_status', 'paid')->sum('total_amount');
        $codDelivered = Order::query()->where('payment_method', 'cod')->where('status', 'delivered')->count();
        $codTotal = max(1, Order::query()->where('payment_method', 'cod')->whereIn('status', self::VALUE_STATUSES)->count());
        $abandonedValue = $this->hasCustomerCarts()
            ? CustomerCart::query()->whereIn('status', $this->abandonedStatuses())->sum('original_value')
            : 0;

        return [
            ['label' => 'Total Customers', 'value' => $total, 'support' => "{$new} joined this month", 'icon' => 'fa-regular fa-user', 'url' => route('admin.customers.index')],
            ['label' => 'New Customers', 'value' => $new, 'support' => 'Registered this month', 'icon' => 'fa-solid fa-user-plus', 'url' => route('admin.customers.index', ['activity' => 'new'])],
            ['label' => 'Repeat Customers', 'value' => $repeat, 'support' => $this->percent($repeat, $total).' of customer base', 'icon' => 'fa-solid fa-repeat', 'url' => route('admin.customers.index', ['type' => 'repeat'])],
            ['label' => 'Active Customers', 'value' => $active, 'support' => $this->percent($active, $total).' currently active', 'icon' => 'fa-solid fa-circle-check', 'url' => route('admin.customers.index', ['status' => User::STATUS_ACTIVE])],
            ['label' => 'Online-Paid Customers', 'value' => $online, 'support' => 'Revenue Rs '.number_format((int) $onlineRevenue), 'icon' => 'fa-solid fa-credit-card', 'url' => route('admin.customers.index', ['payment' => 'online'])],
            ['label' => 'COD Customers', 'value' => $cod, 'support' => $this->percent($codDelivered, $codTotal).' COD delivered', 'icon' => 'fa-solid fa-money-bill-wave', 'url' => route('admin.customers.index', ['payment' => 'cod'])],
            ['label' => 'Cancelled Orders', 'value' => $cancelled, 'support' => 'Customers with cancellations', 'icon' => 'fa-solid fa-ban', 'url' => route('admin.customers.index', ['activity' => 'cancelled'])],
            ['label' => 'Abandoned Carts', 'value' => $abandoned, 'support' => 'Potential Rs '.number_format((int) $abandonedValue), 'icon' => 'fa-solid fa-cart-shopping', 'url' => route('admin.customers.abandoned-carts.index')],
        ];
    }

    public function profile(User $customer): array
    {
        $orders = $customer->orders()->with(['items.vendor', 'items.product.category'])->latest()->paginate(10, ['*'], 'orders_page')->withQueryString();
        $valuableOrders = $customer->orders()->whereIn('status', self::VALUE_STATUSES);
        $totalOrders = $customer->orders()->count();
        $totalSpent = (int) (clone $valuableOrders)->sum('total_amount');
        $onlinePaid = $customer->orders()->where('payment_method', 'razorpay')->where('payment_status', 'paid')->count();
        $cod = $customer->orders()->where('payment_method', 'cod')->count();
        $cancelled = $customer->orders()->where('status', 'cancelled')->count();
        $returnedRefunded = $customer->orders()->whereIn('status', ['returned', 'refunded'])->count();
        $failed = $customer->orders()->where('payment_status', 'failed')->count();
        $hasCustomerCarts = $this->hasCustomerCarts();
        $abandoned = $hasCustomerCarts
            ? $customer->customerCarts()->whereIn('status', $this->abandonedStatuses())->count()
            : 0;
        $lastPayment = $customer->orders()->whereNot('payment_method', 'unselected')->latest()->value('payment_method');
        $codCompleted = $customer->orders()->where('payment_method', 'cod')->where('status', 'delivered')->count();
        $codCancelled = $customer->orders()->where('payment_method', 'cod')->where('status', 'cancelled')->count();
        $vendorStats = OrderItem::query()
            ->selectRaw('vendor_id, COUNT(DISTINCT order_items.order_id) as order_count, SUM(line_total) as spend')
            ->whereHas('order', fn (Builder $query) => $query->where('user_id', $customer->id))
            ->whereNotNull('vendor_id')
            ->with('vendor')
            ->groupBy('vendor_id')
            ->orderByDesc('order_count')
            ->get();
        $categoryStats = OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereHas('order', fn (Builder $query) => $query->where('user_id', $customer->id))
            ->selectRaw('categories.name as category_name, COUNT(*) as item_count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('item_count')
            ->first();

        return [
            'orders' => $orders,
            'addresses' => $customer->addresses()->latest('is_default')->latest()->get(),
            'latestOrder' => $customer->orders()->latest()->first(),
            'activities' => $customer->activities()->latest('occurred_at')->limit(12)->get(),
            'communications' => $customer->communications()->latest()->limit(12)->get(),
            'abandonedCarts' => $hasCustomerCarts
                ? $customer->customerCarts()->with('items')->whereIn('status', $this->abandonedStatuses())->latest('abandoned_at')->limit(3)->get()
                : collect(),
            'summary' => [
                ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'support' => 'Across Sushako'],
                ['label' => 'Total Spent', 'value' => 'Rs '.number_format($totalSpent), 'support' => 'Placed, packing, shipped, delivered'],
                ['label' => 'Online Orders', 'value' => number_format($onlinePaid), 'support' => 'Paid by Razorpay'],
                ['label' => 'COD Orders', 'value' => number_format($cod), 'support' => 'Cash on delivery'],
                ['label' => 'Cancelled / Returned', 'value' => number_format($cancelled + $returnedRefunded), 'support' => $cancelled.' cancelled · '.$returnedRefunded.' returned/refunded'],
            ],
            'secondaryMetrics' => [
                'average_order_value' => $totalOrders > 0 ? 'Rs '.number_format((int) round($totalSpent / $totalOrders)) : 'Rs 0',
                'payment_failures' => number_format($failed),
                'abandoned_carts' => number_format($abandoned),
            ],
            'marketplace' => [
                'stores_purchased_from' => $vendorStats->pluck('vendor_id')->filter()->unique()->count(),
                'last_order' => $customer->orders()->latest()->value('order_number'),
                'most_purchased_store' => $vendorStats->first()?->vendor?->business_name ?: $vendorStats->first()?->vendor?->store_display_name,
                'most_purchased_store_id' => $vendorStats->first()?->vendor?->id,
                'most_purchased_category' => $categoryStats?->category_name,
            ],
            'payment' => [
                'preferred' => $this->preferredPayment($onlinePaid, $cod),
                'online_paid' => $onlinePaid,
                'cod' => $cod,
                'online_success' => $onlinePaid,
                'online_failed' => $failed,
                'cod_completed' => $codCompleted,
                'cod_cancelled' => $codCancelled,
                'cod_success_rate' => $this->percent($codCompleted, $codCompleted + $codCancelled),
                'last_payment_method' => $lastPayment ? str($lastPayment)->replace('_', ' ')->title()->toString() : 'Not selected',
                'badge' => $this->paymentBadge($onlinePaid, $cod, $codCompleted, $codCancelled, $customer->orders()->where('status', 'delivered')->count()),
            ],
        ];
    }

    public function abandonedCarts(): LengthAwarePaginator
    {
        if (! $this->hasCustomerCarts()) {
            return new Paginator(collect(), 0, 12);
        }

        return CustomerCart::query()
            ->with(['user', 'items'])
            ->whereIn('status', $this->abandonedStatuses())
            ->latest('abandoned_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function abandonedSummaries(): array
    {
        if (! $this->hasCustomerCarts()) {
            return [
                ['label' => 'Active Abandoned Carts', 'value' => 0, 'support' => 'Migration pending'],
                ['label' => 'Potential Cart Value', 'value' => 'Rs 0', 'support' => 'Migration pending'],
                ['label' => 'Reminders Opened', 'value' => 0, 'support' => 'Migration pending'],
                ['label' => 'Marked Sent', 'value' => 0, 'support' => 'Migration pending'],
                ['label' => 'Recovered Carts', 'value' => 0, 'support' => 'Migration pending'],
                ['label' => 'Recovery Revenue', 'value' => 'Rs 0', 'support' => 'Migration pending'],
            ];
        }

        $active = CustomerCart::query()->whereIn('status', $this->abandonedStatuses());

        return [
            ['label' => 'Active Abandoned Carts', 'value' => (clone $active)->count(), 'support' => 'Awaiting review'],
            ['label' => 'Potential Cart Value', 'value' => 'Rs '.number_format((int) (clone $active)->sum('original_value')), 'support' => 'Original cart value'],
            ['label' => 'Reminders Opened', 'value' => CustomerCart::query()->whereNotNull('reminder_opened_at')->count(), 'support' => 'WhatsApp intent opened'],
            ['label' => 'Marked Sent', 'value' => CustomerCart::query()->whereNotNull('reminder_marked_sent_at')->count(), 'support' => 'Confirmed manually'],
            ['label' => 'Recovered Carts', 'value' => CustomerCart::query()->where('status', CustomerCart::STATUS_RECOVERED)->count(), 'support' => 'Converted carts'],
            ['label' => 'Recovery Revenue', 'value' => 'Rs '.number_format((int) CustomerCart::query()->where('status', CustomerCart::STATUS_RECOVERED)->sum('original_value')), 'support' => 'Recovered original value'],
        ];
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $digits = preg_replace('/\D+/', '', $search);
            $customerId = null;

            if (preg_match('/^CUS-?0*(\d+)$/i', $search, $matches)) {
                $customerId = (int) $matches[1];
            } elseif (preg_match('/^\d+$/', $search)) {
                $customerId = (int) $search;
            }

            $query->where(function (Builder $query) use ($search, $digits, $customerId): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if ($digits !== '') {
                    $query->orWhere('phone', 'like', "%{$digits}%");
                }

                if ($customerId) {
                    $query->orWhere('users.id', $customerId);
                }

                $query->orWhereHas('orders', fn (Builder $orders) => $orders->where('order_number', 'like', '%'.strtoupper($search).'%'));
            });
        }

        $query
            ->when($filters['name'] ?? null, fn (Builder $query, string $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($filters['phone'] ?? null, fn (Builder $query, string $phone) => $query->where('phone', 'like', '%'.preg_replace('/\D+/', '', $phone).'%'))
            ->when($filters['email'] ?? null, fn (Builder $query, string $email) => $query->where('email', 'like', "%{$email}%"))
            ->when($filters['customer_id'] ?? null, fn (Builder $query, string $id) => $query->whereKey((int) $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['registered_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['registered_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['last_order_from'] ?? null, fn (Builder $query, string $date) => $query->whereHas('orders', fn (Builder $orders) => $orders->whereDate(DB::raw('COALESCE(placed_at, created_at)'), '>=', $date)))
            ->when($filters['last_order_to'] ?? null, fn (Builder $query, string $date) => $query->whereHas('orders', fn (Builder $orders) => $orders->whereDate(DB::raw('COALESCE(placed_at, created_at)'), '<=', $date)))
            ->when($filters['order_number'] ?? null, fn (Builder $query, string $number) => $query->whereHas('orders', fn (Builder $orders) => $orders->where('order_number', 'like', '%'.strtoupper($number).'%')));

        match ($filters['type'] ?? null) {
            'registered' => $query->whereNotNull('email'),
            'repeat' => $query->whereHas('orders', fn (Builder $orders) => $orders->select('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1')),
            'guest' => $query->whereRaw('1 = 0'),
            'converted' => $query->whereHas('orders'),
            default => null,
        };

        match ($filters['payment'] ?? null) {
            'cod' => $query->whereHas('orders', fn (Builder $orders) => $orders->where('payment_method', 'cod')),
            'online' => $query->whereHas('orders', fn (Builder $orders) => $orders->where('payment_method', 'razorpay')->where('payment_status', 'paid')),
            'mixed' => $query->whereHas('orders', fn (Builder $orders) => $orders->where('payment_method', 'cod'))->whereHas('orders', fn (Builder $orders) => $orders->where('payment_method', 'razorpay')->where('payment_status', 'paid')),
            default => null,
        };

        match ($filters['activity'] ?? null) {
            'new' => $query->where('created_at', '>=', now()->startOfMonth()),
            'inactive_30' => $this->applyInactiveFilter($query, 30),
            'inactive_60' => $this->applyInactiveFilter($query, 60),
            'cancelled' => $query->whereHas('orders', fn (Builder $orders) => $orders->where('status', 'cancelled')),
            'abandoned' => $this->hasCustomerCarts()
                ? $query->whereHas('customerCarts', fn (Builder $carts) => $carts->whereIn('status', $this->abandonedStatuses()))
                : $query->whereRaw('1 = 0'),
            default => null,
        };
    }

    public function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'name' => $query->orderBy('name'),
            'orders' => $query->orderByDesc('total_orders_count'),
            'spent' => $query->orderByDesc('total_spent'),
            'oldest' => $query->oldest(),
            'last_order' => $query->orderByDesc('last_order_at'),
            default => $query->orderByDesc(DB::raw($this->hasLastActivityColumn() ? 'COALESCE(last_activity_at, last_login_at, created_at)' : 'COALESCE(last_login_at, created_at)')),
        };
    }

    public function valuableOrders(Builder $query): Builder
    {
        return $query
            ->whereIn('status', self::VALUE_STATUSES)
            ->where('payment_status', '!=', 'failed');
    }

    public function percent(int $value, int $total): string
    {
        return $total > 0 ? round(($value / $total) * 100).'%' : '0%';
    }

    public function preferredPayment(int $onlinePaid, int $cod): string
    {
        return match (true) {
            $onlinePaid === 0 && $cod === 0 => 'New Customer',
            $onlinePaid > $cod => 'Mostly Online',
            $cod > $onlinePaid => 'Mostly COD',
            default => 'Mixed Payment',
        };
    }

    public function paymentBadge(int $onlinePaid, int $cod, int $codCompleted, int $codCancelled, int $completed): string
    {
        if ($completed === 0) {
            return 'No Completed Orders';
        }

        if ($cod >= 2 && $codCompleted > $codCancelled) {
            return 'Reliable COD Customer';
        }

        if ($codCancelled > $codCompleted && $cod > 0) {
            return 'COD Confirmation Recommended';
        }

        return $this->preferredPayment($onlinePaid, $cod);
    }

    private function abandonedStatuses(): array
    {
        return [CustomerCart::STATUS_ABANDONED, CustomerCart::STATUS_WHATSAPP_OPENED, CustomerCart::STATUS_REMINDER_MARKED_SENT];
    }

    private function applyInactiveFilter(Builder $query, int $days): void
    {
        if ($this->hasLastActivityColumn()) {
            $query->where(fn (Builder $query) => $query->whereNull('last_activity_at')->orWhere('last_activity_at', '<', now()->subDays($days)));

            return;
        }

        $query->where(fn (Builder $query) => $query->whereNull('last_login_at')->orWhere('last_login_at', '<', now()->subDays($days)));
    }

    private function hasCustomerCarts(): bool
    {
        return Schema::hasTable('customer_carts');
    }

    private function hasLastActivityColumn(): bool
    {
        return Schema::hasColumn('users', 'last_activity_at');
    }
}
