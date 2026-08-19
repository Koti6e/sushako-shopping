<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerCategoryRequest;
use App\Models\SellerOnboardingPayment;
use App\Models\SellerSettlement;
use App\Models\User;
use App\Models\Vendor;

class AdminControlCenterService
{
    public function sidebar(): array
    {
        $counts = $this->sidebarCounts();

        return [
            'counts' => $counts,
            'sections' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'fa-gauge-high',
                    'route' => 'admin.dashboard',
                    'active' => ['admin.dashboard'],
                ],
                [
                    'label' => 'Marketplace',
                    'icon' => 'fa-store',
                    'active' => ['admin.sellers.*', 'admin.category-requests.*'],
                    'children' => [
                        $this->child('Approval Center', 'admin.approvals.index', ['admin.approvals.*'], $counts['approvals']),
                        $this->child('Seller Management', 'admin.sellers.index', ['admin.sellers.*'], $counts['sellers']),
                        $this->child('Category Requests', 'admin.category-requests.index', ['admin.category-requests.*'], $counts['category_requests']),
                        $this->child('Official Store', 'admin.products.index', ['admin.products.*'], null, ['store' => 'official']),
                    ],
                ],
                [
                    'label' => 'Product Management',
                    'icon' => 'fa-boxes-stacked',
                    'active' => ['admin.products.*', 'admin.categories.*', 'admin.inventory.*'],
                    'children' => [
                        $this->child('Products', 'admin.products.index', ['admin.products.*'], $counts['products']),
                        $this->child('Categories', 'admin.categories.index', ['admin.categories.*'], $counts['categories']),
                        $this->child('Inventory', 'admin.inventory.index', ['admin.inventory.*'], $counts['stock_alerts']),
                    ],
                ],
                [
                    'label' => 'Order Management',
                    'icon' => 'fa-receipt',
                    'active' => ['admin.orders.*', 'admin.shipping-labels.*'],
                    'children' => [
                        $this->child('Order Queue', 'admin.orders.index', ['admin.orders.*'], $counts['orders']),
                        $this->child('New Orders', 'admin.orders.index', ['admin.orders.index'], $counts['new_orders'], ['status' => 'placed']),
                        $this->child('Shipping Labels', 'admin.shipping-labels.index', ['admin.shipping-labels.*']),
                    ],
                ],
                [
                    'label' => 'Customer Management',
                    'icon' => 'fa-users',
                    'active' => ['admin.customers.*', 'admin.customer-communications.*'],
                    'children' => [
                        $this->child('Customers', 'admin.customers.index', ['admin.customers.index', 'admin.customers.show'], $counts['customers']),
                        $this->child('Segments', 'admin.customers.segments', ['admin.customers.segments']),
                        $this->child('Abandoned Carts', 'admin.customers.abandoned-carts.index', ['admin.customers.abandoned-carts.*']),
                        $this->child('Communications', 'admin.customers.communications.index', ['admin.customers.communications.*', 'admin.customer-communications.*']),
                        $this->child('Exports', 'admin.customers.exports', ['admin.customers.exports']),
                    ],
                ],
                [
                    'label' => 'Finance',
                    'icon' => 'fa-indian-rupee-sign',
                    'active' => ['admin.settlements.*'],
                    'children' => [
                        $this->child('Settlements', 'admin.settlements.index', ['admin.settlements.*'], $counts['settlements']),
                        $this->child('Generate Batch', 'admin.settlements.index', ['admin.settlements.index']),
                        $this->child('Commission Rules', 'admin.sellers.commissions.index', ['admin.sellers.commissions.*']),
                    ],
                ],
                [
                    'label' => 'Marketing',
                    'icon' => 'fa-bullhorn',
                    'active' => ['admin.sellers.banners.*', 'admin.customers.whatsapp.*'],
                    'children' => [
                        $this->child('Store Banners', 'admin.sellers.banners.index', ['admin.sellers.banners.*']),
                        $this->child('WhatsApp Campaigns', 'admin.customers.whatsapp.index', ['admin.customers.whatsapp.*']),
                    ],
                ],
                [
                    'label' => 'Reports & Analytics',
                    'icon' => 'fa-chart-line',
                    'active' => ['admin.customers.exports'],
                    'children' => [
                        $this->child('Customer Exports', 'admin.customers.exports', ['admin.customers.exports']),
                        $this->placeholder('Sales Reports', 'admin.settings.placeholder', ['section' => 'reports']),
                    ],
                ],
                [
                    'label' => 'Platform',
                    'icon' => 'fa-sliders',
                    'active' => ['admin.settings.*'],
                    'children' => [
                        $this->child('Company', 'admin.settings.company', ['admin.settings.company']),
                        $this->child('Invoice', 'admin.settings.invoice', ['admin.settings.invoice']),
                        $this->child('Tax', 'admin.settings.tax', ['admin.settings.tax']),
                        $this->child('Shipping', 'admin.settings.shipping', ['admin.settings.shipping']),
                        $this->child('Payments', 'admin.settings.payments', ['admin.settings.payments']),
                    ],
                ],
                [
                    'label' => 'System',
                    'icon' => 'fa-server',
                    'active' => ['admin.settings.placeholder'],
                    'children' => [
                        $this->placeholder('Policies', 'admin.settings.placeholder', ['section' => 'policies']),
                        $this->placeholder('Notifications', 'admin.settings.placeholder', ['section' => 'notifications']),
                        $this->child('Storefront', 'home', [], null, [], true),
                    ],
                ],
                [
                    'label' => 'Support',
                    'icon' => 'fa-headset',
                    'active' => ['admin.customers.communications.*'],
                    'children' => [
                        $this->child('Customer Issues', 'admin.customers.communications.index', ['admin.customers.communications.*'], $counts['customer_issues']),
                        $this->placeholder('Help Desk', 'admin.settings.placeholder', ['section' => 'support']),
                    ],
                ],
            ],
        ];
    }

    public function dashboard(): array
    {
        return [
            'metrics' => $this->metrics(),
            'priorities' => $this->priorities(),
            'insights' => $this->insights(),
            'approvals' => $this->approvalQueues(),
            'recentOrders' => Order::query()->whereIn('status', ['placed', 'packing', 'shipped', 'delivered'])->latest()->take(6)->get(),
        ];
    }

    public function approvalQueues(): array
    {
        return [
            [
                'label' => 'Seller Approval',
                'description' => 'Stores waiting for admin approval or verification.',
                'count' => Vendor::query()->whereIn('approval_status', [Vendor::APPROVAL_PENDING_ADMIN, Vendor::APPROVAL_SETUP_COMPLETE])->count(),
                'url' => route('admin.sellers.index', ['filter' => 'approval_pending']),
                'items' => Vendor::query()->whereIn('approval_status', [Vendor::APPROVAL_PENDING_ADMIN, Vendor::APPROVAL_SETUP_COMPLETE])->latest()->take(5)->get()->map(fn (Vendor $vendor): array => [
                    'title' => $vendor->business_name,
                    'meta' => str($vendor->approval_status)->replace('_', ' ')->title()->toString(),
                    'url' => route('admin.sellers.show', $vendor),
                ])->all(),
            ],
            [
                'label' => 'Category Requests',
                'description' => 'Seller marketplace category requests.',
                'count' => SellerCategoryRequest::query()->where('status', SellerCategoryRequest::STATUS_PENDING)->count(),
                'url' => route('admin.category-requests.index'),
                'items' => SellerCategoryRequest::query()->with('vendor')->where('status', SellerCategoryRequest::STATUS_PENDING)->latest()->take(5)->get()->map(fn (SellerCategoryRequest $request): array => [
                    'title' => $request->requested_category_name,
                    'meta' => $request->vendor?->business_name ?: 'Seller',
                    'url' => route('admin.category-requests.index'),
                ])->all(),
            ],
            [
                'label' => 'Product Approval',
                'description' => 'Seller products awaiting moderation.',
                'count' => Product::query()->where('seller_status', Product::SELLER_STATUS_PENDING_REVIEW)->count(),
                'url' => route('admin.products.index', ['seller_status' => Product::SELLER_STATUS_PENDING_REVIEW]),
                'items' => Product::query()->with('vendor')->where('seller_status', Product::SELLER_STATUS_PENDING_REVIEW)->latest()->take(5)->get()->map(fn (Product $product): array => [
                    'title' => $product->name,
                    'meta' => $product->vendor?->business_name ?: 'Seller product',
                    'url' => route('admin.products.edit', $product->slug),
                ])->all(),
            ],
            [
                'label' => 'Settlement Releases',
                'description' => 'Seller payouts waiting for release.',
                'count' => SellerSettlement::query()->whereIn('status', ['draft', 'pending_approval', 'approved'])->count(),
                'url' => route('admin.settlements.index', ['status' => 'pending_approval']),
                'items' => SellerSettlement::query()->with('vendor')->whereIn('status', ['draft', 'pending_approval', 'approved'])->latest()->take(5)->get()->map(fn (SellerSettlement $settlement): array => [
                    'title' => $settlement->settlement_number,
                    'meta' => ($settlement->vendor?->business_name ?: 'Seller').' · Rs '.number_format((float) $settlement->final_settlement_amount),
                    'url' => route('admin.settlements.show', $settlement),
                ])->all(),
            ],
            [
                'label' => 'Store Verification',
                'description' => 'Bank, GST and profile checks needing admin review.',
                'count' => Vendor::query()->whereIn('bank_verification_status', ['submitted', 'verification_pending'])->count(),
                'url' => route('admin.sellers.index', ['filter' => 'bank_pending']),
                'items' => Vendor::query()->whereIn('bank_verification_status', ['submitted', 'verification_pending'])->latest()->take(5)->get()->map(fn (Vendor $vendor): array => [
                    'title' => $vendor->business_name,
                    'meta' => 'Bank '.$vendor->bank_verification_status,
                    'url' => route('admin.sellers.show', $vendor),
                ])->all(),
            ],
        ];
    }

    private function sidebarCounts(): array
    {
        return [
            'approvals' => Vendor::query()->whereIn('approval_status', [Vendor::APPROVAL_PENDING_ADMIN, Vendor::APPROVAL_SETUP_COMPLETE])->count()
                + SellerCategoryRequest::query()->where('status', SellerCategoryRequest::STATUS_PENDING)->count()
                + Product::query()->where('seller_status', Product::SELLER_STATUS_PENDING_REVIEW)->count(),
            'sellers' => Vendor::query()->count(),
            'category_requests' => SellerCategoryRequest::query()->where('status', SellerCategoryRequest::STATUS_PENDING)->count(),
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'stock_alerts' => Product::query()->whereHas('variants', fn ($query) => $query->whereColumn('product_variants.stock', '<=', 'products.low_stock_threshold'))->count(),
            'orders' => Order::query()->whereIn('status', ['placed', 'packing'])->count(),
            'new_orders' => Order::query()->where('status', 'placed')->count(),
            'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
            'settlements' => SellerSettlement::query()->whereIn('status', ['draft', 'pending_approval', 'approved', 'processing'])->count(),
            'customer_issues' => 0,
        ];
    }

    private function metrics(): array
    {
        $orders = Order::query()->whereIn('status', ['placed', 'packing', 'shipped', 'delivered']);

        return [
            ['label' => 'Gross Sales', 'value' => 'Rs '.number_format((float) (clone $orders)->sum('total_amount')), 'url' => route('admin.orders.index')],
            ['label' => 'Open Orders', 'value' => Order::query()->whereIn('status', ['placed', 'packing'])->count(), 'url' => route('admin.orders.index')],
            ['label' => 'Active Sellers', 'value' => Vendor::query()->where('status', Vendor::STATUS_ACTIVE)->count(), 'url' => route('admin.sellers.index')],
            ['label' => 'Published Products', 'value' => Product::query()->where('is_published', true)->count(), 'url' => route('admin.products.index')],
            ['label' => 'Customers', 'value' => User::query()->where('role', User::ROLE_CUSTOMER)->count(), 'url' => route('admin.customers.index')],
            ['label' => 'Settlement Queue', 'value' => SellerSettlement::query()->whereIn('status', ['draft', 'pending_approval', 'approved'])->count(), 'url' => route('admin.settlements.index')],
        ];
    }

    private function priorities(): array
    {
        return collect([
            $this->priority('New Seller Approvals', Vendor::query()->whereIn('approval_status', [Vendor::APPROVAL_PENDING_ADMIN, Vendor::APPROVAL_SETUP_COMPLETE])->count(), route('admin.sellers.index', ['filter' => 'approval_pending']), 'Review sellers ready to publish.'),
            $this->priority('Pending Category Requests', SellerCategoryRequest::query()->where('status', SellerCategoryRequest::STATUS_PENDING)->count(), route('admin.category-requests.index'), 'Approve or reject seller marketplace categories.'),
            $this->priority('Settlement Releases', SellerSettlement::query()->whereIn('status', ['draft', 'pending_approval', 'approved'])->count(), route('admin.settlements.index'), 'Review payout batches and releases.'),
            $this->priority('Recent Failed Payments', SellerOnboardingPayment::query()->where('status', SellerOnboardingPayment::STATUS_FAILED)->count(), route('admin.sellers.index', ['filter' => 'payment_failed']), 'Follow up seller plan payment failures.'),
            $this->priority('Subscription Renewals', Vendor::query()->whereNotNull('plan_expires_at')->whereBetween('plan_expires_at', [now(), now()->addDays(7)])->count(), route('admin.sellers.index', ['filter' => 'expiring']), 'Plans expiring within seven days.'),
            $this->priority('Platform Alerts', Product::query()->whereHas('variants', fn ($query) => $query->whereColumn('product_variants.stock', '<=', 'products.low_stock_threshold'))->count(), route('admin.inventory.index'), 'Low stock and inventory alerts.'),
        ])->filter(fn (array $item): bool => $item['count'] > 0)->values()->all();
    }

    private function insights(): array
    {
        $incompleteSellers = Vendor::query()->where('onboarding_status', '!=', 'complete')->count();
        $sellersWithoutProducts = Vendor::query()->whereDoesntHave('products')->count();
        $expiringGrowth = Vendor::query()->where('current_plan', Vendor::PLAN_GROWTH)->whereBetween('plan_expires_at', [now(), now()->addDays(7)])->count();
        $topCategory = Category::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->value('name') ?: 'No category signal yet';
        $topSeller = Vendor::query()
            ->withSum(['ledgerEntries as revenue_today' => fn ($query) => $query->whereDate('created_at', today())], 'gross_amount')
            ->orderByDesc('revenue_today')
            ->value('business_name') ?: 'No seller sales today';

        return [
            "{$incompleteSellers} sellers have not completed onboarding.",
            "{$sellersWithoutProducts} sellers have no products.",
            "{$expiringGrowth} Growth subscriptions expire this week.",
            "{$topCategory} is the strongest category signal today.",
            'Settlement queue contains '.SellerSettlement::query()->whereIn('status', ['draft', 'pending_approval', 'approved'])->count().' pending releases.',
            User::query()->where('role', User::ROLE_CUSTOMER)->whereDate('created_at', today())->count().' customer registrations today.',
            "Top performing seller today: {$topSeller}.",
            Product::query()->where('seller_status', Product::SELLER_STATUS_PENDING_REVIEW)->count().' products are awaiting moderation.',
        ];
    }

    private function child(string $label, string $route, array $active, ?int $count = null, array $params = [], bool $external = false): array
    {
        return compact('label', 'route', 'active', 'count', 'params', 'external');
    }

    private function placeholder(string $label, string $route, array $params = []): array
    {
        return $this->child($label, $route, ['admin.settings.placeholder'], null, $params);
    }

    private function priority(string $label, int $count, string $url, string $description): array
    {
        return compact('label', 'count', 'url', 'description');
    }
}
