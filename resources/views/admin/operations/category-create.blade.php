@php
    $category = $category ?? null;
    $isEdit = (bool) $category;
    $excludedParentIds = $excludedParentIds ?? [];
    $selectedParent = old('parent_id', $category?->parent_id);
@endphp

<x-layouts.admin title="{{ $isEdit ? 'Edit Category' : 'Add Category' }} - Admin">
    <x-admin.shell eyebrow="Catalog" title="{{ $isEdit ? 'Edit Category' : 'Add Category' }}" subtitle="{{ $isEdit ? 'Keep the marketplace taxonomy accurate and available to sellers.' : 'Create a top-level category or a focused subcategory.' }}">
        <x-slot:actions><x-admin.action :href="route('admin.categories.index')" tone="secondary" icon="fa-solid fa-layer-group">Categories</x-admin.action></x-slot:actions>

        <section class="admin-dashboard-panel">
            @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
            <form class="admin-edit-form" method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($isEdit) @method('PUT') @endif
                <label>Parent Category<select name="parent_id"><option value="">None - top-level category</option>
                    @foreach ($categories as $parent)
                        <option value="{{ $parent->id }}" @selected((string) $selectedParent === (string) $parent->id) @disabled(in_array($parent->id, $excludedParentIds, true))>{{ $parent->name }}</option>
                        @foreach ($parent->children as $child)
                            <option value="{{ $child->id }}" @selected((string) $selectedParent === (string) $child->id) @disabled(in_array($child->id, $excludedParentIds, true))>&nbsp;&nbsp;{{ $child->name }}</option>
                            @foreach ($child->children as $grandchild)
                                <option value="{{ $grandchild->id }}" @selected((string) $selectedParent === (string) $grandchild->id) @disabled(in_array($grandchild->id, $excludedParentIds, true))>&nbsp;&nbsp;&nbsp;&nbsp;{{ $grandchild->name }}</option>
                            @endforeach
                        @endforeach
                    @endforeach
                </select></label>
                <label>Category Name<input name="name" value="{{ old('name', $category?->name) }}" required maxlength="120"></label>
                <label>GST Slab<select name="tax_slab_id"><option value="">No default tax slab</option>@foreach ($taxSlabs as $slab)<option value="{{ $slab->id }}" @selected((string) old('tax_slab_id', $category?->tax_slab_id) === (string) $slab->id)>{{ $slab->name }} ({{ number_format($slab->rate, 2) }}%)</option>@endforeach</select></label>
                <label>Slug<input name="slug" value="{{ old('slug', $category?->slug) }}" maxlength="140"><small>Leave blank to generate a stable slug.</small></label>
                <label>Tagline<input name="tagline" value="{{ old('tagline', $category?->tagline) }}" maxlength="120"></label>
                <label>Headline<input name="headline" value="{{ old('headline', $category?->headline) }}" maxlength="160"></label>
                <label class="admin-field-wide">Description<textarea name="description" maxlength="1000">{{ old('description', $category?->description) }}</textarea></label>
                <label>Accent Colour<input name="accent" value="{{ old('accent', $category?->accent ?: '#2563eb') }}" maxlength="20"></label>
                <label>Display Order<input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $category?->sort_order ?? 0) }}"></label>
                <label class="admin-checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true))> Active</label>
                <label class="admin-checkbox"><input type="checkbox" name="available_to_sellers" value="1" @checked(old('available_to_sellers', $category?->available_to_sellers ?? true))> Available to Sellers</label>
                <x-ui.file-upload class="admin-field-wide" name="image" label="Category banner" hint="Optional. JPG, PNG or WebP up to 4 MB." accept="image/jpeg,image/png,image/webp" />
                <div class="admin-form-actions admin-field-wide"><a class="button button--ghost" href="{{ route('admin.categories.index') }}">Cancel</a><button class="button button--primary" type="submit">{{ $isEdit ? 'Save Category' : 'Create Category' }}</button></div>
            </form>
        </section>
    </x-admin.shell>
</x-layouts.admin>
