<?php

namespace App\Services;

use App\Models\CookieConsent;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CookieConsentService
{
    public const COOKIE_NAME = 'essential_cookies';

    public const COOKIE_VALUE = 'accepted';

    public const CONSENT_TYPE = 'essential';

    public const COOKIE_DAYS = 365;

    public function hasAccepted(Request $request): bool
    {
        if ($request->cookie(self::COOKIE_NAME) === self::COOKIE_VALUE) {
            return true;
        }

        return CookieConsent::query()
            ->where('consent_type', self::CONSENT_TYPE)
            ->where('accepted', true)
            ->where(function ($query) use ($request): void {
                if ($request->user()) {
                    $query->where('user_id', $request->user()->getKey());
                } else {
                    $query->whereNull('user_id')
                        ->where('session_id', $request->session()->getId());
                }
            })
            ->exists();
    }

    public function accept(Request $request, ?User $user = null): CookieConsent
    {
        $consent = CookieConsent::query()
            ->where('consent_type', self::CONSENT_TYPE)
            ->where(function ($query) use ($request, $user): void {
                if ($user) {
                    $query->where('user_id', $user->getKey())
                        ->orWhere(function ($query) use ($request): void {
                            $query->whereNull('user_id')->where('session_id', $request->session()->getId());
                        });

                    return;
                }

                $query->whereNull('user_id')->where('session_id', $request->session()->getId());
            })
            ->first() ?? new CookieConsent([
                'consent_type' => self::CONSENT_TYPE,
            ]);

        $consent->fill([
            'user_id' => $user?->getKey(),
            'session_id' => $request->session()->getId(),
            'accepted' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => now(),
        ])->save();

        return $consent;
    }

    public function consentCookie(): Cookie
    {
        return cookie(
            self::COOKIE_NAME,
            self::COOKIE_VALUE,
            self::COOKIE_DAYS * 24 * 60,
            '/',
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            'lax'
        );
    }
}
