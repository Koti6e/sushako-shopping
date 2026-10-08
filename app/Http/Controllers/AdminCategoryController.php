<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryReconciliationRun;
use App\Services\CategoryReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function edit(Category $category): View
    {
        return view('admin.operations.category-create', [
            'category' => $category,
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->with(['children.children'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'taxSlabs' => \App\Models\TaxSlab::query()->where('is_active', true)->orderBy('rate')->get(),
            'excludedParentIds' => $this->descendantIds($category),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $imagePath = $request->file('image')?->store(
            'category-images',
            'public'
        );

        Category::query()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'tax_slab_id' => $data['tax_slab_id'] ?? null,
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['name']),
            'tagline' => $data['tagline'] ?: 'Sushako category',
            'headline' => $data['headline'] ?: $data['name'],
            'description' => $data['description'] ?? null,
            'accent' => $data['accent'] ?: '#2f6b4f',
            'image' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
            'available_to_sellers' => $request->boolean(
                'available_to_sellers',
                true
            ),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category created.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $parentId = $data['parent_id'] ?? null;

        if ($parentId && in_array((int) $parentId, $this->descendantIds($category), true)) {
            return back()->withErrors(['parent_id' => 'A category cannot be placed below one of its own descendants.'])->withInput();
        }

        $payload = [
            'parent_id' => $parentId,
            'name' => $data['name'],
            'tax_slab_id' => $data['tax_slab_id'] ?? null,
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['name'], $category),
            'tagline' => $data['tagline'] ?: null,
            'headline' => $data['headline'] ?: null,
            'description' => $data['description'] ?: null,
            'accent' => $data['accent'] ?: '#2563eb',
            'is_active' => $request->boolean('is_active'),
            'available_to_sellers' => $request->boolean('available_to_sellers'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($category->image && ! str_starts_with($category->image, 'assets/')) {
                Storage::disk('public')->delete($category->image);
            }
            $payload['image'] = $request->file('image')->store('category-images', 'public');
        }

        $category->update($payload);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function updateStatus(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $category->update(['is_active' => (bool) $data['is_active']]);

        return back()->with('status', 'Category status updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->forceFill(['is_active' => false, 'available_to_sellers' => false])->save();

        return back()->with('status', 'Category archived. Existing products and history were preserved.');
    }

    public function reconciliationDryRun(Request $request, CategoryReconciliationService $reconciliation): View
    {
        $run = $reconciliation->dryRun($request->user()->id);
        return view('admin.operations.category-reconciliation', ['run' => $run->load('items')]);
    }

    public function reconciliationApprove(Request $request, CategoryReconciliationRun $run, CategoryReconciliationService $reconciliation): RedirectResponse
    {
        $reconciliation->approve($run, $request->user()->id);
        return back()->with('status', 'Category reconciliation dry-run approved.');
    }

    public function reconciliationApply(Request $request, CategoryReconciliationRun $run, CategoryReconciliationService $reconciliation): RedirectResponse
    {
        $reconciliation->apply($run, $request->user()->id);
        return back()->with('status', 'Approved category reconciliation applied safely.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                Rule::notIn($category ? [$category->id] : []),
            ],
            'name' => ['required', 'string', 'max:120'],
            'tax_slab_id' => ['nullable', 'integer', 'exists:tax_slabs,id'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'tagline' => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'accent' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'available_to_sellers' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function uniqueSlug(string $name, ?Category $ignore = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $count = 2;

        while (Category::query()->where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    private function descendantIds(Category $category): array
    {
        $ids = [];
        $pending = [$category->id];

        while ($pending) {
            $childIds = Category::query()->whereIn('parent_id', $pending)->pluck('id')->all();
            $ids = [...$ids, ...$childIds];
            $pending = $childIds;
        }

        return $ids;
    }
}
