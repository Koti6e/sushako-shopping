<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductShippingOverride;
use App\Services\SellerShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(Request $request, SellerShippingService $shipping): View
    {
        $vendor = $request->attributes->get('vendor');
        return view('seller.simple.shipping', ['vendor' => $vendor, 'profile' => $shipping->profile($vendor), 'products' => Product::query()->where('vendor_id', $vendor->id)->with('shippingOverride')->orderBy('name')->get()]);
    }

    public function update(Request $request, SellerShippingService $shipping): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'default_method' => ['nullable', 'string', 'max:120'], 'free_shipping_threshold' => ['nullable', 'integer', 'min:0'], 'free_local_pickup' => ['nullable', 'boolean'],
            'zones' => ['array'], 'zones.*.name' => ['required', 'string', 'max:120'], 'zones.*.coverage_type' => ['required', 'in:local,district,state,pincodes,nationwide,restricted'], 'zones.*.charge_type' => ['required', 'in:flat,weight,order_value'], 'zones.*.base_charge' => ['required', 'integer', 'min:0'], 'zones.*.per_kg_charge' => ['nullable', 'integer', 'min:0'], 'zones.*.free_above_value' => ['nullable', 'integer', 'min:0'], 'zones.*.coverage' => ['nullable', 'array'], 'zones.*.is_active' => ['nullable', 'boolean'],
            'address_line_1' => ['nullable', 'string', 'max:255'], 'address_line_2' => ['nullable', 'string', 'max:255'], 'city' => ['nullable', 'string', 'max:120'], 'district' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'string', 'max:120'], 'postal_code' => ['nullable', 'regex:/^\d{6}$/'], 'pickup_address' => ['nullable', 'string', 'max:600'], 'return_address' => ['nullable', 'string', 'max:600'], 'shipping_commitment' => ['nullable', 'string', 'max:80'], 'preferred_courier_name' => ['nullable', 'string', 'max:120'], 'flat_shipping_charge' => ['nullable', 'numeric', 'min:0'], 'free_shipping_threshold_legacy' => ['nullable', 'numeric', 'min:0'], 'delivery_radius' => ['nullable', 'integer', 'min:0'], 'working_days' => ['array'], 'working_days.*' => ['in:mon,tue,wed,thu,fri,sat,sun'], 'opens_at' => ['nullable', 'date_format:H:i'], 'closes_at' => ['nullable', 'date_format:H:i'], 'manual_tracking_enabled' => ['nullable', 'boolean'],
        ]);
        $shipping->save($vendor, $data);
        $vendor->forceFill(collect($data)->only(['address_line_1', 'address_line_2', 'city', 'district', 'state', 'postal_code', 'pickup_address', 'return_address', 'shipping_commitment', 'preferred_courier_name', 'flat_shipping_charge', 'free_shipping_threshold', 'delivery_radius', 'working_days', 'opens_at', 'closes_at'])->filter(fn ($value) => $value !== null)->all() + ['manual_tracking_enabled' => $request->boolean('manual_tracking_enabled'), 'country' => 'India'])->save();
        return back()->with('status', 'Shipping profile and delivery zones saved.');
    }

    public function addZone(Request $request, SellerShippingService $shipping): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'coverage_type' => ['required', 'in:local,district,state,pincodes,nationwide,restricted'], 'charge_type' => ['required', 'in:flat,weight,order_value'], 'base_charge' => ['required', 'integer', 'min:0'], 'coverage' => ['nullable', 'array']]);
        $shipping->addZone($vendor, $data);
        return back()->with('status', 'Delivery zone added.');
    }

    public function override(Request $request, Product $product): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        abort_unless($product->vendor_id === $vendor->id, 404);
        $data = $request->validate(['mode' => ['required', 'in:flat,weight,free,digital'], 'charge' => ['required', 'integer', 'min:0'], 'method' => ['nullable', 'string', 'max:120'], 'is_active' => ['nullable', 'boolean']]);
        ProductShippingOverride::query()->updateOrCreate(['product_id' => $product->id], ['vendor_id' => $vendor->id, ...$data, 'is_active' => $request->boolean('is_active')]);
        return back()->with('status', 'Product shipping override saved.');
    }
}
