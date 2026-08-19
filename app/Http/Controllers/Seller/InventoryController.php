<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\SellerInventoryAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.inventory.index', [
            'vendor' => $vendor,
            'variants' => ProductVariant::query()
                ->with('product.images')
                ->whereHas('product', fn ($query) => $query->where('vendor_id', $vendor->id))
                ->latest()
                ->paginate(20),
        ]);
    }

    public function adjust(Request $request, ProductVariant $variant): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($variant->product?->vendor_id === $vendor->id, 404);
        $data = $request->validate([
            'mode' => ['required', 'in:increase,decrease,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:5', 'max:180'],
        ]);

        DB::transaction(function () use ($variant, $data, $vendor, $request): void {
            $variant->refresh();
            $old = $variant->stock;
            $new = match ($data['mode']) {
                'increase' => $old + $data['quantity'],
                'decrease' => max(0, $old - $data['quantity']),
                default => $data['quantity'],
            };

            $variant->forceFill(['stock' => $new])->save();

            SellerInventoryAdjustment::query()->create([
                'vendor_id' => $vendor->id,
                'product_variant_id' => $variant->id,
                'quantity_delta' => $new - $old,
                'stock_after' => $new,
                'reason' => $data['reason'],
                'adjusted_by' => $request->user()->id,
            ]);
        });

        return back()->with('status', 'Inventory updated.');
    }
}
