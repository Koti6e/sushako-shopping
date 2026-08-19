<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\AdminControlCenterService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AdminControlCenterService $controlCenter): View
    {
        $finalizedOrders = Order::query()->whereIn('status', ['placed', 'packing', 'shipped', 'delivered']);
        $dashboard = $controlCenter->dashboard();

        return view('admin.dashboard', [
            'recentOrders' => $dashboard['recentOrders'],
            'storeCounts' => [
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
                'inventory_alerts' => Product::query()->whereDoesntHave('variants', fn ($query) => $query->where('stock', '>', 0))->count(),
                'orders' => (clone $finalizedOrders)->count(),
                'pending_orders' => Order::query()->whereIn('status', ['placed', 'packing'])->count(),
                'shipped_orders' => Order::query()->where('status', 'shipped')->count(),
                'delivered_orders' => Order::query()->where('status', 'delivered')->count(),
                'revenue' => (clone $finalizedOrders)->sum('total_amount'),
                'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
            ],
            'metrics' => $dashboard['metrics'],
            'priorities' => $dashboard['priorities'],
            'insights' => $dashboard['insights'],
            'approvals' => $dashboard['approvals'],
        ]);
    }
}
