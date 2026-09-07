<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FinancialAuditLog;
use App\Models\SellerCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryRequestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([SellerCategoryRequest::STATUS_PENDING, SellerCategoryRequest::STATUS_APPROVED, SellerCategoryRequest::STATUS_REJECTED])],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        return view('admin.catalog.category-requests', [
            'requests' => SellerCategoryRequest::query()
                ->with(['vendor', 'suggestedParent', 'resolvedCategory'])
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                    $query->where('requested_category_name', 'like', '%'.$search.'%')
                        ->orWhereHas('vendor', fn ($vendor) => $vendor->where('business_name', 'like', '%'.$search.'%'));
                }))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'categories' => Category::query()->where('is_active', true)->with('parent')->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function update(Request $request, SellerCategoryRequest $categoryRequest): RedirectResponse
    {
        abort_if($categoryRequest->status !== SellerCategoryRequest::STATUS_PENDING, 422, 'This category request has already been reviewed.');

        $data = $request->validate([
            'status' => ['required', Rule::in([SellerCategoryRequest::STATUS_APPROVED, SellerCategoryRequest::STATUS_REJECTED])],
            'admin_comment' => ['required', 'string', 'min:3', 'max:500'],
            'resolved_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'new_category_name' => ['nullable', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        if ($data['status'] === SellerCategoryRequest::STATUS_APPROVED && empty($data['resolved_category_id']) && empty($data['new_category_name'])) {
            return back()->withErrors(['resolved_category_id' => 'Choose an existing category or provide a name for the category to create.'])->withInput();
        }

        DB::transaction(function () use ($categoryRequest, $data, $request): void {
            $resolvedCategory = null;

            if ($data['status'] === SellerCategoryRequest::STATUS_APPROVED) {
                $resolvedCategory = ! empty($data['resolved_category_id'])
                    ? Category::query()->findOrFail($data['resolved_category_id'])
                    : $this->resolveCategory($data, $categoryRequest);

                $categoryRequest->vendor->categories()->syncWithoutDetaching([$resolvedCategory->id]);
            }

            $categoryRequest->forceFill([
                'status' => $data['status'],
                'admin_comment' => $data['admin_comment'],
                'resolved_category_id' => $resolvedCategory?->id,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ])->save();

            FinancialAuditLog::query()->create([
                'vendor_id' => $categoryRequest->vendor_id,
                'admin_user_id' => $request->user()->id,
                'action' => 'seller_category_request_'.$data['status'],
                'reason' => $data['admin_comment'],
                'metadata' => ['category_id' => $resolvedCategory?->id],
            ]);
        });

        return back()->with('status', $data['status'] === SellerCategoryRequest::STATUS_APPROVED ? 'Request approved and category is available to the seller.' : 'Category request rejected.');
    }

    private function resolveCategory(array $data, SellerCategoryRequest $request): Category
    {
        $name = trim($data['new_category_name'] ?: $request->requested_category_name);
        $parentId = $data['parent_id'] ?? $request->suggested_parent_id;

        $existing = Category::query()
            ->where('parent_id', $parentId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $parentSlug = $parentId ? Category::query()->find($parentId)?->slug.'-' : '';
        $slug = $this->uniqueSlug($parentSlug.Str::slug($name));

        return Category::query()->create([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'tagline' => 'Marketplace category',
            'headline' => $name,
            'description' => $request->description ?: 'Products listed in this marketplace category.',
            'accent' => '#2563eb',
            'is_active' => true,
            'available_to_sellers' => true,
            'sort_order' => 0,
        ]);
    }

    private function uniqueSlug(string $base): string
    {
        $base = $base ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
