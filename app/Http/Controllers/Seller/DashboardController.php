<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SellerAccountService;
use App\Services\SellerDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(SellerAccountService $accounts, SellerDashboardService $dashboard): View
    {
        $vendor = request()->attributes->get('vendor');
        $readiness = $accounts->readiness($vendor);
        $setupChecklist = $accounts->storeSetupChecklist($vendor);

        return view('seller.dashboard', [
            'vendor' => $vendor,
            'readiness' => $readiness,
            'setupChecklist' => $setupChecklist,
            'cards' => $dashboard->cards($vendor),
            'priorityActions' => $dashboard->priorityActions($vendor, $readiness),
            'quickActions' => $dashboard->quickActions($vendor, $readiness),
            'settlementSummary' => $dashboard->settlementSummary($vendor),
            'monthlyEarnings' => $dashboard->monthlyEarnings($vendor),
            'recentOrders' => Order::query()
                ->with('items')
                ->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
