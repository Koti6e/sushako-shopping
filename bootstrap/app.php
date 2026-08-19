<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureCustomerHasMobile;
use App\Http\Middleware\EnsureSellerAccountActive;
use App\Http\Middleware\EnsureSellerHasProfile;
use App\Http\Middleware\EnsureSellerOnboardingCompleted;
use App\Http\Middleware\EnsureSellerPlanActivated;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ShareCookieConsent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', ShareCookieConsent::class);
        $middleware->validateCsrfTokens(except: [
            'seller/onboarding/payment/webhook',
        ]);

        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
            'mobile.required' => EnsureCustomerHasMobile::class,
            'role' => EnsureUserHasRole::class,
            'seller.active' => EnsureSellerAccountActive::class,
            'seller.onboarded' => EnsureSellerOnboardingCompleted::class,
            'seller.profile' => EnsureSellerHasProfile::class,
            'seller.plan' => EnsureSellerPlanActivated::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            if ($request->is('seller/*')) {
                return route('seller.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
