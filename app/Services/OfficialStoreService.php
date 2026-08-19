<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OfficialStoreService
{
    public const SLUG = 'sushako-official-store';

    public const NAME = 'Sushako Official Store';

    public function ensure(?User $owner = null): Vendor
    {
        $owner ??= User::query()
            ->where('role', User::ROLE_SUPER_ADMIN)
            ->where('status', User::STATUS_ACTIVE)
            ->oldest()
            ->first();

        if (! $owner) {
            $owner = User::query()->firstOrCreate(
                ['email' => env('SUSHAKO_SUPER_ADMIN_EMAIL', 'superadmin@sushako.test')],
                [
                    'name' => 'Sushako Super Admin',
                    'phone' => '9000000001',
                    'password' => Hash::make(env('SUSHAKO_DEV_PASSWORD', Str::random(32))),
                    'role' => User::ROLE_SUPER_ADMIN,
                    'status' => User::STATUS_ACTIVE,
                ]
            );
        }

        $vendor = Vendor::query()
            ->where('slug', self::SLUG)
            ->orWhere('user_id', $owner->id)
            ->first() ?: new Vendor;

        $vendor->forceFill([
            'user_id' => $owner->id,
            'business_name' => self::NAME,
            'legal_name' => 'Sushako Shopping',
            'slug' => self::SLUG,
            'store_display_name' => self::NAME,
            'store_description' => 'Verified official Sushako marketplace store managed by the Super Admin team.',
            'email' => $owner->email,
            'phone' => $owner->phone ?: '9000000001',
            'business_type' => 'marketplace_official_store',
            'address_line_1' => 'Sushako Operations Center',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'postal_code' => '600001',
            'country' => 'India',
            'status' => Vendor::STATUS_ACTIVE,
            'onboarding_status' => 'complete',
            'approval_status' => Vendor::APPROVAL_APPROVED,
            'store_status' => Vendor::STORE_LIVE,
            'store_visibility' => Vendor::VISIBILITY_PUBLISHED,
            'current_plan' => Vendor::PLAN_ENTERPRISE,
            'selected_plan' => Vendor::PLAN_ENTERPRISE,
            'selected_plan_slug' => Vendor::PLAN_ENTERPRISE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
            'setup_completed_at' => $vendor->setup_completed_at ?: now(),
            'approved_at' => $vendor->approved_at ?: now(),
            'published_at' => $vendor->published_at ?: now(),
            'published_by' => $vendor->published_by ?: $owner->id,
        ])->save();

        return $vendor;
    }

    public function isOfficial(?Vendor $vendor): bool
    {
        return $vendor?->slug === self::SLUG;
    }
}
