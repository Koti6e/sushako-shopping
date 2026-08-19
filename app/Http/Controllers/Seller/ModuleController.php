<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerHoliday;
use App\Models\SellerPlan;
use App\Models\SellerPolicyAcceptance;
use App\Models\SellerSettlement;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use App\Services\SellerOnboardingPaymentService;
use App\Support\CustomerContactIntents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(private readonly SellerAccountService $sellerAccounts) {}

    public function categories(Request $request): View
    {
        return view('seller.simple.categories', [
            'vendor' => $request->attributes->get('vendor')->loadMissing('categories'),
            'categories' => $this->sellerAccounts->sellerCategories(),
        ]);
    }

    public function customers(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'activity' => ['nullable', Rule::in(['recent_30', 'inactive_60'])],
            'sort' => ['nullable', Rule::in(['last_order', 'orders', 'spent', 'name'])],
        ]);

        $customers = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.vendor_id', $vendor->id)
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $digits = preg_replace('/\D+/', '', $search);

                $query->where(function ($query) use ($search, $digits): void {
                    $query->where('orders.customer_name', 'like', "%{$search}%");

                    if ($digits !== '') {
                        $query->orWhere('orders.customer_phone', 'like', "%{$digits}%");
                    }
                });
            })
            ->when(($filters['activity'] ?? null) === 'recent_30', fn ($query) => $query->where('orders.created_at', '>=', now()->subDays(30)))
            ->when(($filters['activity'] ?? null) === 'inactive_60', fn ($query) => $query->where('orders.created_at', '<=', now()->subDays(60)))
            ->selectRaw('orders.user_id, orders.customer_name, orders.customer_email, orders.customer_phone, COUNT(DISTINCT orders.id) as orders_count, SUM(order_items.line_total) as total_spend, MAX(COALESCE(orders.placed_at, orders.created_at)) as last_order_at')
            ->groupBy('orders.user_id', 'orders.customer_name', 'orders.customer_email', 'orders.customer_phone');

        match ($filters['sort'] ?? 'last_order') {
            'orders' => $customers->orderByDesc('orders_count'),
            'spent' => $customers->orderByDesc('total_spend'),
            'name' => $customers->orderBy('customer_name'),
            default => $customers->orderByDesc('last_order_at'),
        };

        return view('seller.simple.customers', [
            'vendor' => $vendor,
            'customers' => $customers->paginate(20)->withQueryString(),
            'filters' => collect($filters)->filter(fn ($value) => filled($value))->all(),
        ]);
    }

    public function customer(Request $request, string $customerKey): View
    {
        $vendor = $request->attributes->get('vendor');
        $baseOrders = $this->sellerCustomerOrders($vendor, $customerKey);
        abort_unless((clone $baseOrders)->exists(), 404);

        $latestOrder = (clone $baseOrders)->latest()->firstOrFail();
        $orders = (clone $baseOrders)
            ->with(['items' => fn ($query) => $query->where('vendor_id', $vendor->id)->with('product')])
            ->latest()
            ->paginate(10, ['*'], 'orders_page')
            ->withQueryString();
        $sellerItems = OrderItem::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', fn (Builder $query) => $this->applySellerCustomerKey($query, $customerKey));
        $ordersCount = (clone $baseOrders)->distinct('orders.id')->count('orders.id');
        $totalSpend = (int) (clone $sellerItems)->sum('line_total');
        $onlineOrders = (clone $baseOrders)->where('payment_method', 'razorpay')->where('payment_status', 'paid')->count();
        $codOrders = (clone $baseOrders)->where('payment_method', 'cod')->count();

        return view('seller.simple.customer-show', [
            'vendor' => $vendor,
            'customerKey' => $customerKey,
            'latestOrder' => $latestOrder,
            'orders' => $orders,
            'summary' => [
                ['label' => 'Orders with Your Store', 'value' => number_format($ordersCount), 'support' => 'Store-specific'],
                ['label' => 'Total Spent at Your Store', 'value' => 'Rs '.number_format($totalSpend), 'support' => 'Seller item total'],
                ['label' => 'Last Order', 'value' => $latestOrder->order_number, 'support' => ($latestOrder->placed_at ?? $latestOrder->created_at)->format('d M Y')],
                ['label' => 'COD / Online', 'value' => $codOrders.' / '.$onlineOrders, 'support' => 'Payment mix'],
            ],
            'contactActions' => [
                'tel' => CustomerContactIntents::tel($latestOrder->customer_phone),
                'email' => CustomerContactIntents::email($latestOrder->customer_email),
                'whatsapp' => CustomerContactIntents::whatsApp($latestOrder->customer_phone),
                'maps' => CustomerContactIntents::maps($latestOrder->delivery_location_url),
            ],
            'deliveryAddress' => CustomerContactIntents::addressFromOrder($latestOrder),
        ]);
    }

    public function payments(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.simple.payments', [
            'vendor' => $vendor,
            'items' => OrderItem::query()->with('order')->where('vendor_id', $vendor->id)->latest()->paginate(20),
        ]);
    }

    private function sellerCustomerOrders(Vendor $vendor, string $customerKey): Builder
    {
        return $this->applySellerCustomerKey(
            Order::query()->whereHas('items', fn (Builder $query) => $query->where('vendor_id', $vendor->id)),
            $customerKey
        );
    }

    private function applySellerCustomerKey(Builder $query, string $customerKey): Builder
    {
        if (preg_match('/^user-(\d+)$/', $customerKey, $matches)) {
            return $query->where('user_id', (int) $matches[1]);
        }

        if (preg_match('/^phone-(\d{10,12})$/', $customerKey, $matches)) {
            return $query->where('customer_phone', 'like', '%'.$matches[1].'%');
        }

        abort(404);
    }

    public function settlements(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.simple.settlements', [
            'vendor' => $vendor,
            'settlements' => SellerSettlement::query()->where('vendor_id', $vendor->id)->latest()->paginate(20),
            'items' => OrderItem::query()->with('order')->where('vendor_id', $vendor->id)->where('settlement_status', 'upcoming')->latest()->paginate(20),
        ]);
    }

    public function generic(Request $request, string $module): View
    {
        $vendor = $request->attributes->get('vendor');

        if ($module === 'store_profile') {
            return view('seller.simple.store-profile', [
                'vendor' => $vendor,
                'categories' => $this->sellerAccounts->sellerCategories(),
            ]);
        }

        if ($module === 'shipping') {
            return view('seller.simple.shipping', ['vendor' => $vendor]);
        }

        if ($module === 'settings') {
            $vendor->loadMissing(['policyAcceptances', 'holidays']);

            return view('seller.simple.settings', [
                'vendor' => $vendor,
                'policies' => SellerAccountService::POLICIES,
                'acceptedPolicies' => $vendor->policyAcceptances->pluck('policy_key')->all(),
            ]);
        }

        if ($module === 'plans') {
            return view('seller.simple.plans', [
                'vendor' => $vendor,
                'plans' => $this->sellerAccounts->plans(),
                'payments' => $vendor->planPayments()->latest()->paginate(20),
            ]);
        }

        return view('seller.simple.generic', [
            'vendor' => $vendor,
            'module' => $module,
        ]);
    }

    public function updateCategories(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)->where('available_to_sellers', true)],
        ]);

        $vendor->categories()->sync($data['categories']);
        $this->sellerAccounts->audit($vendor, 'seller_categories_updated', 'Seller updated product categories.', ['category_ids' => $data['categories']]);

        return back()->with('status', 'Product categories updated.');
    }

    public function updateStoreProfile(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:160'],
            'store_display_name' => ['required', 'string', 'max:160'],
            'owner_name' => ['required', 'string', 'max:120'],
            'business_category' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'store_description' => ['required', 'string', 'max:1200'],
            'store_tagline' => ['nullable', 'string', 'max:160'],
            'website' => ['nullable', 'url', 'max:255'],
            'gst_status' => ['nullable', 'string', 'max:40'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($vendor->business_name !== $data['business_name'] && (int) $vendor->business_name_edit_count >= 3) {
            return back()->withErrors(['business_name' => 'Business name can only be edited three times.']);
        }

        if ($request->hasFile('logo')) {
            $data['business_logo_path'] = $this->sellerAccounts->storeUploadedFile($request->file('logo'), 'seller-logos');
        }
        unset($data['logo']);

        $vendor->forceFill(array_merge($data, [
            'slug' => $vendor->slug ?: $this->uniqueSlug($data['store_display_name'], $vendor),
            'business_name_edit_count' => $vendor->business_name !== $data['business_name'] ? ((int) $vendor->business_name_edit_count + 1) : (int) $vendor->business_name_edit_count,
            'store_status' => $vendor->store_status === Vendor::STORE_LIVE ? Vendor::STORE_LIVE : Vendor::STORE_SETUP_REQUIRED,
        ]))->save();

        $this->sellerAccounts->audit($vendor, 'seller_store_profile_updated', 'Seller updated store profile details.');

        return back()->with('status', 'Store profile details updated.');
    }

    public function updateShipping(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'regex:/^\d{6}$/'],
            'pickup_address' => ['required', 'string', 'max:600'],
            'return_address' => ['required', 'string', 'max:600'],
            'shipping_commitment' => ['required', 'string', 'max:80'],
            'preferred_courier_name' => ['nullable', 'string', 'max:120'],
            'flat_shipping_charge' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'delivery_radius' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'working_days' => ['array'],
            'working_days.*' => [Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'manual_tracking_enabled' => ['nullable', 'boolean'],
        ]);

        $vendor->forceFill(array_merge($data, [
            'country' => 'India',
            'manual_tracking_enabled' => $request->boolean('manual_tracking_enabled'),
            'working_days' => $data['working_days'] ?? [],
            'opens_at' => $data['opens_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'delivery_settings' => [
                'commitment' => $data['shipping_commitment'],
                'preferred_courier' => $data['preferred_courier_name'] ?? null,
                'flat_charge' => $data['flat_shipping_charge'] ?? null,
                'free_threshold' => $data['free_shipping_threshold'] ?? null,
                'delivery_radius' => $data['delivery_radius'] ?? null,
                'working_days' => $data['working_days'] ?? [],
                'opens_at' => $data['opens_at'] ?? null,
                'closes_at' => $data['closes_at'] ?? null,
            ],
        ]))->save();

        $this->sellerAccounts->audit($vendor, 'seller_shipping_updated', 'Seller updated pickup, return, and delivery settings.');

        return back()->with('status', 'Shipping and delivery details updated.');
    }

    public function updateSettlements(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'bank_account_holder_name' => ['required', 'string', 'max:160'],
            'bank_name' => ['required', 'string', 'max:160'],
            'bank_account_number' => ['required', 'string', 'min:6', 'max:40', 'confirmed'],
            'bank_ifsc' => ['required', 'string', 'max:20'],
            'bank_branch_name' => ['nullable', 'string', 'max:160'],
            'bank_account_type' => ['nullable', Rule::in(['savings', 'current'])],
            'bank_upi_id' => ['nullable', 'string', 'max:120'],
            'settlement_cycle' => ['required', Rule::in(['weekly', 'biweekly', 'monthly'])],
            'minimum_settlement_amount' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'settlement_enquiry' => ['nullable', 'string', 'max:800'],
        ]);

        $enquiry = $data['settlement_enquiry'] ?? null;
        unset($data['settlement_enquiry']);

        $vendor->forceFill($data + [
            'bank_verification_status' => 'pending_review',
            'pending_settlement_notice' => $enquiry ?: $vendor->pending_settlement_notice,
        ])->save();

        $this->sellerAccounts->audit($vendor, 'seller_settlement_details_updated', 'Seller updated bank and settlement details.', [
            'has_enquiry' => filled($enquiry),
        ]);

        return back()->with('status', 'Bank, settlement, and enquiry details updated.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $vendor = $request->attributes->get('vendor');
        $data = $request->validate([
            'policies' => ['array'],
            'policies.*' => [Rule::in(array_keys(SellerAccountService::POLICIES))],
            'vacation_auto_reactivate' => ['nullable', 'boolean'],
            'vacation_message' => ['nullable', 'string', 'max:400'],
            'tax_preference' => ['required', Rule::in(['gst_included', 'gst_excluded'])],
            'working_days' => ['array'],
            'working_days.*' => [Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'store_visibility' => ['required', Rule::in([Vendor::VISIBILITY_DRAFT, Vendor::VISIBILITY_PUBLISHED, Vendor::VISIBILITY_HIDDEN])],
            'holiday_date' => ['nullable', 'date'],
            'holiday_title' => ['nullable', 'string', 'max:120'],
            'holiday_note' => ['nullable', 'string', 'max:400'],
        ]);

        foreach ($data['policies'] ?? [] as $policy) {
            SellerPolicyAcceptance::query()->updateOrCreate([
                'vendor_id' => $vendor->id,
                'policy_key' => $policy,
            ], [
                'user_id' => $request->user()->id,
                'policy_version' => 'v1',
                'ip_address' => $request->ip(),
                'accepted_at' => now(),
            ]);
        }

        $vendor->forceFill([
            'vacation_auto_reactivate' => $request->boolean('vacation_auto_reactivate'),
            'vacation_message' => $data['vacation_message'] ?? null,
            'tax_preference' => $data['tax_preference'],
            'working_days' => $data['working_days'] ?? [],
            'opens_at' => $data['opens_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'store_visibility' => $data['store_visibility'],
            'store_status' => $data['store_visibility'] === Vendor::VISIBILITY_PUBLISHED ? Vendor::STORE_LIVE : ($data['store_visibility'] === Vendor::VISIBILITY_HIDDEN ? Vendor::STORE_TEMPORARILY_CLOSED : Vendor::STORE_SETUP_REQUIRED),
        ])->save();

        if (filled($data['holiday_date'] ?? null) && filled($data['holiday_title'] ?? null)) {
            SellerHoliday::query()->updateOrCreate([
                'vendor_id' => $vendor->id,
                'holiday_date' => $data['holiday_date'],
            ], [
                'title' => $data['holiday_title'],
                'note' => $data['holiday_note'] ?? null,
            ]);
        }

        $this->sellerAccounts->audit($vendor, 'seller_settings_updated', 'Seller updated store settings and policies.');

        return back()->with('status', 'Store policies and settings updated.');
    }

    public function renewPlan(Request $request, SellerOnboardingPaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['nullable', Rule::exists('seller_plans', 'slug')->where('status', SellerPlan::STATUS_ACTIVE)],
        ]);

        $vendor = $request->attributes->get('vendor')->fresh();
        $plan = $data['plan'] ?? ($vendor->current_plan ?: $vendor->selected_plan_slug ?: $vendor->selected_plan);
        $planRecord = $plan ? SellerPlan::query()
            ->where('slug', $plan)
            ->where('status', SellerPlan::STATUS_ACTIVE)
            ->first() : null;

        if (! $plan || $plan === Vendor::PLAN_FREE || ! $planRecord?->is_paid) {
            return back()->with('status', 'Free plan has unlimited validity and does not require renewal.');
        }

        $vendor->forceFill([
            'selected_plan' => $plan,
            'selected_plan_slug' => $plan,
            'selected_plan_id' => $planRecord->id,
            'selected_plan_snapshot' => $this->sellerAccounts->normalizePlan($planRecord),
            'plan_selected_at' => now(),
            'payment_status' => Vendor::PAYMENT_PENDING,
            'razorpay_payment_status' => Vendor::PAYMENT_PENDING,
            'plan_status' => Vendor::PLAN_PENDING_PAYMENT,
        ])->save();

        $payments->createOrder($vendor, $plan);

        return redirect()->route('seller.onboarding.payment')->with('status', str($planRecord->name)->append(' payment created. Complete payment to activate the plan for 30 days.')->toString());
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
