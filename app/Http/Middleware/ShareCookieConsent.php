<?php

namespace App\Http\Middleware;

use App\Services\CookieConsentService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCookieConsent
{
    public function __construct(private readonly CookieConsentService $cookieConsent)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $accepted = $this->cookieConsent->hasAccepted($request);

        View::share('essentialCookieConsentAccepted', $accepted);
        View::share('showEssentialCookieBanner', ! $accepted);

        return $next($request);
    }
}
