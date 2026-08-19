<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');
        [$start, $end] = $this->period($request);

        $query = OrderItem::query()
            ->with(['order.user', 'product'])
            ->where('vendor_id', $vendor->id)
            ->when($start, fn (Builder $query) => $query->whereHas('order', fn (Builder $orders) => $orders->whereDate('created_at', '>=', $start)))
            ->when($end, fn (Builder $query) => $query->whereHas('order', fn (Builder $orders) => $orders->whereDate('created_at', '<=', $end)))
            ->when($request->query('settlement_status'), fn (Builder $query, string $status) => $query->where('settlement_status', $status))
            ->when($request->query('order_status'), fn (Builder $query, string $status) => $query->whereHas('order', fn (Builder $orders) => $orders->where('status', $status)));

        $items = (clone $query)->latest()->paginate(20)->withQueryString();

        return view('seller.earnings.index', [
            'vendor' => $vendor,
            'items' => $items,
            'filters' => $request->query(),
            'summary' => [
                'gross_sales' => (float) (clone $query)->sum('gross_line_amount'),
                'products_sold' => (int) (clone $query)->sum('eligible_quantity'),
                'platform_fees' => (float) (clone $query)->sum('platform_fee_total'),
                'refunds' => (float) (clone $query)->sum('refund_amount'),
                'returns' => (float) (clone $query)->sum('return_amount'),
                'adjustments' => (float) (clone $query)->sum('adjustment_amount'),
                'net_earnings' => (float) (clone $query)->sum('seller_earning'),
                'pending_amount' => (float) (clone $query)->whereIn('settlement_status', ['upcoming', 'pending_approval', 'approved', 'processing'])->sum('seller_earning'),
                'settled_amount' => (float) (clone $query)->where('settlement_status', 'settled')->sum('seller_earning'),
            ],
        ]);
    }

    private function period(Request $request): array
    {
        return match ($request->query('period')) {
            'today' => [today()->toDateString(), today()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month', null => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'custom' => [$request->query('start_date'), $request->query('end_date')],
            default => [null, null],
        };
    }
}
