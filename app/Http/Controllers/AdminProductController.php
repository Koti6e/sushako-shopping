<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\TaxSlab;
use App\Support\ProductCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => ProductCatalog::products(),
        ]);
    }

    public function create(): View
    {
        return view('admin.operations.product-create', [
            'categories' => ProductCatalog::categories(),
            'taxSlabs' => TaxSlab::query()->where('is_active', true)->orderBy('rate')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $product = Product::query()->create($this->productPayload($data));

        $this->syncVariant($product, $data);
        $this->storeImages($request, $product);

        return redirect()->route('admin.products.edit', $product->slug)->with('status', 'Product created and published in the Sushako catalog.');
    }

    public function edit(string $slug): View
    {
        $productModel = Product::query()->with(['category', 'images', 'variants'])->where('slug', $slug)->first();
        abort_unless($productModel, 404);

        return view('admin.products.edit', [
            'product' => ProductCatalog::productArray($productModel),
            'productModel' => $productModel,
            'categories' => ProductCatalog::categories(),
            'taxSlabs' => TaxSlab::query()->where('is_active', true)->orderBy('rate')->get(),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $product = Product::query()->with(['images', 'variants'])->where('slug', $slug)->first();
        abort_unless($product, 404);

        $data = $this->validatedProduct($request, $product);
        $product->fill($this->productPayload($data, $product))->save();

        foreach ($request->input('variants', []) as $variantId => $variantData) {
            $variant = $product->variants()->whereKey($variantId)->first();

            if ($variant) {
                $variant->fill([
                    'colour' => ($variantData['colour'] ?? null) ?: 'Standard',
                    'colour_hex' => ($variantData['colour_hex'] ?? null) ?: '#d8ccb9',
                    'size' => ($variantData['size'] ?? null) ?: 'Standard',
                    'stock' => max(0, (int) ($variantData['stock'] ?? $variant->stock)),
                ])->save();
            }
        }

        $this->syncVariant($product, $data, createOnlyIfMissing: true);
        $this->storeImages($request, $product);

        return back()->with('status', 'Product updated successfully.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        $product = Product::query()->with('images')->where('slug', $slug)->first();
        abort_unless($product, 404);

        foreach ($product->images as $image) {
            $this->deleteStoredImage($image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted from the Sushako catalog.');
    }

    public function destroyImage(ProductImage $image): RedirectResponse
    {
        $product = $image->product;
        abort_unless($product, 404);

        $this->deleteStoredImage($image);
        $image->delete();

        return redirect()->route('admin.products.edit', $product->slug)->with('status', 'Product image removed.');
    }

    private function validatedProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tax_slab_id' => ['nullable', 'integer', 'exists:tax_slabs,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('products', 'slug')->ignore($product?->id)],
            'collection' => ['nullable', 'string', 'max:120'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'brand' => ['required', 'string', 'max:120'],
            'badge' => ['nullable', 'string', 'max:60'],
            'short_description' => ['required', 'string', 'max:500'],
            'full_description' => ['nullable', 'string', 'max:3000'],
            'fabric' => ['nullable', 'string', 'max:120'],
            'fit' => ['nullable', 'string', 'max:120'],
            'sleeve' => ['nullable', 'string', 'max:120'],
            'pattern' => ['nullable', 'string', 'max:120'],
            'occasion' => ['nullable', 'string', 'max:120'],
            'country_of_origin' => ['nullable', 'string', 'max:80'],
            'return_policy' => ['nullable', 'string', 'max:160'],
            'mrp' => ['required', 'integer', 'min:1'],
            'selling_price' => ['required', 'integer', 'min:1', 'lte:mrp'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['published', 'unpublished'])],
            'is_new' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'colour' => ['nullable', 'string', 'max:80'],
            'colour_hex' => ['nullable', 'string', 'max:20'],
            'size' => ['nullable', 'string', 'max:80'],
            'stock' => ['required', 'integer', 'min:0'],
            'variants' => ['nullable', 'array'],
            'variants.*.colour' => ['nullable', 'string', 'max:80'],
            'variants.*.colour_hex' => ['nullable', 'string', 'max:20'],
            'variants.*.size' => ['nullable', 'string', 'max:80'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function productPayload(array $data, ?Product $product = null): array
    {
        return [
            'category_id' => $data['category_id'],
            'tax_slab_id' => $data['tax_slab_id'] ?? null,
            'name' => $data['name'],
            'slug' => ($data['slug'] ?? null) ?: $this->uniqueSlug($data['name'], $product),
            'collection' => ($data['collection'] ?? null) ?: 'Sushako Signature',
            'subcategory' => ($data['subcategory'] ?? null) ?: 'Featured',
            'brand' => $data['brand'],
            'badge' => ($data['badge'] ?? null) ?: 'Featured',
            'short_description' => $data['short_description'],
            'full_description' => ($data['full_description'] ?? null) ?: $data['short_description'],
            'fabric' => ($data['fabric'] ?? null) ?: 'Premium Finish',
            'fit' => ($data['fit'] ?? null) ?: 'Standard',
            'sleeve' => ($data['sleeve'] ?? null) ?: 'Not Applicable',
            'pattern' => ($data['pattern'] ?? null) ?: 'Premium Finish',
            'occasion' => ($data['occasion'] ?? null) ?: 'Everyday Shopping',
            'country_of_origin' => ($data['country_of_origin'] ?? null) ?: 'India',
            'return_policy' => ($data['return_policy'] ?? null) ?: 'Easy return support',
            'mrp' => $data['mrp'],
            'selling_price' => $data['selling_price'],
            'rating' => $data['rating'] ?? 5.0,
            'reviews' => $data['reviews'] ?? 0,
            'is_published' => $data['status'] === 'published',
            'is_new' => (bool) ($data['is_new'] ?? false),
            'is_best_seller' => (bool) ($data['is_best_seller'] ?? false),
        ];
    }

    private function syncVariant(Product $product, array $data, bool $createOnlyIfMissing = false): void
    {
        $variant = $product->variants()
            ->where('colour', ($data['colour'] ?? null) ?: 'Standard')
            ->where('size', ($data['size'] ?? null) ?: 'Standard')
            ->first();

        if ($createOnlyIfMissing && $variant) {
            return;
        }

        $payload = [
            'sku' => $variant?->sku ?: $this->uniqueSku($product),
            'colour' => ($data['colour'] ?? null) ?: 'Standard',
            'colour_hex' => ($data['colour_hex'] ?? null) ?: '#d8ccb9',
            'size' => ($data['size'] ?? null) ?: 'Standard',
            'stock' => $data['stock'],
        ];

        $variant ? $variant->fill($payload)->save() : $product->variants()->create($payload);
    }

    private function storeImages(Request $request, Product $product): void
    {
        foreach ($request->file('images', []) as $index => $file) {
            $path = $file->store('product-images', 'public');
            $product->images()->create([
                'path' => $path,
                'label' => $index === 0 && ! $product->images()->exists() ? 'Primary View' : 'Product View',
                'sort_order' => (int) $product->images()->max('sort_order') + 1,
            ]);
        }
    }

    private function deleteStoredImage(ProductImage $image): void
    {
        if (! Str::startsWith($image->path, ['assets/', '/assets/', 'images/', '/images/'])) {
            Storage::disk('public')->delete($image->path);
        }
    }

    private function uniqueSlug(string $name, ?Product $product = null): string
    {
        $base = Str::slug($name) ?: 'sushako-product';
        $slug = $base;
        $count = 2;

        while (Product::query()->where('slug', $slug)->when($product, fn ($query) => $query->whereKeyNot($product->id))->exists()) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }

    private function uniqueSku(Product $product): string
    {
        $base = 'SS-'.strtoupper(Str::limit(Str::slug($product->name, ''), 8, '') ?: 'PRODUCT');
        $sku = $base.'-001';
        $count = 2;

        while (ProductVariant::query()->where('sku', $sku)->exists()) {
            $sku = $base.'-'.str_pad((string) $count++, 3, '0', STR_PAD_LEFT);
        }

        return $sku;
    }
}
