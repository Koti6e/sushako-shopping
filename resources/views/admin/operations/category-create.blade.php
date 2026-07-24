<x-layouts.admin title="Add Category - Admin">
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
            <header class="admin-topbar"><x-brand.logo href="{{ route('admin.dashboard') }}" loading="eager" /><span>Add Category</span></header>
            <section class="admin-dashboard-panel">
                @if ($errors->any())
                    <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
                @endif
                <p class="eyebrow">Quick Action</p>
                <h1>Add category</h1>
                <p class="lede">Create a storefront category for products, filters and menu navigation.</p>
                <form class="admin-edit-form" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                    @csrf
                    <label>Category Name<input name="name" value="{{ old('name') }}" placeholder="Example: Electronics" required></label>
                    <label>GST Slab
                        <select name="tax_slab_id" required>
                            <option value="">Select GST slab</option>
                            @foreach ($taxSlabs as $slab)
                                <option value="{{ $slab->id }}" @selected(old('tax_slab_id') == $slab->id)>{{ $slab->name }} ({{ number_format($slab->rate, 2) }}%)</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Slug<input name="slug" value="{{ old('slug') }}" placeholder="auto-generated when blank"></label>
                    <label>Tagline<input name="tagline" value="{{ old('tagline') }}" placeholder="Category tagline"></label>
                    <label>Headline<input name="headline" value="{{ old('headline') }}" placeholder="Category headline"></label>
                    <label>Description<textarea name="description" placeholder="Short category description" required>{{ old('description') }}</textarea></label>
                    <label>Accent Colour<input name="accent" value="{{ old('accent', '#2f6b4f') }}"></label>
                    <label class="admin-upload-button">
                        <span><i class="fa-solid fa-image" aria-hidden="true"></i> Upload Category Banner</span>
                        <input name="image" type="file" accept="image/jpeg,image/png,image/webp">
                        <small>Only JPG, PNG or WEBP. Max 4 MB.</small>
                    </label>
                    <button class="button button--primary" type="submit">Save Category</button>
                </form>
            </section>
        </main>
    </div>
</x-layouts.admin>
