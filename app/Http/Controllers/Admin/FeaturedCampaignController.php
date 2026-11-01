<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedCampaign;
use App\Models\SellerAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeaturedCampaignController extends Controller
{
    public function index(): View
    {
        return view('admin/advertising/index', ['campaigns' => FeaturedCampaign::query()->with(['vendor', 'product'])->latest()->paginate(25)]);
    }

    public function status(Request $request, FeaturedCampaign $campaign): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:active,paused,rejected']]);
        $campaign->forceFill(['status' => $data['status']])->save();
        SellerAuditLog::query()->create(['vendor_id' => $campaign->vendor_id, 'actor_user_id' => $request->user()->id, 'action' => 'featured_campaign_status_updated', 'reason' => 'Super Admin updated advertising campaign status.', 'metadata' => ['campaign_id' => $campaign->id, 'status' => $data['status']]]);
        return back()->with('status', 'Advertising campaign status updated.');
    }
}
