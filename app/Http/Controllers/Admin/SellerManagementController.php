<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\SellerAuditLog;
use App\Models\StorefrontPromotionalBanner;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter');
        $view = in_array($request->query('view'), ['cards', 'list'], true) ? $request->query('view') : 'cards';
        $sort = in_array($request->query('sort'), ['latest', 'oldest', 'sales', 'products', 'completion'], true) ? $request->query('sort') : 'latest';
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $plan = $request->query('plan');
        $storeStatus = $request->query('store_status');
        $approvalStatus = $request->query('approval_status');
        $location = trim((string) $request->query('location', ''));

        $baseQuery = Vendor::query();

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', Vendor::STATUS_ACTIVE)->count(),
            'approval_pending' => (clone $baseQuery)->whereIn('approval_status', [Vendor::APPROVAL_PENDING_ADMIN, Vendor::APPROVAL_SETUP_COMPLETE])->count(),
            'setup_incomplete' => (clone $baseQuery)->where('onboarding_status', '!=', 'complete')->count(),
            'bank_pending' => (clone $baseQuery)->whereIn('bank_verification_status', ['submitted', 'verification_pending'])->count(),
            'payment_attention' => (clone $baseQuery)->whereIn('payment_status', [Vendor::PAYMENT_PENDING, Vendor::PAYMENT_FAILED])->count(),
        ];

        $vendors = Vendor::query()
            ->with(['user', 'selectedSellerPlan'])
            ->with(['onboardingPayments' => fn ($query) => $query->latest()])
            ->withCount([
                'products',
                'settlements',
                'products as active_products_count' => fn ($query) => $query->where('is_published', true),
            ])
            ->withSum('ledgerEntries as gross_sales', 'gross_amount')
            ->withSum('ledgerEntries as commission_total', 'commission_amount')
            ->withSum([
                'settlements as pending_settlement_total' => fn ($query) => $query->whereNull('paid_at'),
            ], 'final_settlement_amount')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('business_name', 'like', "%{$search}%")
                        ->orWhere('store_display_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->when(is_numeric($search), fn ($query) => $query->orWhere('id', $search))
                        ->orWhereHas('user', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($plan, fn ($query) => $query->where(fn ($query) => $query->where('selected_plan_slug', $plan)->orWhere('current_plan', $plan)))
            ->when($storeStatus, fn ($query) => $query->where('store_status', $storeStatus))
            ->when($approvalStatus, fn ($query) => $query->where('approval_status', $approvalStatus))
            ->when($location !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('city', 'like', "%{$location}%")
                ->orWhere('state', 'like', "%{$location}%")
                ->orWhere('postal_code', 'like', "%{$location}%")))
            ->when($filter === 'onboarding_incomplete', fn ($query) => $query->where('onboarding_status', '!=', 'complete'))
            ->when($filter === 'approval_pending', fn ($query) => $query->whereIn('approval_status', [Vendor::APPROVAL_PENDING_ADMIN, Vendor::APPROVAL_SETUP_COMPLETE]))
            ->when($filter === 'bank_pending', fn ($query) => $query->whereIn('bank_verification_status', ['submitted', 'verification_pending']))
            ->when($filter === 'payment_pending', fn ($query) => $query->where('payment_status', Vendor::PAYMENT_PENDING))
            ->when($filter === 'payment_failed', fn ($query) => $query->where('payment_status', Vendor::PAYMENT_FAILED))
            ->when($filter === 'free', fn ($query) => $query->where(fn ($query) => $query->where('selected_plan_slug', Vendor::PLAN_FREE)->orWhere('current_plan', Vendor::PLAN_FREE)))
            ->when($filter === 'growth', fn ($query) => $query->where(fn ($query) => $query->where('selected_plan_slug', Vendor::PLAN_GROWTH)->orWhere('current_plan', Vendor::PLAN_GROWTH)))
            ->when($filter === 'enterprise', fn ($query) => $query->where(fn ($query) => $query->where('selected_plan_slug', Vendor::PLAN_ENTERPRISE)->orWhere('current_plan', Vendor::PLAN_ENTERPRISE)))
            ->when($filter === 'active', fn ($query) => $query->where('plan_status', Vendor::PLAN_ACTIVE))
            ->when($filter === 'expiring', fn ($query) => $query->whereNotNull('plan_expires_at')->whereBetween('plan_expires_at', [now(), now()->addDays(7)]))
            ->when($filter === 'expired', fn ($query) => $query->whereNotNull('plan_expires_at')->where('plan_expires_at', '<', now()))
            ->when($filter === 'suspended', fn ($query) => $query->whereIn('status', [Vendor::STATUS_SUSPENDED, Vendor::STATUS_CLOSED, Vendor::STATUS_REJECTED, Vendor::STATUS_INACTIVE]))
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'sales', fn ($query) => $query->orderByDesc('gross_sales'))
            ->when($sort === 'products', fn ($query) => $query->orderByDesc('products_count'))
            ->when($sort === 'completion', fn ($query) => $query->orderByDesc('onboarding_completed_at')->orderByDesc('setup_completed_at'))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate(20)
            ->withQueryString();

        return view('admin.sellers.index', [
            'vendors' => $vendors,
            'filter' => $filter,
            'view' => $view,
            'sort' => $sort,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'plan' => $plan,
                'store_status' => $storeStatus,
                'approval_status' => $approvalStatus,
                'location' => $location,
            ],
        ]);
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load([
            'user',
            'products' => fn ($query) => $query->latest()->take(10),
            'settlements' => fn ($query) => $query->latest()->take(10),
            'audits' => fn ($query) => $query->latest()->take(10),
            'onboardingPayments' => fn ($query) => $query->latest()->take(5),
            'categoryRequests' => fn ($query) => $query->latest()->take(5),
        ])->loadCount(['products', 'settlements', 'categoryRequests']);

        return view('admin.sellers.show', [
            'vendor' => $vendor,
            'ordersCount' => Order::query()->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))->count(),
            'grossRevenue' => (float) $vendor->ledgerEntries()->sum('gross_amount'),
        ]);
    }

    public function updateStatus(Request $request, Vendor $vendor, SellerAccountService $accounts): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([Vendor::STATUS_ACTIVE, Vendor::STATUS_SUSPENDED, Vendor::STATUS_CLOSED, Vendor::STATUS_REJECTED, Vendor::STATUS_INACTIVE])],
            'store_status' => ['required', Rule::in([Vendor::STORE_SETUP_REQUIRED, Vendor::STORE_LIVE, Vendor::STORE_TEMPORARILY_CLOSED, Vendor::STORE_SUSPENDED])],
            'reason' => ['required', 'string', 'min:5', 'max:240'],
        ]);

        if ($data['status'] === Vendor::STATUS_ACTIVE && $data['store_status'] === Vendor::STORE_LIVE) {
            $readiness = $accounts->readiness($vendor);
            if (! $readiness['publish_eligible']) {
                return back()->withErrors(['status' => 'Cannot activate store: Mandatory requirements are missing.']);
            }
        } elseif ($data['status'] === Vendor::STATUS_REJECTED) {
            if ($data['store_status'] === Vendor::STORE_LIVE) {
                return back()->withErrors(['store_status' => 'A rejected seller cannot have a live store.']);
            }
            $data['store_status'] = Vendor::STORE_SUSPENDED;
        } elseif ($data['status'] === Vendor::STATUS_SUSPENDED) {
            $data['store_status'] = Vendor::STORE_SUSPENDED;
        } elseif ($data['status'] === Vendor::STATUS_INACTIVE) {
            $data['store_status'] = Vendor::STORE_SETUP_REQUIRED;
        }

        $oldStatus = $vendor->status;
        $oldStoreStatus = $vendor->store_status;

        $vendor->forceFill([
            'status' => $data['status'],
            'store_status' => $data['store_status'],
        ])->save();

        SellerAuditLog::query()->create([
            'vendor_id' => $vendor->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'super_admin_status_update',
            'reason' => $data['reason'],
            'metadata' => [
                'previous_status' => $oldStatus,
                'previous_store_status' => $oldStoreStatus,
                'new_status' => $data['status'],
                'new_store_status' => $data['store_status'],
            ],
        ]);

        return back()->with('status', 'Seller status updated.');
    }

    public function updateBankStatus(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate([
            'bank_verification_status' => ['required', Rule::in(['submitted', 'verification_pending', 'verified', 'rejected', 'reupload_required'])],
            'reason' => ['required', 'string', 'min:5', 'max:240'],
        ]);

        $vendor->forceFill(['bank_verification_status' => $data['bank_verification_status']])->save();

        SellerAuditLog::query()->create([
            'vendor_id' => $vendor->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'bank_review',
            'reason' => $data['reason'],
            'metadata' => $data,
        ]);

        return back()->with('status', 'Bank verification status updated.');
    }

    public function commissionRules(): View
    {
        return view('admin.sellers.commissions', [
            'rules' => CommissionRule::query()->latest()->paginate(20),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'vendors' => Vendor::query()->orderBy('business_name')->get(),
        ]);
    }

    public function storeCommissionRule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'plan' => ['nullable', Rule::in([Vendor::PLAN_FREE, Vendor::PLAN_GROWTH, Vendor::PLAN_ENTERPRISE])],
            'type' => ['required', Rule::in(['percentage', 'flat'])],
            'rate' => ['required', 'numeric', 'min:0', 'max:100000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CommissionRule::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('status', 'Commission rule saved.');
    }

    public function banners(): View
    {
        return view('admin.sellers.banners', [
            'banners' => StorefrontPromotionalBanner::query()->orderBy('display_order')->paginate(20),
        ]);
    }

    public function storeBanner(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['required', 'string', 'max:500'],
            'desktop_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mobile_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cta_label' => ['required', 'string', 'max:80'],
            'cta_url' => ['required', 'string', 'max:500'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        StorefrontPromotionalBanner::query()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'desktop_image_path' => $accounts->storeUploadedFile($request->file('desktop_image'), 'seller-banners'),
            'mobile_image_path' => $accounts->storeUploadedFile($request->file('mobile_image'), 'seller-banners'),
            'cta_label' => $data['cta_label'],
            'cta_url' => $data['cta_url'],
            'display_order' => $data['display_order'],
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return back()->with('status', 'Seller promotional banner saved.');
    }
}
