<x-layouts.admin title="Add Product - Admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
            <nav aria-label="Admin navigation">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.categories.index') }}">Categories</a>
                <a href="{{ route('admin.products.index') }}">Products</a>
                <a href="{{ route('admin.inventory.index') }}">Inventory</a>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
                <a href="{{ route('admin.customers.index') }}">Customers</a>
                <a href="{{ route('admin.settings.company') }}"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <x-admin.side-meta />
        </aside>
        <main class="admin-main">
            <header class="admin-topbar"><x-brand.logo href="{{ route('admin.dashboard') }}" loading="eager" /><span>Add Product</span></header>
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
                    <label class="admin-upload-button">
                        <span><i class="fa-solid fa-image" aria-hidden="true"></i> Upload Product Images</span>
                        <input name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp">
                        <small>Only JPG, PNG or WEBP. Max 4 MB each.</small>
                    </label>
                    <button class="button button--primary" type="submit">Save Product</button>
                </form>
            </section>
        </main>
    </div>
</x-layouts.admin>
