<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SellerAuditLog;
use App\Models\SellerOnboardingPayment;
use App\Models\SellerPlan;
use App\Models\SellerPlanPayment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SellerAccountService
{
    public const POLICIES = [
        'seller_agreement' => 'Seller Agreement',
        'privacy_policy' => 'Privacy Policy',
        'commission_policy' => 'Commission Policy',
        'return_refund_policy' => 'Return & Refund Policy',
    ];

    public function ensureSeedData(): void
    {
        $this->ensureDefaultPlans();
        $this->ensureDefaultCategories();
    }

    public function plans(): array
    {
        $this->ensureDefaultPlans();

        if (Schema::hasTable('seller_plans') && SellerPlan::query()->where('status', SellerPlan::STATUS_ACTIVE)->exists()) {
            return SellerPlan::query()
                ->where('status', SellerPlan::STATUS_ACTIVE)
                ->orderByRaw("case slug when 'free' then 1 when 'growth' then 2 when 'enterprise' then 3 else 9 end")
                ->get()
                ->mapWithKeys(fn (SellerPlan $plan): array => [$plan->slug => $this->normalizePlan($plan)])
                ->all();
        }

        return [
            Vendor::PLAN_FREE => [
                'id' => null,
                'key' => Vendor::PLAN_FREE,
                'slug' => Vendor::PLAN_FREE,
                'name' => 'Free',
                'amount' => 0,
                'price' => 0,
                'currency' => config('services.razorpay.currency', 'INR'),
                'billing_period' => 'none',
                'commission_type' => 'percentage',
                'commission_value' => 1,
                'commission' => 'Rs 1 commission on every product unit sold',
                'product_limit' => 'Unlimited',
                'order_limit' => 'Unlimited',
                'is_paid' => false,
                'validity_days' => null,
                'grace_days' => 0,
                'features' => ['Sushako-branded labels', 'Unlimited time', 'Rs 1 commission on every product unit sold', 'Self shipping', 'Draft to published workflow', 'Professional dashboard'],
                'supporting_text' => 'Start free for unlimited time with a simple Rs 1 unit commission.',
            ],
            Vendor::PLAN_GROWTH => [
                'id' => null,
                'key' => Vendor::PLAN_GROWTH,
                'slug' => Vendor::PLAN_GROWTH,
                'name' => 'Growth',
                'amount' => (int) config('seller.plans.growth.amount', 999),
                'price' => (int) config('seller.plans.growth.amount', 999),
                'currency' => config('services.razorpay.currency', 'INR'),
                'billing_period' => 'monthly',
                'commission_type' => 'none',
                'commission_value' => 0,
                'commission' => 'Zero commission on orders',
                'product_limit' => 100,
                'order_limit' => config('seller.plans.growth.order_limit', 'Unlimited'),
                'is_paid' => true,
                'validity_days' => 30,
                'grace_days' => 0,
                'features' => ['Sushako labelling', 'Up to 100 products', 'Unlimited orders', 'Zero commission on orders', 'Advanced seller dashboard', 'Professional storefront', 'Sales and order reports', 'Marketing tools', 'Priority support', 'One-month plan validity'],
                'supporting_text' => 'Built for growing sellers who want predictable monthly pricing and no commission on orders.',
            ],
            Vendor::PLAN_ENTERPRISE => [
                'id' => null,
                'key' => Vendor::PLAN_ENTERPRISE,
                'slug' => Vendor::PLAN_ENTERPRISE,
                'name' => 'Enterprise',
                'amount' => (int) config('seller.plans.enterprise.amount', 4999),
                'price' => (int) config('seller.plans.enterprise.amount', 4999),
                'currency' => config('services.razorpay.currency', 'INR'),
                'billing_period' => 'monthly',
                'commission_type' => 'none',
                'commission_value' => 0,
                'commission' => 'Zero commission',
                'product_limit' => 'Unlimited',
                'order_limit' => 'Unlimited',
                'is_paid' => true,
                'validity_days' => 30,
                'grace_days' => 5,
                'features' => ['Own branding labels', 'Unlimited products', 'Unlimited orders', 'Zero commission', 'Complete storefront branding', 'Advanced analytics', 'Premium reports', 'Marketing tools', 'Priority support', 'Settlement insights', 'Five-day renewal grace period', 'One-month plan validity'],
                'supporting_text' => 'A complete premium selling suite for established businesses that need scale, branding, and operational control.',
            ],
        ];
    }

    public function normalizePlan(SellerPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'key' => $plan->slug,
            'slug' => $plan->slug,
            'name' => $plan->name,
            'amount' => (int) $plan->price,
            'price' => (int) $plan->price,
            'currency' => config('services.razorpay.currency', 'INR'),
            'billing_period' => $plan->billing_period,
            'commission_type' => $plan->commission_type,
            'commission_value' => (float) $plan->commission_value,
            'commission' => $plan->commission_type === 'flat' ? 'Rs '.number_format((float) $plan->commission_value).' commission on every product unit sold' : ($plan->commission_type === 'none' ? 'Zero commission on orders' : 'Commission charged on every order'),
            'product_limit' => $plan->unlimited_products ? 'Unlimited' : (int) $plan->product_limit,
            'order_limit' => $plan->order_limit ?: 'Unlimited',
            'is_paid' => (bool) $plan->is_paid,
            'validity_days' => $plan->billing_period === 'monthly' ? 30 : null,
            'grace_days' => (int) $plan->grace_period_days,
            'features' => $plan->features ?: [],
            'supporting_text' => $plan->supporting_text,
        ];
    }

    public function ensureVendor(User $user): Vendor
    {
        return DB::transaction(function () use ($user): Vendor {
            $user->forceFill([
                'role' => User::ROLE_SELLER,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'last_login_at' => now(),
            ])->save();

            $vendor = Vendor::query()->firstOrCreate(['user_id' => $user->id], [
                'business_name' => $user->name.' Store',
                'store_display_name' => $user->name.' Store',
                'legal_name' => $user->name,
                'slug' => $this->uniqueSlug($user->name.' Store'),
                'email' => $user->email,
                'phone' => $user->phone ?: '',
                'status' => Vendor::STATUS_ACTIVE,
                'onboarding_status' => 'draft',
                'store_status' => Vendor::STORE_SETUP_REQUIRED,
                'store_visibility' => Vendor::VISIBILITY_DRAFT,
                'approval_status' => Vendor::APPROVAL_REGISTERED,
                'current_plan' => null,
                'selected_plan' => null,
                'plan_status' => Vendor::PLAN_PENDING_PAYMENT,
                'payment_status' => null,
                'razorpay_payment_status' => null,
                'plan_activated_at' => null,
                'plan_expires_at' => null,
                'bank_verification_status' => 'not_submitted',
                'store_timezone' => 'Asia/Kolkata',
                'shipping_commitment' => null,
                'manual_tracking_enabled' => true,
                'vacation_auto_reactivate' => true,
            ]);

            return $vendor->refresh();
        });
    }

    public function redirectFor(Vendor $vendor): string
    {
        if ($vendor->user?->status !== User::STATUS_ACTIVE || ! in_array($vendor->status, [Vendor::STATUS_ACTIVE, 'pending'], true)) {
            return route('seller.status');
        }

        if ($vendor->onboarding_status !== 'complete') {
            return route('seller.onboarding');
        }

        return route('seller.dashboard');
    }

    public function canAccessDashboard(Vendor $vendor): bool
    {
        return $vendor->user?->status === User::STATUS_ACTIVE
            && in_array($vendor->status, [Vendor::STATUS_ACTIVE, 'pending'], true)
            && $vendor->onboarding_status === 'complete'
            && (bool) $vendor->dashboard_access_enabled;
    }

    public function hasActivePlan(Vendor $vendor): bool
    {
        $selectedPlan = $vendor->selected_plan_slug ?: $vendor->selected_plan;

        if ($selectedPlan === Vendor::PLAN_FREE || $vendor->current_plan === Vendor::PLAN_FREE) {
            return $vendor->payment_status === Vendor::PAYMENT_NOT_REQUIRED || $vendor->plan_status === Vendor::PLAN_ACTIVE;
        }

        return in_array($vendor->current_plan, [Vendor::PLAN_GROWTH, Vendor::PLAN_ENTERPRISE], true)
            && $vendor->plan_status === Vendor::PLAN_ACTIVE
            && $vendor->payment_status === Vendor::PAYMENT_PAID
            && $vendor->plan_expires_at?->isFuture();
    }

    public function activateStarter(Vendor $vendor): Vendor
    {
        return DB::transaction(function () use ($vendor): Vendor {
            $plan = $this->plans()[Vendor::PLAN_FREE];

            SellerPlanPayment::query()->firstOrCreate([
                'vendor_id' => $vendor->id,
                'plan' => Vendor::PLAN_FREE,
                'status' => Vendor::PLAN_ACTIVE,
            ], [
                'amount' => 0,
                'activated_at' => now(),
            ]);

            $vendor->forceFill([
                'selected_plan' => Vendor::PLAN_FREE,
                'selected_plan_id' => $plan['id'],
                'selected_plan_slug' => Vendor::PLAN_FREE,
                'selected_plan_snapshot' => $plan,
                'plan_selected_at' => $vendor->plan_selected_at ?? now(),
                'current_plan' => Vendor::PLAN_FREE,
                'plan_status' => Vendor::PLAN_ACTIVE,
                'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
                'razorpay_payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
                'plan_activated_at' => now(),
                'plan_expires_at' => null,
                'grace_starts_at' => null,
                'grace_ends_at' => null,
                'onboarding_status' => 'complete',
                'onboarding_step' => 3,
                'onboarding_completed_at' => $vendor->onboarding_completed_at ?? now(),
                'dashboard_access_enabled' => true,
                'store_status' => Vendor::STORE_LIVE,
                'store_visibility' => Vendor::VISIBILITY_PUBLISHED,
                'published_at' => $vendor->published_at ?? now(),
                'approval_status' => Vendor::APPROVAL_ONBOARDING_COMPLETED,
            ])->save();

            $this->audit($vendor, 'starter_plan_activated', 'Starter onboarding activated.');

            return $vendor->refresh();
        });
    }

    public function activatePaidPlan(Vendor $vendor, SellerOnboardingPayment $payment): Vendor
    {
        $plan = $this->plans()[$payment->plan] ?? null;
        abort_unless($plan && $payment->status === SellerOnboardingPayment::STATUS_PAID, 422);

        $activatedAt = now();
        $baseExpiry = $vendor->plan_expires_at?->isFuture() ? $vendor->plan_expires_at->copy() : $activatedAt->copy();
        $expiresAt = $baseExpiry->copy()->addDays(30);
        $graceEndsAt = (int) ($plan['grace_days'] ?? 0) > 0 ? $expiresAt->copy()->addDays((int) $plan['grace_days']) : null;

        return DB::transaction(function () use ($vendor, $payment, $plan, $activatedAt, $expiresAt, $graceEndsAt): Vendor {
            $vendor->refresh();

            $paymentReference = $payment->provider_payment_id ?: $payment->provider_order_id;

            if ($vendor->planPayments()->where('payment_reference', $paymentReference)->exists()) {
                return $vendor;
            }

            SellerPlanPayment::query()->create([
                'vendor_id' => $vendor->id,
                'plan' => $payment->plan,
                'amount' => $payment->amount,
                'payment_reference' => $paymentReference,
                'status' => Vendor::PLAN_ACTIVE,
                'activated_at' => $activatedAt,
                'expires_at' => $expiresAt,
                'grace_starts_at' => $graceEndsAt ? $expiresAt : null,
                'grace_ends_at' => $graceEndsAt,
            ]);

            $vendor->forceFill([
                'current_plan' => $payment->plan,
                'selected_plan' => $payment->plan,
                'selected_plan_id' => $payment->seller_plan_id ?: ($plan['id'] ?? null),
                'selected_plan_slug' => $payment->plan,
                'selected_plan_snapshot' => $plan,
                'plan_selected_at' => $vendor->plan_selected_at ?? now(),
                'plan_status' => Vendor::PLAN_ACTIVE,
                'payment_status' => Vendor::PAYMENT_PAID,
                'razorpay_payment_status' => Vendor::PAYMENT_PAID,
                'plan_activated_at' => $activatedAt,
                'plan_expires_at' => $expiresAt,
                'grace_starts_at' => $graceEndsAt ? $expiresAt : null,
                'grace_ends_at' => $graceEndsAt,
                'onboarding_status' => 'complete',
                'onboarding_step' => 3,
                'onboarding_completed_at' => $vendor->onboarding_completed_at ?? now(),
                'dashboard_access_enabled' => true,
                'store_status' => Vendor::STORE_SETUP_REQUIRED,
                'store_visibility' => Vendor::VISIBILITY_DRAFT,
                'approval_status' => Vendor::APPROVAL_ONBOARDING_COMPLETED,
            ])->save();

            $this->audit($vendor, 'paid_plan_activated', "Paid plan {$payment->plan} activated.", [
                'payment_id' => $payment->id,
                'provider_payment_id' => $payment->provider_payment_id,
            ]);

            return $vendor->refresh();
        });
    }

    public function storeUploadedFile(UploadedFile $file, string $diskPath, string $disk = 'public'): string
    {
        return $file->storeAs($diskPath, Str::uuid().'.'.$file->extension(), $disk);
    }

    public function activatePlan(Vendor $vendor, string $plan, ?string $reference = null): Vendor
    {
        $amount = match ($plan) {
            Vendor::PLAN_GROWTH => 999,
            Vendor::PLAN_ENTERPRISE => 4999,
            default => 0,
        };
        $activatedAt = now();
        $baseExpiry = $vendor->plan_expires_at?->isFuture() && $vendor->current_plan === $plan
            ? $vendor->plan_expires_at
            : $activatedAt;
        $expiresAt = $plan === Vendor::PLAN_FREE ? null : $baseExpiry->copy()->addDays(30);

        return DB::transaction(function () use ($vendor, $plan, $reference, $amount, $activatedAt, $expiresAt): Vendor {
            SellerPlanPayment::query()->create([
                'vendor_id' => $vendor->id,
                'plan' => $plan,
                'amount' => $amount,
                'payment_reference' => $reference,
                'status' => Vendor::PLAN_ACTIVE,
                'activated_at' => $activatedAt,
                'expires_at' => $expiresAt,
                'grace_starts_at' => $plan === Vendor::PLAN_ENTERPRISE && $expiresAt ? $expiresAt : null,
                'grace_ends_at' => $plan === Vendor::PLAN_ENTERPRISE && $expiresAt ? $expiresAt->copy()->addDays(5) : null,
            ]);

            $vendor->forceFill([
                'current_plan' => $plan,
                'selected_plan' => $plan,
                'selected_plan_slug' => $plan,
                'plan_status' => Vendor::PLAN_ACTIVE,
                'payment_status' => $plan === Vendor::PLAN_FREE ? Vendor::PAYMENT_NOT_REQUIRED : Vendor::PAYMENT_PAID,
                'plan_activated_at' => $activatedAt,
                'plan_expires_at' => $expiresAt,
                'grace_starts_at' => $plan === Vendor::PLAN_ENTERPRISE && $expiresAt ? $expiresAt : null,
                'grace_ends_at' => $plan === Vendor::PLAN_ENTERPRISE && $expiresAt ? $expiresAt->copy()->addDays(5) : null,
            ])->save();

            $this->audit($vendor, 'plan_activated', "Plan {$plan} activated.", ['amount' => $amount, 'reference' => $reference]);

            return $vendor->refresh();
        });
    }

    public function readiness(Vendor $vendor): array
    {
        $checklist = $this->storeSetupChecklist($vendor);
        $items = collect($checklist['items'])->mapWithKeys(fn (array $item): array => [$item['label'] => $item['complete']])->all();

        return [
            'items' => $items,
            'complete' => $checklist['complete'],
            'total' => $checklist['total'],
            'percentage' => $checklist['percentage'],
            'ready' => $checklist['ready'],
            'publish_eligible' => $checklist['publish_eligible'],
            'missing_mandatory' => $checklist['missing_mandatory'],
            'optional_improvements' => $checklist['optional_improvements'],
            'fully_complete' => $checklist['fully_complete'],
        ];
    }

    public function storeSetupChecklist(Vendor $vendor): array
    {
        $vendor->loadMissing(['products', 'policyAcceptances', 'storefrontCategories']);
        $accepted = $vendor->policyAcceptances->pluck('policy_key')->all();
        $policiesAccepted = collect(array_keys(self::POLICIES))->diff($accepted)->isEmpty();
        $hasProduct = $vendor->products()->exists();
        $hasAdditionalProducts = $vendor->products()->count() >= 3;
        $hasAdditionalCategories = $vendor->storefrontCategories->count() >= 2;

        $items = collect([
            ['key' => 'business_identity', 'label' => 'Business Identity', 'url' => route('seller.store-profile'), 'complete' => filled($vendor->business_name) && filled($vendor->store_display_name), 'weight' => 10, 'mandatory' => true],
            ['key' => 'business_contact', 'label' => 'Business Contact', 'url' => route('seller.store-profile'), 'complete' => filled($vendor->phone) && filled($vendor->email), 'weight' => 10, 'mandatory' => true],
            ['key' => 'store_location', 'label' => 'Store / Pickup Location', 'url' => route('seller.shipping.index'), 'complete' => filled($vendor->address_line_1) && filled($vendor->city) && filled($vendor->postal_code), 'weight' => 10, 'mandatory' => true],
            ['key' => 'delivery_configuration', 'label' => 'Delivery Configuration', 'url' => route('seller.shipping.index'), 'complete' => filled($vendor->pickup_address) && filled($vendor->delivery_radius) && filled($vendor->shipping_commitment), 'weight' => 10, 'mandatory' => true],
            ['key' => 'storefront_category', 'label' => 'Seller Storefront Category', 'url' => route('seller.categories.index'), 'complete' => $vendor->storefrontCategories->isNotEmpty(), 'weight' => 10, 'mandatory' => true],
            ['key' => 'first_product', 'label' => 'At least one Product', 'url' => route('seller.products.create'), 'complete' => $hasProduct, 'weight' => 15, 'mandatory' => true],
            ['key' => 'plan_activation', 'label' => 'Plan Selected and Active', 'url' => route('seller.plans.index'), 'complete' => $vendor->onboarding_status === 'complete' && $this->hasActivePlan($vendor), 'weight' => 10, 'mandatory' => true],
            ['key' => 'store_logo', 'label' => 'Store Logo', 'url' => route('seller.store-profile'), 'complete' => filled($vendor->business_logo_path ?: $vendor->logo), 'weight' => 5, 'mandatory' => false],
            ['key' => 'business_description', 'label' => 'Business Description', 'url' => route('seller.store-profile'), 'complete' => filled($vendor->store_description), 'weight' => 5, 'mandatory' => false],
            ['key' => 'working_hours', 'label' => 'Working Hours', 'url' => route('seller.settings.index'), 'complete' => filled($vendor->working_days) || (filled($vendor->opens_at) && filled($vendor->closes_at)), 'weight' => 3, 'mandatory' => false],
            ['key' => 'bank_details', 'label' => 'Bank Details', 'url' => route('seller.settlements.index'), 'complete' => filled($vendor->bank_account_holder_name) && filled($vendor->bank_account_number) && filled($vendor->bank_ifsc), 'weight' => 5, 'mandatory' => false],
            ['key' => 'tax_preferences', 'label' => 'Tax Preferences', 'url' => route('seller.settings.index'), 'complete' => in_array($vendor->tax_preference, ['gst_included', 'gst_excluded'], true), 'weight' => 3, 'mandatory' => false],
            ['key' => 'additional_products', 'label' => 'Additional Products', 'url' => route('seller.products.create'), 'complete' => $hasAdditionalProducts, 'weight' => 1, 'mandatory' => false],
            ['key' => 'additional_categories', 'label' => 'Additional Categories', 'url' => route('seller.categories.index'), 'complete' => $hasAdditionalCategories, 'weight' => 3, 'mandatory' => false],
            ['key' => 'legal_policies', 'label' => 'Legal Policies', 'url' => route('seller.settings.index'), 'complete' => $policiesAccepted, 'weight' => 0, 'mandatory' => false],
        ]);

        $complete = $items->where('complete', true)->count();
        $total = $items->count();
        $percentage = (int) min(100, $items->filter(fn (array $item): bool => (bool) $item['complete'])->sum('weight'));
        $missingMandatory = $items->filter(fn (array $item): bool => $item['mandatory'] && ! $item['complete'])->values();
        $optional = $items->filter(fn (array $item): bool => ! $item['mandatory'])->values();
        $eligible = $percentage >= 75 && $missingMandatory->isEmpty();

        return [
            'items' => $items->all(),
            'complete' => $complete,
            'total' => $total,
            'percentage' => $percentage,
            'remaining' => $items->where('complete', false)->pluck('label')->values()->all(),
            'missing_mandatory' => $missingMandatory->pluck('label')->all(),
            'optional_improvements' => $optional->where('complete', false)->pluck('label')->all(),
            'publish_eligible' => $eligible,
            'fully_complete' => $percentage === 100,
            'ready' => $eligible,
        ];
    }

    public function publish(Vendor $vendor): bool
    {
        $readiness = $this->readiness($vendor);

        if (! $readiness['publish_eligible']) {
            return false;
        }

        $vendor->forceFill([
            'store_status' => Vendor::STORE_LIVE,
            'store_visibility' => Vendor::VISIBILITY_PUBLISHED,
            'approval_status' => Vendor::APPROVAL_APPROVED,
            'setup_completed_at' => $vendor->setup_completed_at ?? now(),
            'onboarding_status' => 'complete',
            'onboarding_completed_at' => $vendor->onboarding_completed_at ?? now(),
            'published_at' => $vendor->published_at ?? now(),
            'published_by' => auth()->id(),
        ])->save();

        $this->audit($vendor, 'store_published', 'Seller published store after reaching launch readiness.', [
            'readiness_percentage' => $readiness['percentage'],
        ]);

        return true;
    }

    public function downgradeExpiredPlans(): int
    {
        $count = 0;

        Vendor::query()
            ->whereIn('current_plan', [Vendor::PLAN_GROWTH, Vendor::PLAN_ENTERPRISE])
            ->whereNotNull('plan_expires_at')
            ->where(function ($query): void {
                $query->where(fn ($growth) => $growth->where('current_plan', Vendor::PLAN_GROWTH)->where('plan_expires_at', '<', now()))
                    ->orWhere(fn ($enterprise) => $enterprise->where('current_plan', Vendor::PLAN_ENTERPRISE)->where('grace_ends_at', '<', now()));
            })
            ->each(function (Vendor $vendor) use (&$count): void {
                $vendor->forceFill([
                    'current_plan' => Vendor::PLAN_FREE,
                    'plan_status' => Vendor::PLAN_DOWNGRADED,
                    'plan_expires_at' => null,
                    'grace_starts_at' => null,
                    'grace_ends_at' => null,
                ])->save();

                $this->audit($vendor, 'plan_downgraded', 'Expired paid plan moved to Free Plan.');
                $count++;
            });

        return $count;
    }

    public function sellerCategories(): array
    {
        $this->ensureDefaultCategories();

        return Category::query()->where('is_active', true)->where('available_to_sellers', true)->orderBy('name')->get()->all();
    }

    public function audit(Vendor $vendor, string $action, ?string $reason = null, array $metadata = []): void
    {
        SellerAuditLog::query()->create([
            'vendor_id' => $vendor->id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    private function ensureDefaultPlans(): void
    {
        if (! Schema::hasTable('seller_plans')) {
            return;
        }

        foreach ([
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'billing_period' => 'none',
                'product_limit' => 25,
                'unlimited_products' => false,
                'order_limit' => 'Unlimited',
                'commission_type' => 'flat',
                'commission_value' => 1,
                'is_paid' => false,
                'grace_period_days' => 0,
                'status' => SellerPlan::STATUS_ACTIVE,
                'features' => ['Unlimited time', 'Rs 1 commission on every product unit sold', 'Self shipping', 'Draft to published workflow', 'Professional dashboard'],
                'supporting_text' => 'Start free for unlimited time with a simple Rs 1 unit commission.',
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'price' => 999,
                'billing_period' => 'monthly',
                'product_limit' => 100,
                'unlimited_products' => false,
                'order_limit' => 'Unlimited',
                'commission_type' => 'none',
                'commission_value' => 0,
                'is_paid' => true,
                'grace_period_days' => 0,
                'status' => SellerPlan::STATUS_ACTIVE,
                'features' => ['Up to 100 products', 'Unlimited orders', 'Zero commission on orders', 'Advanced seller dashboard', 'Professional storefront', 'Sales and order reports', 'Marketing tools', 'Seller labelling features available as an add-on', 'Priority support', 'One-month plan validity'],
                'supporting_text' => 'Built for growing sellers who want predictable monthly pricing and no commission on orders.',
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => 4999,
                'billing_period' => 'monthly',
                'product_limit' => null,
                'unlimited_products' => true,
                'order_limit' => 'Unlimited',
                'commission_type' => 'none',
                'commission_value' => 0,
                'is_paid' => true,
                'grace_period_days' => 5,
                'status' => SellerPlan::STATUS_ACTIVE,
                'features' => ['Unlimited products', 'Unlimited orders', 'Zero commission', 'Complete storefront branding', 'Advanced analytics', 'Premium reports', 'Marketing tools', 'Seller labelling included', 'Priority support', 'Settlement insights', 'Five-day renewal grace period', 'One-month plan validity'],
                'supporting_text' => 'A complete premium selling suite for established businesses that need scale, branding, and operational control.',
            ],
        ] as $plan) {
            SellerPlan::query()->updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }

    private function ensureDefaultCategories(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        $defaultCategories = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Default category for starter seller onboarding.',
                'is_active' => true,
                'available_to_sellers' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Fashion and accessories for marketplace sellers.',
                'is_active' => true,
                'available_to_sellers' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($defaultCategories as $category) {
            Category::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'seller-store';
        $slug = $base;
        $count = 2;

        while (Vendor::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }
}
