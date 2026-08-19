<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerSettlement;
use App\Models\Vendor;
use Illuminate\Support\Collection;

class SellerDashboardService
{
    public function cards(Vendor $vendor): array
    {
        $products = $vendor->products();
        $items = OrderItem::query()->where('vendor_id', $vendor->id);
        $todayItems = (clone $items)->whereHas('order', fn ($query) => $query->whereDate('created_at', today())->whereIn('status', ['placed', 'packing', 'shipped', 'delivered', 'completed']));
        $orders = Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id));
        $todayOrders = (clone $orders)->whereDate('created_at', today());

        return [
            ['label' => "Today's Sales", 'value' => 'Rs '.number_format((float) (clone $todayItems)->sum('gross_line_amount')), 'url' => route('seller.orders.index')],
            ['label' => "Today's Orders", 'value' => (clone $todayOrders)->count(), 'url' => route('seller.orders.index')],
            ['label' => 'Orders to Pack', 'value' => (clone $orders)->whereIn('seller_order_status', ['new', 'accepted', 'processing', 'packed'])->count(), 'url' => route('seller.orders.index')],
            ['label' => 'Ready / Shipped', 'value' => (clone $orders)->whereIn('seller_order_status', ['ready_to_ship', 'shipped'])->count(), 'url' => route('seller.orders.index')],
            ['label' => 'Products', 'value' => (clone $products)->count(), 'url' => route('seller.products.index')],
        ];
    }

    public function monthlyEarnings(Vendor $vendor): array
    {
        $items = OrderItem::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]));

        return [
            'gross_sales' => (float) (clone $items)->sum('gross_line_amount'),
            'total_product_units_sold' => (int) (clone $items)->sum('eligible_quantity'),
            'platform_fee_rate' => ($vendor->current_plan ?: Vendor::PLAN_FREE) === Vendor::PLAN_FREE ? 'Rs 1 per product' : 'Zero while paid plan is active',
            'total_platform_fee' => (float) (clone $items)->sum('platform_fee_total'),
            'refunds' => (float) (clone $items)->sum('refund_amount'),
            'returns' => (float) (clone $items)->sum('return_amount'),
            'other_adjustments' => (float) (clone $items)->sum('adjustment_amount'),
            'net_seller_earnings' => (float) (clone $items)->sum('seller_earning'),
            'pending_settlement' => (float) (clone $items)->whereIn('settlement_status', ['upcoming', 'pending_approval', 'approved', 'processing'])->sum('seller_earning'),
            'settled_amount' => (float) (clone $items)->where('settlement_status', 'settled')->sum('seller_earning'),
        ];
    }

    public function priorityActions(Vendor $vendor, array $readiness): Collection
    {
        return collect()
            ->when(blank($vendor->bank_account_number), fn ($items) => $items->push(['label' => 'Add bank details to receive settlements', 'url' => route('seller.settlements.index')]))
            ->merge($vendor->products()->whereHas('variants', fn ($query) => $query->where('stock', '<=', 2))->limit(5)->get()->map(fn ($product) => ['label' => 'Low stock: '.$product->name, 'url' => route('seller.inventory.index')]))
            ->merge(Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))->where('seller_order_status', 'new')->limit(5)->get()->map(fn ($order) => ['label' => 'New order '.$order->order_number, 'url' => route('seller.orders.show', $order->order_number)]));
    }

    public function quickActions(Vendor $vendor, array $readiness): array
    {
        return collect([
            ['title' => 'Add Product', 'copy' => 'Create a listing.', 'icon' => 'fa-plus', 'url' => route('seller.products.create'), 'status' => null, 'show' => true],
            ['title' => 'View Orders', 'copy' => 'Pack and update orders.', 'icon' => 'fa-receipt', 'url' => route('seller.orders.index'), 'status' => null, 'show' => true],
            ['title' => 'Customers', 'copy' => 'People who ordered from you.', 'icon' => 'fa-users', 'url' => route('seller.customers.index'), 'status' => null, 'show' => true],
            ['title' => 'Manage Stock', 'copy' => 'Update inventory.', 'icon' => 'fa-warehouse', 'url' => route('seller.inventory.index'), 'status' => null, 'show' => true],
        ])->where('show', true)->values()->all();
    }

    public function settlementSummary(Vendor $vendor): array
    {
        $items = OrderItem::query()->where('vendor_id', $vendor->id);

        return [
            'gross_product_value' => (int) (clone $items)->sum('gross_line_amount'),
            'platform_fee' => (int) (clone $items)->sum('platform_fee_total'),
            'refund_deductions' => 0,
            'other_adjustments' => 0,
            'net_settlement' => (int) (clone $items)->sum('seller_earning'),
            'expected_settlement_date' => now()->addWeekdays(5)->format('d M Y'),
            'status' => SellerSettlement::query()->where('vendor_id', $vendor->id)->latest()->value('status') ?? 'upcoming',
        ];
    }
}
