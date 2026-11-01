<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * Keeps old subscription URLs safe for bookmarks without exposing a purchase flow.
 */
class LegacySubscriptionController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return redirect()->route('seller.onboarding');
    }
}
