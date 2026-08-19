<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\SellerOnboardingPayment;
use App\Models\SellerSettlement;
use App\Models\Vendor;

class SellerSidebarService
{
    public function data(Vendor $vendor): array
    {
        $vendor->loadMissing('user');
        $counts = $this->counts($vendor);
        $setupComplete = $vendor->onboarding_status === 'complete';

        return [
            'vendor' => $vendor,
            'counts' => $counts,
            'setupComplete' => $setupComplete,
            'navigation' => $this->navigation($counts, $setupComplete),
            'quickActions' => $this->quickActions($counts, $setupComplete),
            'accountActions' => $this->accountActions($setupComplete),
        ];
    }

    private function counts(Vendor $vendor): array
    {
        return [
            'orders' => Order::query()
                ->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
                ->whereIn('seller_order_status', ['new', 'placed', 'accepted', 'processing'])
                ->count(),
            'low_stock' => Product::query()
                ->where('vendor_id', $vendor->id)
                ->whereHas('variants', fn ($query) => $query->whereColumn('product_variants.stock', '<=', 'products.low_stock_threshold'))
                ->count(),
            'settlements' => SellerSettlement::query()
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', ['upcoming', 'pending_approval', 'approved', 'processing'])
                ->count(),
        ];
    }

    private function navigation(array $counts, bool $setupComplete): array
    {
        return [
            'Seller OS' => [
                $this->item('seller.dashboard', 'fa-gauge-high', 'Dashboard', ['seller.dashboard']),
                $this->item('seller.products.index', 'fa-box', 'Products', ['seller.products.*'], null, $setupComplete),
                $this->item('seller.orders.index', 'fa-receipt', 'Orders', ['seller.orders.*'], $counts['orders'], $setupComplete),
                $this->item('seller.customers.index', 'fa-users', 'Customers', ['seller.customers.*'], null, $setupComplete),
                $this->item('seller.earnings.index', 'fa-chart-line', 'Earnings / Settlements', ['seller.earnings.*', 'seller.settlements.*'], $counts['settlements'], $setupComplete),
                $this->item('seller.shipping.index', 'fa-truck', 'Delivery', ['seller.shipping.*'], null, $setupComplete),
                $this->item('seller.store-profile', 'fa-store', 'My Shop', ['seller.store-profile'], null, $setupComplete),
                $this->item('seller.plans.index', 'fa-arrow-up-right-dots', 'Upgrade Plan', ['seller.plans.*'], null, $setupComplete),
                $this->item('seller.settings.index', 'fa-gear', 'Settings', ['seller.settings.*'], null, $setupComplete),
            ],
        ];
    }

    private function quickActions(array $counts, bool $setupComplete): array
    {
        return collect([
            [
                'label' => 'Complete Store Setup',
                'description' => 'Finish required seller setup',
                'icon' => 'fa-list-check',
                'route' => 'seller.onboarding',
                'enabled' => ! $setupComplete,
                'priority' => true,
            ],
            ['label' => 'Add Product', 'description' => 'Create a new listing', 'icon' => 'fa-plus', 'route' => 'seller.products.create', 'enabled' => $setupComplete],
            ['label' => 'View Orders', 'description' => $counts['orders'] ? $this->badge($counts['orders']).' need action' : 'Open order queue', 'icon' => 'fa-receipt', 'route' => 'seller.orders.index', 'enabled' => $setupComplete],
            ['label' => 'Customers', 'description' => 'View your customers', 'icon' => 'fa-users', 'route' => 'seller.customers.index', 'enabled' => $setupComplete],
            ['label' => 'Update Stock', 'description' => $counts['low_stock'] ? $this->badge($counts['low_stock']).' low stock' : 'Adjust inventory', 'icon' => 'fa-warehouse', 'route' => 'seller.inventory.index', 'enabled' => $setupComplete],
        ])
            ->filter(fn (array $action) => $action['enabled'] || (($action['priority'] ?? false) && ! $setupComplete))
            ->values()
            ->all();
    }

    private function accountActions(bool $setupComplete): array
    {
        return [
            ['label' => 'Business Profile', 'icon' => 'fa-store', 'route' => 'seller.store-profile', 'enabled' => $setupComplete],
            ['label' => 'Account Settings', 'icon' => 'fa-gear', 'route' => 'seller.settings.index', 'enabled' => $setupComplete],
            ['label' => 'Help & Support', 'icon' => 'fa-headset', 'route' => 'seller.support.index', 'enabled' => $setupComplete],
        ];
    }

    private function item(string $route, string $icon, string $label, array $activeRoutes, ?int $count = null, bool $enabled = true): array
    {
        return compact('route', 'icon', 'label', 'activeRoutes', 'count', 'enabled') + [
            'disabledReason' => 'Complete store setup to access this module.',
        ];
    }

    private function paymentRequired(Vendor $vendor): bool
    {
        return ($vendor->selected_plan_slug ?: $vendor->selected_plan) !== Vendor::PLAN_FREE
            && in_array($vendor->payment_status, [Vendor::PAYMENT_PENDING, Vendor::PAYMENT_FAILED], true)
            && $vendor->onboardingPayments()->whereIn('status', [SellerOnboardingPayment::STATUS_PENDING, SellerOnboardingPayment::STATUS_FAILED])->exists();
    }

    private function badge(int $count): string
    {
        return $count > 99 ? '99+' : (string) $count;
    }
}
