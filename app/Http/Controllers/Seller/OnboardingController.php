<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerOnboardingPayment;
use App\Models\SellerPlan;
use App\Models\SellerPolicyAcceptance;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use App\Services\SellerOnboardingPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public const STEPS = [
        1 => 'Business Details',
        2 => 'Delivery Setup',
        3 => 'Choose Plan',
    ];

    public const BUSINESS_TYPES = [
        'individual' => 'Individual',
        'proprietorship' => 'Proprietorship',
        'partnership' => 'Partnership',
        'llp' => 'LLP',
        'private_limited' => 'Private Limited',
        'home_business' => 'Home Business',
        'other' => 'Other',
    ];

    public const INDIAN_STATES = [
        'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh',
        'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa', 'Gujarat', 'Haryana',
        'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry',
        'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand',
        'West Bengal',
    ];

    public const LEGAL_ACCEPTANCES = [
        'terms_privacy',
        'plan_fees',
    ];

    public function show(Request $request, SellerAccountService $accounts): View|RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();

        if ($vendor->onboarding_status === 'complete') {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.onboarding', $this->onboardingViewData($request, $vendor, $accounts));
    }

    public function step(string $step): RedirectResponse
    {
        return redirect()->route('seller.onboarding');
    }

    public function showPlanSelection(): RedirectResponse
    {
        return redirect()->route('seller.onboarding');
    }

    public function selectPlan(): RedirectResponse
    {
        return redirect()->route('seller.onboarding');
    }

    public function review(): RedirectResponse
    {
        return redirect()->route('seller.onboarding');
    }

    public function activateFromReview(Request $request, SellerAccountService $accounts, SellerOnboardingPaymentService $payments): RedirectResponse
    {
        return $this->store($request, $accounts, $payments);
    }

    public function store(Request $request, SellerAccountService $accounts, SellerOnboardingPaymentService $payments): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();

        if ($vendor->onboarding_status === 'complete') {
            return redirect()->route('seller.dashboard');
        }

        $data = $request->validate($this->simpleOnboardingRules($request), $this->businessMessages());
        $useBusinessPickup = $request->boolean('pickup_same_as_business', true);
        $pickup = [
            'address' => $useBusinessPickup ? $data['business_address'] : $data['pickup_address'],
            'city' => $useBusinessPickup ? $data['city'] : $data['pickup_city'],
            'state' => $useBusinessPickup ? $data['state'] : $data['pickup_state'],
            'postal_code' => $useBusinessPickup ? $data['postal_code'] : $data['pickup_postal_code'],
        ];
        $returnsAccepted = $data['returns_accepted'] === 'yes';
        $returnWindowDays = $returnsAccepted ? (int) $data['return_window_days'] : null;
        $returnPolicySummary = $returnsAccepted
            ? 'Returns accepted within '.$returnWindowDays.' '.str('day')->plural($returnWindowDays).' from delivery'
            : 'No Returns';

        DB::transaction(function () use ($request, $vendor, $data, $accounts, $useBusinessPickup, $pickup, $returnsAccepted, $returnWindowDays, $returnPolicySummary): void {
            $request->user()->forceFill([
                'phone' => $data['phone'],
                'last_login_at' => now(),
            ])->save();

            $slug = $vendor->slug && $vendor->slug !== 'seller-store'
                ? $vendor->slug
                : $this->uniqueSlug($data['business_name'], $vendor);
            $address = $this->formatAddress($data['business_address'], null, null, $data['city'], $data['state'], $data['postal_code']);
            $pickupAddress = $this->formatAddress($pickup['address'], null, null, $pickup['city'], $pickup['state'], $pickup['postal_code']);
            $hasGst = $data['gst_registered'] === 'yes';
            $logoPath = $request->hasFile('business_logo')
                ? $accounts->storeUploadedFile($request->file('business_logo'), 'seller-logos')
                : $vendor->business_logo_path;

            $vendor->forceFill([
                'business_name' => $data['business_name'],
                'store_display_name' => $data['business_name'],
                'business_type' => $hasGst ? 'registered_business' : 'individual',
                'store_description' => $vendor->store_description ?: 'Local Sushako seller.',
                'email' => $vendor->email ?: $request->user()->email,
                'phone' => $data['phone'],
                'address_line_1' => $data['business_address'],
                'address_line_2' => null,
                'area' => $data['city'],
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['postal_code'],
                'country' => 'India',
                'business_logo_path' => $logoPath,
                'slug' => $slug,
                'gst_status' => $hasGst ? 'registered' : 'not_registered',
                'gstin' => $hasGst ? strtoupper($data['gstin']) : null,
                'pan_number' => $hasGst ? null : strtoupper($data['pan_number']),
                'legal_compliance_confirmed_at' => now(),
                'pickup_address' => $pickupAddress,
                'pickup_address_line_1' => $pickup['address'],
                'pickup_city' => $pickup['city'],
                'pickup_state' => $pickup['state'],
                'pickup_postal_code' => $pickup['postal_code'],
                'return_address' => $pickupAddress,
                'delivery_radius' => (int) $data['delivery_radius'],
                'radius_unit' => 'km',
                'shipping_commitment' => 'self_shipping',
                'flat_shipping_charge' => $data['flat_shipping_charge'],
                'free_shipping_threshold' => $data['free_shipping_threshold'] ?? null,
                'returns_accepted' => $returnsAccepted,
                'return_window_days' => $returnWindowDays,
                'return_policy_summary' => $returnPolicySummary,
                'delivery_settings' => [
                    'shipment_method' => 'self_shipping',
                    'delivery_radius' => (int) $data['delivery_radius'],
                    'delivery_radius_label' => $data['delivery_radius'].' km',
                    'flat_shipping_charge' => (float) $data['flat_shipping_charge'],
                    'free_shipping_threshold' => $data['free_shipping_threshold'] ?? null,
                    'use_business_address' => $useBusinessPickup,
                    'pickup_address_line_1' => $pickup['address'],
                    'pickup_city' => $pickup['city'],
                    'pickup_state' => $pickup['state'],
                    'pickup_postal_code' => $pickup['postal_code'],
                    'return_policy_summary' => $returnPolicySummary,
                ],
                'status' => Vendor::STATUS_ACTIVE,
                'store_status' => Vendor::STORE_SETUP_REQUIRED,
                'store_visibility' => Vendor::VISIBILITY_DRAFT,
                'approval_status' => Vendor::APPROVAL_ONBOARDING_COMPLETED,
            ])->save();

            $accounts->activateStarter($vendor->fresh());
            $accounts->audit($vendor->fresh(), 'simple_onboarding_completed', 'Seller completed simplified business setup.', [
                'gst_status' => $hasGst ? 'registered' : 'not_registered',
                'delivery_radius' => (int) $data['delivery_radius'],
                'returns_accepted' => $returnsAccepted,
            ]);
        });

        return redirect()->route('seller.dashboard')->with('status', 'Your shop is ready 🎉');
    }

    private function legacyPlanSubmit(Request $request, SellerAccountService $accounts, SellerOnboardingPaymentService $payments): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();
        $data = $request->validate([
            'plan' => ['required', Rule::exists('seller_plans', 'slug')->where('status', SellerPlan::STATUS_ACTIVE)],
            'legal_acceptance' => ['required_without:policies', 'array', 'size:2'],
            'legal_acceptance.*' => [Rule::in(self::LEGAL_ACCEPTANCES)],
            'policies' => ['nullable', 'array'],
            'policies.*' => [Rule::in(array_keys(SellerAccountService::POLICIES))],
        ]);

        $acceptedPolicies = array_keys(SellerAccountService::POLICIES);
        if (! empty($data['policies'])) {
            abort_unless(collect($acceptedPolicies)->diff($data['policies'])->isEmpty(), 422, 'All legal policies must be accepted.');
        } else {
            abort_unless(collect(self::LEGAL_ACCEPTANCES)->diff($data['legal_acceptance'])->isEmpty(), 422, 'All legal acknowledgements must be accepted.');
        }

        $plan = SellerPlan::query()
            ->where('slug', $data['plan'])
            ->where('status', SellerPlan::STATUS_ACTIVE)
            ->firstOrFail();
        $snapshot = $accounts->normalizePlan($plan);

        DB::transaction(function () use ($request, $vendor, $plan, $snapshot, $accounts, $acceptedPolicies): void {
            foreach ($acceptedPolicies as $policy) {
                SellerPolicyAcceptance::query()->updateOrCreate([
                    'vendor_id' => $vendor->id,
                    'policy_key' => $policy,
                    'policy_version' => 'v1',
                ], [
                    'user_id' => $request->user()->id,
                    'ip_address' => $request->ip(),
                    'accepted_at' => now(),
                ]);
            }

            $vendor->forceFill([
                'selected_plan' => $plan->slug,
                'selected_plan_id' => $plan->id,
                'selected_plan_slug' => $plan->slug,
                'selected_plan_snapshot' => $snapshot,
                'plan_selected_at' => now(),
                'current_plan' => $plan->is_paid ? $vendor->current_plan : Vendor::PLAN_FREE,
                'plan_status' => $plan->is_paid ? Vendor::PLAN_PENDING_PAYMENT : Vendor::PLAN_ACTIVE,
                'payment_status' => $plan->is_paid ? Vendor::PAYMENT_PENDING : Vendor::PAYMENT_NOT_REQUIRED,
                'razorpay_payment_status' => $plan->is_paid ? Vendor::PAYMENT_PENDING : Vendor::PAYMENT_NOT_REQUIRED,
                'onboarding_status' => 'draft',
                'onboarding_step' => 3,
                'dashboard_access_enabled' => false,
            ])->save();

            $accounts->audit($vendor, 'onboarding_plan_selected', 'Seller selected onboarding plan.', [
                'plan' => $plan->slug,
                'price' => (int) $plan->price,
            ]);
        });

        if ($plan->slug === Vendor::PLAN_FREE) {
            $accounts->activateStarter($vendor->fresh());

            return redirect()->route('seller.onboarding')->with('status', 'Free plan activated.');
        }

        $payments->createOrder($vendor->fresh(), $plan->slug);

        return redirect()->route('seller.onboarding.payment');
    }

    public function autosave(Request $request, SellerAccountService $accounts): JsonResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();
        abort_unless($vendor instanceof Vendor, 403);

        $step = min(3, max(1, (int) $request->integer('step', 1)));

        if ($step === 1) {
            $data = $request->validate([
                'business_name' => ['nullable', 'string', 'min:3', 'max:100', 'not_regex:/^\d+$/', 'regex:/^[A-Za-z0-9&().,\\-\\s]+$/'],
                'store_description' => ['nullable', 'string', 'max:1000'],
                'phone' => ['nullable', 'regex:/^\d{10}$/', Rule::unique('users', 'phone')->ignore($request->user()->id)],
                'email' => ['nullable', 'email', 'max:255'],
                'business_type' => ['nullable', Rule::in(array_keys(self::BUSINESS_TYPES))],
                'categories' => ['nullable', 'array'],
                'categories.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)->where('available_to_sellers', true)],
                'address_line_1' => ['nullable', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'landmark' => ['nullable', 'string', 'max:160'],
                'city' => ['nullable', 'string', 'max:120'],
                'state' => ['nullable', Rule::in(self::INDIAN_STATES)],
                'postal_code' => ['nullable', 'regex:/^\d{6}$/'],
            ], $this->businessMessages());

            $payload = collect($data)->except(['categories'])->filter(fn ($value) => ! is_null($value))->all();
            if (isset($payload['phone'])) {
                $request->user()->forceFill(['phone' => $payload['phone']])->save();
            }
            if ($payload !== []) {
                $vendor->forceFill([
                    ...$payload,
                    'store_display_name' => $payload['business_name'] ?? $vendor->store_display_name,
                    'area' => $payload['city'] ?? $vendor->area,
                    'onboarding_status' => 'draft',
                ])->save();
            }
            if (isset($data['categories'])) {
                $vendor->categories()->sync($data['categories']);
            }
        } elseif ($step === 2) {
            $data = $request->validate([
                'use_business_address' => ['nullable', 'boolean'],
                'pickup_address_line_1' => ['nullable', 'string', 'max:255'],
                'pickup_address_line_2' => ['nullable', 'string', 'max:255'],
                'pickup_landmark' => ['nullable', 'string', 'max:160'],
                'pickup_city' => ['nullable', 'string', 'max:120'],
                'pickup_state' => ['nullable', Rule::in(self::INDIAN_STATES)],
                'pickup_postal_code' => ['nullable', 'regex:/^\d{6}$/'],
                'delivery_radius' => ['nullable', 'integer', 'min:1', 'max:100'],
                'flat_shipping_charge' => ['nullable', 'numeric', 'min:0', 'max:99999'],
                'free_shipping_threshold' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            ]);

            $settings = array_merge($vendor->delivery_settings ?? [], collect($data)->filter(fn ($value) => ! is_null($value))->all());
            $vendor->forceFill([
                'shipping_commitment' => 'self_shipping',
                'delivery_radius' => $data['delivery_radius'] ?? $vendor->delivery_radius,
                'flat_shipping_charge' => $data['flat_shipping_charge'] ?? $vendor->flat_shipping_charge,
                'free_shipping_threshold' => array_key_exists('free_shipping_threshold', $data) ? $data['free_shipping_threshold'] : $vendor->free_shipping_threshold,
                'delivery_settings' => $settings + ['shipment_method' => 'self_shipping'],
            ])->save();
        } elseif ($request->filled('plan')) {
            $data = $request->validate([
                'plan' => ['required', Rule::exists('seller_plans', 'slug')->where('status', SellerPlan::STATUS_ACTIVE)],
            ]);
            $vendor->forceFill([
                'selected_plan' => $data['plan'],
                'selected_plan_slug' => $data['plan'],
                'onboarding_status' => 'draft',
            ])->save();
        }

        $accounts->audit($vendor, 'onboarding_autosaved', 'Seller onboarding draft autosaved.', ['step' => $step]);

        return response()->json(['saved' => true, 'saved_at' => now()->toIso8601String()]);
    }

    public function payment(Request $request, SellerAccountService $accounts, SellerOnboardingPaymentService $payments): View|RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();
        $selectedPlan = $vendor->selected_plan_slug ?: $vendor->selected_plan;

        if (blank($selectedPlan)) {
            return redirect()->route('seller.onboarding');
        }

        if ($selectedPlan === Vendor::PLAN_FREE) {
            return redirect($accounts->redirectFor($vendor));
        }

        $payment = $vendor->onboardingPayments()
            ->where('status', SellerOnboardingPayment::STATUS_PENDING)
            ->latest()
            ->first() ?: $payments->createOrder($vendor, (string) $selectedPlan);

        return view('seller.onboarding-payment', [
            'vendor' => $vendor->fresh(),
            'payment' => $payment,
            'plan' => $accounts->plans()[$selectedPlan],
            'razorpayConfigured' => $payments->razorpayConfigured(),
        ]);
    }

    public function retryPayment(Request $request, SellerOnboardingPaymentService $payments): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();
        $selectedPlan = $vendor->selected_plan_slug ?: $vendor->selected_plan;

        if (blank($selectedPlan) || $selectedPlan === Vendor::PLAN_FREE) {
            return redirect()->route('seller.onboarding');
        }

        $payments->createOrder($vendor, (string) $selectedPlan);

        return redirect()->route('seller.onboarding.payment')->with('status', 'Secure payment session refreshed.');
    }

    public function confirmPayment(Request $request, SellerOnboardingPaymentService $payments): JsonResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'payment_id' => ['required', 'integer', 'exists:seller_onboarding_payments,id'],
            'razorpay_payment_id' => ['required', 'string', 'max:120'],
            'razorpay_order_id' => ['required', 'string', 'max:120'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);

        $payment = SellerOnboardingPayment::query()->where('vendor_id', $vendor->id)->findOrFail($data['payment_id']);
        try {
            $payments->verify($payment, $data);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'status' => 'paid',
            'redirect_url' => route('seller.onboarding.success'),
        ]);
    }

    public function webhook(Request $request, SellerOnboardingPaymentService $payments): JsonResponse
    {
        $signature = (string) $request->header('X-Razorpay-Signature');
        $secret = (string) config('services.razorpay.webhook_secret');

        if (filled($secret)) {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            abort_unless(hash_equals($expected, $signature), 403);
        }

        $payload = $request->all();
        $event = (string) ($payload['event'] ?? '');
        $entity = data_get($payload, 'payload.payment.entity', data_get($payload, 'payload.order.entity', []));
        $orderId = data_get($entity, 'order_id') ?: data_get($entity, 'id');
        $payment = $orderId ? SellerOnboardingPayment::query()->where('provider_order_id', $orderId)->first() : null;

        if (! $payment) {
            return response()->json(['received' => true]);
        }

        if (in_array($event, ['payment.captured', 'order.paid'], true)) {
            $providerPaymentId = data_get($entity, 'id', $payment->provider_payment_id ?: 'webhook');

            $payment->forceFill([
                'provider_payment_id' => $providerPaymentId,
                'status' => SellerOnboardingPayment::STATUS_PAID,
                'verified_at' => $payment->verified_at ?: now(),
                'metadata' => array_merge($payment->metadata ?? [], ['webhook_event' => $event]),
            ])->save();

            $payments->verify($payment->fresh(), [
                'razorpay_order_id' => $payment->provider_order_id,
                'razorpay_payment_id' => $providerPaymentId,
                'razorpay_signature' => hash_hmac('sha256', $payment->provider_order_id.'|'.$providerPaymentId, (string) config('services.razorpay.secret')),
            ]);
        } elseif (str_contains($event, 'failed')) {
            $payment->forceFill([
                'status' => SellerOnboardingPayment::STATUS_FAILED,
                'failure_reason' => $event,
            ])->save();

            $payment->vendor->forceFill([
                'payment_status' => Vendor::PAYMENT_FAILED,
                'razorpay_payment_status' => Vendor::PAYMENT_FAILED,
                'plan_status' => Vendor::PLAN_PENDING_PAYMENT,
                'dashboard_access_enabled' => false,
            ])->save();
        }

        return response()->json(['received' => true]);
    }

    public function success(Request $request): View|RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();
        
        return redirect()->route('seller.onboarding')->with('status', 'Payment successful.');
    }

    public function publish(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');

        if (! $accounts->publish($vendor)) {
            $readiness = $accounts->storeSetupChecklist($vendor->fresh());
            $missing = $readiness['missing_mandatory'] ?? [];

            return back()->withErrors([
                'store' => $missing
                    ? 'Complete these launch items before publishing: '.implode(', ', $missing).'.'
                    : 'Store readiness must be at least 75% before publishing.',
            ]);
        }

        return redirect()->route('seller.dashboard')->with('status', 'Your seller store is now live.');
    }

    public function status(Request $request): View
    {
        return view('seller.status', [
            'vendor' => $request->attributes->get('vendor'),
        ]);
    }

    public function submitForReview(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor')->fresh();

        $vendor->forceFill([
            'onboarding_status' => 'complete',
            'dashboard_access_enabled' => true,
        ])->save();

        $accounts->audit($vendor, 'legacy_review_bypassed', 'Seller review step bypassed by simplified onboarding.');

        return redirect()->route('seller.dashboard')->with('status', 'Seller setup is complete.');
    }

    private function onboardingViewData(Request $request, Vendor $vendor, SellerAccountService $accounts): array
    {
        return [
            'vendor' => $vendor,
            'step' => 1,
            'steps' => [1 => 'Business Setup'],
            'plans' => $accounts->plans(),
            'defaultPlan' => Vendor::PLAN_FREE,
            'isGoogleSeller' => filled($request->user()?->google_id),
            'policies' => SellerAccountService::POLICIES,
            'businessTypes' => self::BUSINESS_TYPES,
            'indianStates' => self::INDIAN_STATES,
        ];
    }

    private function simpleOnboardingRules(Request $request): array
    {
        return [
            'business_name' => ['required', 'string', 'min:3', 'max:100', 'not_regex:/^\d+$/', 'regex:/^[A-Za-z0-9&().,\\-\\s]+$/'],
            'business_address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'regex:/^\d{6}$/'],
            'phone' => ['required', 'regex:/^[6-9]\d{9}$/', Rule::unique('users', 'phone')->ignore($request->user()->id)],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', Rule::in(self::INDIAN_STATES)],
            'business_logo' => ['nullable', 'image', 'max:2048'],
            'pickup_same_as_business' => ['nullable', 'boolean'],
            'pickup_address' => ['required_unless:pickup_same_as_business,1', 'nullable', 'string', 'max:500'],
            'pickup_postal_code' => ['required_unless:pickup_same_as_business,1', 'nullable', 'regex:/^\d{6}$/'],
            'pickup_city' => ['required_unless:pickup_same_as_business,1', 'nullable', 'string', 'max:120'],
            'pickup_state' => ['required_unless:pickup_same_as_business,1', 'nullable', Rule::in(self::INDIAN_STATES)],
            'gst_registered' => ['required', Rule::in(['yes', 'no'])],
            'gstin' => ['required_if:gst_registered,yes', 'nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i'],
            'pan_number' => ['required_if:gst_registered,no', 'nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'],
            'delivery_radius' => ['required', 'integer', 'min:1', 'max:500'],
            'flat_shipping_charge' => ['required', 'numeric', 'min:0', 'max:99999'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'returns_accepted' => ['required', Rule::in(['yes', 'no'])],
            'return_window_days' => ['required_if:returns_accepted,yes', 'nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    private function businessMessages(): array
    {
        return [
            'business_name.not_regex' => 'Business name cannot contain only numbers.',
            'business_name.regex' => 'Business name can contain letters, numbers, spaces, &, dots, commas, hyphens, and brackets only.',
            'phone.regex' => 'Enter a valid 10 digit Indian mobile number.',
            'phone.unique' => 'This mobile number is already linked to another Sushako account.',
            'postal_code.regex' => 'Enter a valid 6 digit pincode.',
            'gstin.regex' => 'Enter a valid GSTIN.',
            'pan_number.regex' => 'Enter a valid PAN number.',
        ];
    }

    private function formatAddress(?string $line1, ?string $line2, ?string $landmark, ?string $city, ?string $state, ?string $pincode): string
    {
        return collect([$line1, $line2, $landmark, $city, $state, $pincode])
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }

    private function uniqueSlug(string $value, Vendor $vendor): string
    {
        $base = Str::slug($value) ?: 'seller-store';
        $slug = $base;
        $count = 2;

        while (Vendor::query()->where('slug', $slug)->whereKeyNot($vendor->id)->exists()) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }
}
