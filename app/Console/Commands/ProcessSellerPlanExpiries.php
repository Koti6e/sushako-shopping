<?php

namespace App\Console\Commands;

use App\Models\SellerNotification;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use Illuminate\Console\Command;

class ProcessSellerPlanExpiries extends Command
{
    protected $signature = 'sellers:process-plan-expiries';

    protected $description = 'Process seller paid-plan reminders, grace periods and downgrades idempotently.';

    public function handle(SellerAccountService $accounts): int
    {
        Vendor::query()
            ->whereIn('current_plan', [Vendor::PLAN_GROWTH, Vendor::PLAN_ENTERPRISE])
            ->whereNotNull('plan_expires_at')
            ->each(function (Vendor $vendor): void {
                $days = now()->diffInDays($vendor->plan_expires_at, false);

                foreach ([7, 3, 1] as $stage) {
                    if ((int) $days === $stage) {
                        SellerNotification::query()->firstOrCreate([
                            'vendor_id' => $vendor->id,
                            'dedupe_key' => "plan-expiry-{$stage}-{$vendor->plan_expires_at?->toDateString()}",
                        ], [
                            'type' => 'plan_expiring',
                            'title' => "Your {$vendor->current_plan} plan expires in {$stage} day".($stage === 1 ? '' : 's').'.',
                            'body' => 'Renew manually to continue the current seller plan entitlement. This does not auto-renew.',
                            'action_url' => route('seller.plans.index'),
                        ]);
                    }
                }

                if ($vendor->current_plan === Vendor::PLAN_ENTERPRISE && $vendor->plan_expires_at?->isPast() && $vendor->grace_ends_at?->isFuture()) {
                    $vendor->forceFill(['plan_status' => Vendor::PLAN_GRACE_PERIOD])->save();
                    SellerNotification::query()->firstOrCreate([
                        'vendor_id' => $vendor->id,
                        'dedupe_key' => 'enterprise-grace-'.$vendor->grace_ends_at?->toDateString(),
                    ], [
                        'type' => 'grace_period_active',
                        'title' => 'Enterprise grace period is active.',
                        'body' => 'Renew before grace ends to keep Enterprise entitlements.',
                        'action_url' => route('seller.plans.index'),
                    ]);
                }
            });

        $downgraded = $accounts->downgradeExpiredPlans();
        $this->info("Processed seller plan expiries. Downgraded {$downgraded} seller(s).");

        return self::SUCCESS;
    }
}
