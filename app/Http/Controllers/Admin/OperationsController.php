<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\TaxSlab;
use App\Models\User;
use App\Support\ProductCatalog;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function categories(): View
    {
        return view('admin.operations.categories', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->with([
                    'children' => fn ($query) => $query
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->withCount('products')
                        ->with([
                            'children' => fn ($query) => $query
                                ->orderBy('sort_order')
                                ->orderBy('name')
                                ->withCount('products')
                                ->with([
                                    'children' => fn ($query) => $query
                                        ->orderBy('sort_order')
                                        ->orderBy('name')
                                        ->withCount('products'),
                                ]),
                        ]),
                ])
                ->withCount('products')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createCategory(): View
    {
        return view('admin.operations.category-create', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->with([
                    'children' => fn ($query) => $query
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with([
                            'children' => fn ($query) => $query
                                ->orderBy('sort_order')
                                ->orderBy('name')
                                ->with([
                                    'children' => fn ($query) => $query
                                        ->orderBy('sort_order')
                                        ->orderBy('name'),
                                ]),
                        ]),
                ])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'taxSlabs' => TaxSlab::query()
                ->where('is_active', true)
                ->orderBy('rate')
                ->get(),
        ]);
    }

    public function createProduct(): View
    {
        return view('admin.operations.product-create', [
            'categories' => ProductCatalog::categories(),
            'taxSlabs' => TaxSlab::query()
                ->where('is_active', true)
                ->orderBy('rate')
                ->get(),
        ]);
    }

    public function inventory(): View
    {
        return view('admin.operations.inventory', [
            'products' => ProductCatalog::products(),
        ]);
    }

    public function orders(): View
    {
        $orders = Order::query()
            ->with(['items', 'latestShippingLabel'])
            ->whereIn('status', ['placed', 'packing', 'shipped', 'delivered'])
            ->latest()
            ->paginate(20);

        return view('admin.operations.orders', [
            'orders' => $orders,
            'orderLanes' => [
                'placed' => [
                    'title' => 'New Orders',
                    'subtitle' => 'Immediate action needed',
                    'orders' => $orders->getCollection()->where('status', 'placed'),
                ],
                'packing' => [
                    'title' => 'Packing',
                    'subtitle' => 'Prepare and assign courier',
                    'orders' => $orders->getCollection()->where('status', 'packing'),
                ],
                'shipped' => [
                    'title' => 'In Transit',
                    'subtitle' => 'Shipped orders on the way',
                    'orders' => $orders->getCollection()->where('status', 'shipped'),
                ],
                'delivered' => [
                    'title' => 'Delivered',
                    'subtitle' => 'Completed orders',
                    'orders' => $orders->getCollection()->where('status', 'delivered'),
                ],
            ],
            'shippingProviders' => $this->shippingProviders(),
        ]);
    }

    public function orderShow(Order $order): View
    {
        abort_if($order->status === 'payment_pending', 404);

        return view('admin.operations.order-show', [
            'order' => $order->load(['items', 'latestShippingLabel']),
            'shippingProviders' => $this->shippingProviders(),
        ]);
    }

    public function customers(): View
    {
        return view('admin.operations.customers', [
            'customers' => User::query()
                ->where('role', User::ROLE_CUSTOMER)
                ->latest()
                ->paginate(20),
        ]);
    }

    private function shippingProviders(): array
    {
        return [
            'rapido' => 'Rapido',
            'uber' => 'Uber',
            'india_post' => 'India Post',
            'dtdc' => 'DTDC',
            'professional' => 'Professional Courier',
            'others' => 'Others',
        ];
    }
}
