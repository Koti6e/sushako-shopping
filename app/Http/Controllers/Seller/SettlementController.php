<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\SellerSettlement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');
        $settlements = SellerSettlement::query()->where('vendor_id', $vendor->id)->latest()->paginate(20);
        $base = SellerSettlement::query()->where('vendor_id', $vendor->id);

        return view('seller.settlements.index', [
            'vendor' => $vendor,
            'settlements' => $settlements,
            'upcomingItems' => OrderItem::query()
                ->with('order')
                ->where('vendor_id', $vendor->id)
                ->where('settlement_status', 'upcoming')
                ->latest()
                ->limit(10)
                ->get(),
            'summary' => [
                'eligible' => (float) OrderItem::query()->where('vendor_id', $vendor->id)->where('settlement_status', 'upcoming')->sum('seller_earning'),
                'processing' => (float) (clone $base)->where('status', 'processing')->sum('final_settlement_amount'),
                'on_hold' => (float) (clone $base)->where('status', 'on_hold')->sum('final_settlement_amount'),
                'settled' => (float) (clone $base)->where('status', 'paid')->sum('final_settlement_amount'),
                'failed' => (float) (clone $base)->where('status', 'failed')->sum('final_settlement_amount'),
            ],
        ]);
    }

    public function show(Request $request, SellerSettlement $settlement): View
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($settlement->vendor_id === $vendor->id, 404);

        return view('seller.settlements.show', [
            'vendor' => $vendor,
            'settlement' => $settlement->load(['items.orderItem.order', 'adjustments']),
        ]);
    }

    public function statement(Request $request, SellerSettlement $settlement): Response
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($settlement->vendor_id === $vendor->id, 404);

        $settlement->load(['items.orderItem.order']);
        $lines = [
            ['Settlement ID', $settlement->settlement_number],
            ['Status', $settlement->status],
            ['Gross Sales', $settlement->gross_amount],
            ['Platform Fee', $settlement->platform_fee_amount],
            ['Refunds', $settlement->refund_amount],
            ['Returns', $settlement->return_amount],
            ['Adjustments', $settlement->adjustment_amount],
            ['Final Settlement', $settlement->final_settlement_amount],
            [],
            ['Order ID', 'Product', 'Quantity', 'Gross', 'Platform Fee', 'Final Amount'],
        ];

        foreach ($settlement->items as $item) {
            $lines[] = [
                $item->orderItem?->order?->order_number,
                $item->orderItem?->product_name,
                $item->eligible_quantity,
                $item->gross_amount,
                $item->platform_fee_total,
                $item->final_amount,
            ];
        }

        $csv = collect($lines)
            ->map(fn (array $row): string => collect($row)->map(fn ($value): string => '"'.str_replace('"', '""', (string) $value).'"')->implode(','))
            ->implode("\n");

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$settlement->settlement_number.'.csv"',
        ]);
    }
}
