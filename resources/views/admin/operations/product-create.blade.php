<x-layouts.admin title="Add Product - Admin">
    <x-admin.shell eyebrow="Catalog" title="Add Product" subtitle="Create a live Sushako product with pricing, stock and strict image uploads.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.products.index')" tone="secondary" icon="fa-solid fa-box">Products</x-admin.action>
        </x-slot:actions>
            <section class="admin-dashboard-panel">
                @if ($errors->any())
                    <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
                @endif
                <p class="eyebrow">Quick Action</p>
                <h1>Add product</h1>
                <p class="lede">Create a live Sushako product with pricing, stock and strict image uploads.</p>
                <form class="admin-edit-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <label>Product Name<input name="name" value="{{ old('name') }}" placeholder="Product name" required></label>
                    <label>Slug<input name="slug" value="{{ old('slug') }}" placeholder="auto-generated when blank"></label>
                    <label>Category
                        <select name="category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category['id'] }}" @selected(old('category_id') == $category['id'])>{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>GST Override <small>Optional. Leave blank to inherit category GST.</small>
                        <select name="tax_slab_id">
                            <option value="">Inherit from category</option>
                            @foreach ($taxSlabs as $slab)
                                <option value="{{ $slab->id }}" @selected(old('tax_slab_id') == $slab->id)>{{ $slab->name }} ({{ number_format($slab->rate, 2) }}%)</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Collection<input name="collection" value="{{ old('collection', 'Sushako Signature') }}"></label>
                    <label>Subcategory<input name="subcategory" value="{{ old('subcategory', 'Featured') }}"></label>
                    <label>Brand<input name="brand" value="{{ old('brand', 'Sushako') }}" required></label>
                    <label>Badge<input name="badge" value="{{ old('badge', 'Featured') }}"></label>
                    <label>Short Description<textarea name="short_description" required>{{ old('short_description') }}</textarea></label>
                    <label>Full Description<textarea name="full_description">{{ old('full_description') }}</textarea></label>
                    <label>MRP<input name="mrp" type="number" min="1" value="{{ old('mrp') }}" required></label>
                    <label>Selling Price<input name="selling_price" type="number" min="1" value="{{ old('selling_price') }}" required></label>
                    <label>Status
                        <select name="status">
                            <option value="published" @selected(old('status', 'published') === 'published')>Published</option>
                            <option value="unpublished" @selected(old('status') === 'unpublished')>Unpublished</option>
                        </select>
                    </label>
                    <label>Colour <small>Optional. Use for clothing or products with colour choices.</small><input name="colour" value="{{ old('colour') }}" placeholder="Example: Black"></label>
                    <label>Colour Hex <small>Optional swatch colour.</small><input name="colour_hex" value="{{ old('colour_hex') }}" placeholder="#111111"></label>
                    <label>Size <small>Optional. Usually for clothing only.</small><input name="size" value="{{ old('size') }}" placeholder="Example: M"></label>
                    <label>Opening Stock<input name="stock" type="number" min="0" value="{{ old('stock', 10) }}" required></label>
                    <x-ui.file-upload class="admin-field-wide" name="images[]" label="Upload product images" hint="Only JPG, PNG or WebP. Max 4 MB each." accept="image/jpeg,image/png,image/webp" multiple />
                    <div class="admin-form-actions admin-field-wide">
                        <button class="button button--primary" type="submit">Save Product</button>
                    </div>
                </form>
            </section>
    </x-admin.shell>
</x-layouts.admin>
