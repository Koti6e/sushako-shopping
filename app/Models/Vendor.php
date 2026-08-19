<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'business_name', 'legal_name', 'slug', 'email', 'phone', 'alternate_phone', 'business_type', 'tax_number', 'registration_number', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'logo', 'status', 'onboarding_status', 'approved_at', 'approved_by', 'rejection_reason', 'store_display_name', 'store_description', 'business_logo_path', 'pan_number', 'gstin', 'website', 'area', 'landmark', 'pickup_address', 'pickup_address_line_1', 'pickup_city', 'pickup_state', 'pickup_postal_code', 'return_address', 'working_days', 'opens_at', 'closes_at', 'order_cutoff_at', 'support_hours', 'delivery_radius', 'radius_unit', 'store_timezone', 'shipping_commitment', 'flat_shipping_charge', 'free_shipping_threshold', 'returns_accepted', 'return_window_days', 'return_policy_summary', 'local_delivery_preference', 'preferred_courier_name', 'manual_tracking_enabled', 'store_status', 'current_plan', 'plan_status', 'plan_activated_at', 'plan_expires_at', 'grace_starts_at', 'grace_ends_at', 'onboarding_step', 'onboarding_completed_at', 'published_at', 'vacation_starts_at', 'vacation_ends_at', 'vacation_message', 'vacation_auto_reactivate', 'bank_account_holder_name', 'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch_name', 'bank_account_type', 'bank_upi_id', 'bank_document_path', 'bank_verification_status', 'owner_name', 'business_category', 'years_in_business', 'store_tagline', 'store_banner_path', 'store_support_number', 'store_support_email', 'gst_status', 'legal_compliance_confirmed_at', 'gst_certificate_path', 'pan_document_path', 'district', 'address_latitude', 'address_longitude', 'billing_address', 'use_pickup_as_return', 'use_pickup_as_billing', 'delivery_settings', 'settlement_cycle', 'minimum_settlement_amount', 'pending_settlement_notice', 'commission_deductions_note', 'razorpay_payment_status', 'selected_plan', 'selected_plan_id', 'selected_plan_slug', 'selected_plan_snapshot', 'plan_selected_at', 'payment_status', 'dashboard_access_enabled', 'business_name_edit_count', 'tax_preference', 'approval_status', 'approval_rejection_reason', 'store_visibility', 'setup_completed_at', 'published_by'])]
class Vendor extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_INACTIVE = 'inactive';

    public const STORE_SETUP_REQUIRED = 'setup_required';

    public const STORE_READY = 'ready_to_publish';

    public const STORE_LIVE = 'live';

    public const STORE_VACATION = 'vacation';

    public const STORE_TEMPORARILY_CLOSED = 'temporarily_closed';

    public const STORE_SUSPENDED = 'suspended';

    public const VISIBILITY_DRAFT = 'draft';

    public const VISIBILITY_PUBLISHED = 'published';

    public const VISIBILITY_HIDDEN = 'hidden';

    public const APPROVAL_REGISTERED = 'registered';

    public const APPROVAL_ONBOARDING_COMPLETED = 'onboarding_completed';

    public const APPROVAL_PRODUCTS_ADDED = 'products_added';

    public const APPROVAL_SETUP_COMPLETE = 'store_setup_complete';

    public const APPROVAL_PENDING_ADMIN = 'pending_admin_approval';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

    public const PLAN_FREE = 'free';

    public const PLAN_GROWTH = 'growth';

    public const PLAN_ENTERPRISE = 'enterprise';

    public const PLAN_ACTIVE = 'active';

    public const PLAN_PENDING_PAYMENT = 'pending_payment';

    public const PLAN_EXPIRED = 'expired';

    public const PLAN_GRACE_PERIOD = 'grace_period';

    public const PLAN_DOWNGRADED = 'downgraded';

    public const PAYMENT_NOT_REQUIRED = 'not_required';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'seller_categories')->withTimestamps();
    }

    public function storefrontCategories(): HasMany
    {
        return $this->hasMany(SellerStorefrontCategory::class);
    }

    public function categoryRequests(): HasMany
    {
        return $this->hasMany(SellerCategoryRequest::class);
    }

    public function policyAcceptances(): HasMany
    {
        return $this->hasMany(SellerPolicyAcceptance::class);
    }

    public function planPayments(): HasMany
    {
        return $this->hasMany(SellerPlanPayment::class);
    }

    public function selectedSellerPlan(): BelongsTo
    {
        return $this->belongsTo(SellerPlan::class, 'selected_plan_id');
    }

    public function onboardingPayments(): HasMany
    {
        return $this->hasMany(SellerOnboardingPayment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SellerLedgerEntry::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(SellerSettlement::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SellerNotification::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(SellerHoliday::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SellerAuditLog::class);
    }

    public function maskedBankAccount(): string
    {
        $account = (string) $this->bank_account_number;

        return $account === '' ? 'Not submitted' : str_repeat('*', max(0, strlen($account) - 4)).substr($account, -4);
    }

    protected function casts(): array
    {
        return [
            'working_days' => 'array',
            'local_delivery_preference' => 'boolean',
            'manual_tracking_enabled' => 'boolean',
            'vacation_auto_reactivate' => 'boolean',
            'use_pickup_as_return' => 'boolean',
            'use_pickup_as_billing' => 'boolean',
            'delivery_settings' => 'array',
            'returns_accepted' => 'boolean',
            'billing_address' => 'array',
            'selected_plan_snapshot' => 'array',
            'plan_selected_at' => 'datetime',
            'plan_activated_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'grace_starts_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'published_at' => 'datetime',
            'legal_compliance_confirmed_at' => 'datetime',
            'dashboard_access_enabled' => 'boolean',
            'setup_completed_at' => 'datetime',
            'business_name_edit_count' => 'integer',
            'vacation_starts_at' => 'datetime',
            'vacation_ends_at' => 'datetime',
            'bank_account_number' => 'encrypted',
            'bank_ifsc' => 'encrypted',
            'bank_upi_id' => 'encrypted',
        ];
    }
}
