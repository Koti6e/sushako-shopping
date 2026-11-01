<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\FeaturedCampaign;
use App\Models\Product;
use App\Services\FeaturedCampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');
        $campaigns = Schema::hasTable('featured_campaigns')
            ? FeaturedCampaign::query()->where('vendor_id', $vendor->id)->with('product')->latest()->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('seller.promotions.index', ['vendor' => $vendor, 'products' => Product::query()->where('vendor_id', $vendor->id)->where('is_published', true)->where('seller_status', Product::SELLER_STATUS_APPROVED)->with('images')->latest()->get(), 'campaigns' => $campaigns]);
    }

    public function create(Request $request, FeaturedCampaignService $campaigns): JsonResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);
        $campaign = $campaigns->createPayment($vendor, Product::query()->findOrFail($data['product_id']));
        return response()->json(['campaign_id' => $campaign->id, 'key' => config('services.razorpay.key'), 'order_id' => $campaign->razorpay_order_id, 'amount' => FeaturedCampaign::PRICE * 100, 'currency' => 'INR']);
    }

    public function verify(Request $request, FeaturedCampaign $campaign, FeaturedCampaignService $campaigns): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate(['razorpay_payment_id' => ['required', 'string'], 'razorpay_order_id' => ['required', 'string'], 'razorpay_signature' => ['required', 'string']]);
        $campaigns->verify($vendor, $campaign, $data);
        return back()->with('status', 'Featured campaign activated for seven days.');
    }
}
