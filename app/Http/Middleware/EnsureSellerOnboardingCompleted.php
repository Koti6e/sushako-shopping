<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = $request->attributes->get('vendor');

        abort_unless($vendor instanceof Vendor, 403);

        if ($vendor->onboarding_status !== 'complete') {
            $selectedPlan = $vendor->selected_plan_slug ?: $vendor->selected_plan;

            if ($selectedPlan && $selectedPlan !== Vendor::PLAN_FREE && in_array($vendor->payment_status, [Vendor::PAYMENT_PENDING, Vendor::PAYMENT_FAILED], true)) {
                $hasPaymentOrder = $vendor->onboardingPayments()
                    ->whereIn('status', ['pending', 'failed'])
                    ->exists();

                if ($hasPaymentOrder) {
                    return redirect()->route('seller.onboarding.payment');
                }
            }

            return redirect()->route('seller.onboarding');
        }

        return $next($request);
    }
}
