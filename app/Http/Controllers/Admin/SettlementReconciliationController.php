<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialAuditLog;
use App\Models\SellerCategoryRequest;
use App\Models\SellerSettlement;
use App\Models\SettlementAdjustment;
use App\Models\Vendor;
use App\Services\ManualSettlementPaymentService;
use App\Services\SettlementBatchService;
use App\Services\SettlementCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettlementReconciliationController extends Controller
{
    public function index(Request $request): View
    {
        $settlements = SellerSettlement::query()
            ->with('vendor')
            ->when($request->query('seller'), fn ($query, string $seller) => $query->where('vendor_id', $seller))
            ->when($request->query('status'), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.settlements.index', [
            'settlements' => $settlements,
            'vendors' => Vendor::query()->orderBy('business_name')->get(),
            'summary' => [
                'gross_sales' => (float) SellerSettlement::query()->sum('gross_amount'),
                'seller_payable' => (float) SellerSettlement::query()->sum('final_settlement_amount'),
                'platform_fees' => (float) SellerSettlement::query()->sum('platform_fee_amount'),
                'refund_deductions' => (float) SellerSettlement::query()->sum('refund_amount'),
                'pending' => (float) SellerSettlement::query()->whereIn('status', ['draft', 'pending_approval', 'approved'])->sum('final_settlement_amount'),
                'processing' => (float) SellerSettlement::query()->where('status', 'processing')->sum('final_settlement_amount'),
                'settled' => (float) SellerSettlement::query()->where('status', 'paid')->sum('final_settlement_amount'),
                'failed_hold' => (float) SellerSettlement::query()->whereIn('status', ['failed', 'on_hold'])->sum('final_settlement_amount'),
            ],
        ]);
    }

    public function preview(Request $request, SettlementCalculationService $calculator): View
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $vendor = Vendor::query()->findOrFail($data['vendor_id']);

        return view('admin.settlements.preview', [
            'vendor' => $vendor,
            'vendors' => Vendor::query()->orderBy('business_name')->get(),
            'summary' => $calculator->summarize($vendor, $data['start_date'] ?? null, $data['end_date'] ?? null),
            'items' => $calculator->eligibleItemsQuery($vendor, $data['start_date'] ?? null, $data['end_date'] ?? null)->paginate(50)->withQueryString(),
            'filters' => $data,
        ]);
    }

    public function generate(Request $request, SettlementBatchService $batches): RedirectResponse
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $settlement = $batches->create(Vendor::query()->findOrFail($data['vendor_id']), $data['start_date'] ?? null, $data['end_date'] ?? null, $request->user()->id);

        return redirect()->route('admin.settlements.show', $settlement)->with('status', 'Settlement batch generated for approval.');
    }

    public function show(SellerSettlement $settlement): View
    {
        return view('admin.settlements.show', [
            'settlement' => $settlement->load(['vendor', 'items.orderItem.order', 'adjustments']),
        ]);
    }

    public function transition(Request $request, SellerSettlement $settlement, SettlementBatchService $batches): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'processing', 'on_hold', 'failed', 'cancelled'])],
            'reason' => ['required_if:status,on_hold,failed,cancelled', 'nullable', 'string', 'max:500'],
        ]);

        $extra = match ($data['status']) {
            'on_hold' => ['hold_reason' => $data['reason']],
            'failed' => ['failure_reason' => $data['reason']],
            default => [],
        };

        $batches->transition($settlement, $data['status'], $request->user()->id, $data['reason'] ?? null, $extra);

        return back()->with('status', 'Settlement status updated.');
    }

    public function adjustment(Request $request, SellerSettlement $settlement): RedirectResponse
    {
        abort_if($settlement->status === 'paid', 422, 'Paid settlements are immutable. Apply this correction in a future settlement.');

        $data = $request->validate([
            'adjustment_type' => ['required', 'string', 'max:80'],
            'amount' => ['required', 'numeric', 'not_in:0', 'min:-999999', 'max:999999'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::transaction(function () use ($settlement, $data, $request): void {
            SettlementAdjustment::query()->create($data + [
                'seller_settlement_id' => $settlement->id,
                'vendor_id' => $settlement->vendor_id,
                'admin_user_id' => $request->user()->id,
            ]);

            $adjustment = (float) $settlement->adjustments()->sum('amount') + (float) $data['amount'];
            $settlement->forceFill([
                'adjustment_amount' => $adjustment,
                'other_adjustments' => $adjustment,
                'final_settlement_amount' => max(0, (float) $settlement->gross_amount - (float) $settlement->platform_fee_amount - (float) $settlement->refund_amount - (float) $settlement->return_amount + $adjustment),
                'net_payable' => max(0, (float) $settlement->gross_amount - (float) $settlement->platform_fee_amount - (float) $settlement->refund_amount - (float) $settlement->return_amount + $adjustment),
            ])->save();
        });

        return back()->with('status', 'Manual adjustment added.');
    }

    public function markPaid(Request $request, SellerSettlement $settlement, ManualSettlementPaymentService $payment): RedirectResponse
    {
        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_reference' => ['required', 'string', 'max:120'],
            'payment_mode' => ['required', 'string', 'max:80'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $payment->markPaid($settlement, $data, $request->user()->id);

        return back()->with('status', 'Manual settlement marked paid.');
    }

    public function categoryRequests(): View
    {
        return view('admin.settlements.category-requests', [
            'requests' => SellerCategoryRequest::query()->with('vendor')->latest()->paginate(20),
        ]);
    }

    public function updateCategoryRequest(Request $request, SellerCategoryRequest $categoryRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_comment' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $categoryRequest->forceFill($data + [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        FinancialAuditLog::query()->create([
            'vendor_id' => $categoryRequest->vendor_id,
            'admin_user_id' => $request->user()->id,
            'action' => 'seller_category_request_'.$data['status'],
            'reason' => $data['admin_comment'],
        ]);

        return back()->with('status', 'Category request reviewed.');
    }
}
