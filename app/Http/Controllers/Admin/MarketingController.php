<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MarketingContent;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Services\SellerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(array_keys($this->contentTypes()))],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        return view('admin.marketing.index', [
            'contents' => MarketingContent::query()
                ->with(['category', 'product', 'productCollection'])
                ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('title', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%')))
                ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
                ->orderBy('placement')
                ->orderBy('display_order')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
            'contentTypes' => $this->contentTypes(),
        ]);
    }

    public function create(): View
    {
        return view('admin.marketing.form', $this->formData(new MarketingContent([
            'type' => MarketingContent::TYPE_PROMOTION,
            'placement' => 'homepage_promotion',
            'destination_type' => MarketingContent::DESTINATION_NONE,
            'is_active' => true,
        ])));
    }

    public function store(Request $request, SellerAccountService $accounts): RedirectResponse
    {
        $content = MarketingContent::query()->create($this->payload($request, $accounts));

        return redirect()->route('admin.content.edit', $content)->with('status', 'Content item created.');
    }

    public function edit(MarketingContent $content): View
    {
        return view('admin.marketing.form', $this->formData($content));
    }

    public function update(Request $request, MarketingContent $content, SellerAccountService $accounts): RedirectResponse
    {
        $payload = $this->payload($request, $accounts, $content);
        $content->update($payload);

        return redirect()->route('admin.content.edit', $content)->with('status', 'Content item updated.');
    }

    public function updateStatus(Request $request, MarketingContent $content): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $content->update(['is_active' => (bool) $data['is_active']]);

        return back()->with('status', 'Content status updated.');
    }

    public function destroy(MarketingContent $content): RedirectResponse
    {
        foreach ([$content->image_path, $content->mobile_image_path] as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $content->delete();

        return redirect()->route('admin.content.index')->with('status', 'Content item deleted.');
    }

    public function collections(): View
    {
        return view('admin.marketing.collections', [
            'collections' => ProductCollection::query()->with(['category', 'products'])->orderBy('display_order')->paginate(20),
            'categories' => $this->categoryOptions(),
            'products' => $this->productOptions(),
        ]);
    }

    public function storeCollection(Request $request): RedirectResponse
    {
        $data = $this->collectionData($request);
        $collection = ProductCollection::query()->create($data['payload']);
        $this->syncProducts($collection, $data['product_ids']);

        return back()->with('status', 'Product collection created.');
    }

    public function updateCollection(Request $request, ProductCollection $collection): RedirectResponse
    {
        $data = $this->collectionData($request, $collection);
        $collection->update($data['payload']);
        $this->syncProducts($collection, $data['product_ids']);

        return back()->with('status', 'Product collection updated.');
    }

    private function payload(Request $request, SellerAccountService $accounts, ?MarketingContent $content = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys($this->contentTypes()))],
            'placement' => ['required', Rule::in(array_keys($this->placements()))],
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('marketing_contents', 'slug')->ignore($content?->id)],
            'subtitle' => ['nullable', 'string', 'max:240'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'destination_type' => ['required', Rule::in($this->destinationTypes())],
            'destination_url' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_collection_id' => ['nullable', 'integer', 'exists:product_collections,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->validateDestination($data);

        $payload = array_merge(collect($data)->except(['image', 'mobile_image'])->all(), [
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['title'], $content),
            'is_active' => $request->boolean('is_active'),
            'display_order' => $data['display_order'] ?? 0,
        ]);

        foreach (['image', 'mobile_image'] as $field) {
            if ($request->hasFile($field)) {
                $pathColumn = $field === 'image' ? 'image_path' : 'mobile_image_path';
                if ($content?->{$pathColumn}) {
                    Storage::disk('public')->delete($content->{$pathColumn});
                }
                $payload[$pathColumn] = $accounts->storeUploadedFile($request->file($field), 'marketing-content');
            }
        }

        return $payload;
    }

    private function collectionData(Request $request, ?ProductCollection $collection = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('product_collections', 'slug')->ignore($collection?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_ids' => ['nullable', 'array', 'max:48'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'payload' => array_merge(collect($data)->except('product_ids')->all(), [
                'slug' => $data['slug'] ?: $this->uniqueCollectionSlug($data['name'], $collection),
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => $request->boolean('is_active'),
            ]),
            'product_ids' => $data['product_ids'] ?? [],
        ];
    }

    private function formData(MarketingContent $content): array
    {
        return [
            'content' => $content,
            'contentTypes' => $this->contentTypes(),
            'placements' => $this->placements(),
            'destinationTypes' => $this->destinationTypes(),
            'categories' => $this->categoryOptions(),
            'products' => $this->productOptions(),
            'collections' => ProductCollection::query()->orderBy('name')->get(),
        ];
    }

    private function categoryOptions()
    {
        return Category::query()->where('is_active', true)->with('parent')->orderBy('name')->get();
    }

    private function productOptions()
    {
        return Product::query()->with('vendor')->latest()->limit(200)->get();
    }

    private function contentTypes(): array
    {
        return [
            MarketingContent::TYPE_HERO => 'Hero banner',
            MarketingContent::TYPE_PROMOTION => 'Promotional banner',
            MarketingContent::TYPE_CATEGORY => 'Category banner',
            MarketingContent::TYPE_COLLECTION => 'Product collection',
            MarketingContent::TYPE_CAMPAIGN => 'Campaign',
            MarketingContent::TYPE_ANNOUNCEMENT => 'Announcement',
        ];
    }

    private function placements(): array
    {
        return [
            'homepage_hero' => 'Homepage hero',
            'homepage_announcement' => 'Homepage announcement',
            'homepage_promotion' => 'Homepage promotion',
            'homepage_collection' => 'Homepage collection',
            'homepage_category' => 'Homepage category feature',
        ];
    }

    private function destinationTypes(): array
    {
        return [
            MarketingContent::DESTINATION_NONE => 'No destination',
            MarketingContent::DESTINATION_CATEGORY => 'Marketplace category',
            MarketingContent::DESTINATION_PRODUCT => 'Product',
            MarketingContent::DESTINATION_COLLECTION => 'Product collection',
            MarketingContent::DESTINATION_URL => 'Custom URL',
        ];
    }

    private function validateDestination(array $data): void
    {
        $field = match ($data['destination_type']) {
            MarketingContent::DESTINATION_CATEGORY => 'category_id',
            MarketingContent::DESTINATION_PRODUCT => 'product_id',
            MarketingContent::DESTINATION_COLLECTION => 'product_collection_id',
            MarketingContent::DESTINATION_URL => 'destination_url',
            default => null,
        };

        if ($field && blank($data[$field] ?? null)) {
            validator([], [$field => ['required']])->validate();
        }
    }

    private function uniqueSlug(string $title, ?MarketingContent $content = null): string
    {
        return $this->uniqueValue('marketing_contents', Str::slug($title) ?: 'content', $content?->id);
    }

    private function uniqueCollectionSlug(string $name, ?ProductCollection $collection = null): string
    {
        return $this->uniqueValue('product_collections', Str::slug($name) ?: 'collection', $collection?->id);
    }

    private function uniqueValue(string $table, string $base, ?int $ignoreId = null): string
    {
        $value = $base;
        $suffix = 2;

        while (DB::table($table)->where('slug', $value)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $value = $base.'-'.$suffix++;
        }

        return $value;
    }

    private function syncProducts(ProductCollection $collection, array $productIds): void
    {
        $collection->products()->sync(collect($productIds)->values()->mapWithKeys(
            fn ($productId, $position) => [$productId => ['display_order' => $position + 1]]
        )->all());
    }
}
