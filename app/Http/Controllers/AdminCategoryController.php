<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'name' => ['required', 'string', 'max:120'],
            'tax_slab_id' => ['required', 'integer', 'exists:tax_slabs,id'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:categories,slug'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:1000'],
            'accent' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'available_to_sellers' => ['nullable', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $imagePath = $request->file('image')?->store(
            'category-images',
            'public'
        );

        Category::query()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'tax_slab_id' => $data['tax_slab_id'],
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['name']),
            'tagline' => $data['tagline'] ?: 'Sushako category',
            'headline' => $data['headline'] ?: $data['name'],
            'description' => $data['description'],
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

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' =>
                    'Move or delete products in this category before deleting it.',
            ]);
        }

        if ($category->children()->exists()) {
            return back()->withErrors([
                'category' =>
                    'Delete or move child categories before deleting this category.',
            ]);
        }

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $count = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}