<?php

namespace App\Http\Controllers;

use App\Services\CookieConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CookieConsentController extends Controller
{
    public function store(Request $request, CookieConsentService $cookieConsent): RedirectResponse|Response
    {
        $cookieConsent->accept($request, $request->user());

        if ($request->expectsJson()) {
            return response()->noContent()->withCookie($cookieConsent->consentCookie());
        }

        return back()->withCookie($cookieConsent->consentCookie());
    }
}
