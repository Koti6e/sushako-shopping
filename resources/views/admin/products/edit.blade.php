<x-layouts.admin title="Edit Product - Admin">
    <x-admin.shell eyebrow="Catalog" title="Edit Product" :badge="$product['name']" subtitle="Update product details, pricing, stock and images.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.products.index')" tone="ghost" icon="fa-solid fa-arrow-left">Products</x-admin.action>
            <x-admin.action :href="route('products.show', $product['slug'])" tone="secondary" icon="fa-regular fa-eye" target="_blank" rel="noopener noreferrer">View Product</x-admin.action>
        </x-slot:actions>
            <section class="admin-dashboard-panel">
                @php
                    $firstVariant = $product['variants'][0] ?? ['colour' => 'Standard', 'size' => 'Standard', 'stock' => 0];
                    $firstVariantColour = $firstVariant['colour'] ?? 'Standard';
                    $firstVariantSize = $firstVariant['size'] ?? 'Standard';
                @endphp
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
                @endif
                <p class="eyebrow">Admin Panel</p>
                <h1>{{ $product['name'] }}</h1>
                <form class="admin-edit-form" method="POST" action="{{ route('admin.products.update', $product['slug']) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <label>Name <input name="name" value="{{ old('name', $product['name']) }}" required></label>
                    <label>Slug <input name="slug" value="{{ old('slug', $product['slug']) }}" required></label>
                    <label>Category
                        <select name="category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category['id'] }}" @selected((int) old('category_id', $productModel->category_id) === (int) $category['id'])>{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>GST Override <small>Optional. Leave blank to inherit category GST.</small>
                        <select name="tax_slab_id">
                            <option value="">Inherit from category</option>
                            @foreach ($taxSlabs as $slab)
                                <option value="{{ $slab->id }}" @selected((int) old('tax_slab_id', $productModel->tax_slab_id) === $slab->id)>{{ $slab->name }} ({{ number_format($slab->rate, 2) }}%)</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Collection <input name="collection" value="{{ old('collection', $product['collection']) }}"></label>
                    <label>Subcategory <input name="subcategory" value="{{ old('subcategory', $product['subcategory']) }}"></label>
                    <label>Brand <input name="brand" value="{{ old('brand', $product['brand']) }}" required></label>
                    <label>Badge <input name="badge" value="{{ old('badge', $product['badge']) }}"></label>
                    <label>Short Description <textarea name="short_description" required>{{ old('short_description', $product['short_description']) }}</textarea></label>
                    <label>Full Description <textarea name="full_description">{{ old('full_description', $product['full_description']) }}</textarea></label>
                    <label>MRP <input name="mrp" type="number" min="1" value="{{ old('mrp', $product['mrp']) }}" required></label>
                    <label>Selling Price <input name="selling_price" type="number" min="1" value="{{ old('selling_price', $product['selling_price']) }}" required></label>
                    <label>Status
                        <select name="status">
                            <option value="published" @selected(old('status', $productModel->is_published ? 'published' : 'unpublished') === 'published')>Published</option>
                            <option value="unpublished" @selected(old('status', $productModel->is_published ? 'published' : 'unpublished') === 'unpublished')>Unpublished</option>
                        </select>
                    </label>
                    <input type="hidden" name="colour" value="{{ $firstVariantColour === 'Standard' ? '' : $firstVariantColour }}">
                    <input type="hidden" name="colour_hex" value="{{ $product['colours'][$firstVariantColour]['hex'] ?? '' }}">
                    <input type="hidden" name="size" value="{{ $firstVariantSize === 'Standard' ? '' : $firstVariantSize }}">
                    <input type="hidden" name="stock" value="{{ $firstVariant['stock'] ?? 0 }}">
                    <x-ui.file-upload class="admin-field-wide" name="images[]" label="Add product images" hint="Only JPG, PNG or WebP. Max 4 MB each." accept="image/jpeg,image/png,image/webp" multiple />
                    <div class="admin-image-grid">
                        @foreach ($product['images'] as $image)
                            <figure>
                                <x-product.image :src="$image['path']" :alt="$product['name'].' '.$image['label']" />
                                <figcaption>{{ $image['label'] }}</figcaption>
                                @if ($image['id'])
                                    <button
                                        form="delete-product-image-{{ $image['id'] }}"
                                        type="submit"
                                        class="button button--ghost"
                                    >Remove Image</button>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                    <div class="admin-editor-grid admin-editor-grid--variants">
                        <section>
                            <h2>Variant Options</h2>
                            <p>Leave colour or size as Standard when the product does not need that option.</p>
                            @foreach ($product['variants'] as $variant)
                                <label>
                                    Colour
                                    <input name="variants[{{ $variant['id'] }}][colour]" value="{{ $variant['colour'] }}">
                                </label>
                                <label>
                                    Hex
                                    <input name="variants[{{ $variant['id'] }}][colour_hex]" value="{{ $product['colours'][$variant['colour']]['hex'] ?? '#14213d' }}">
                                </label>
                                <label>
                                    Size
                                    <input name="variants[{{ $variant['id'] }}][size]" value="{{ $variant['size'] }}">
                                </label>
                                <label>{{ $variant['sku'] }} <input name="variants[{{ $variant['id'] }}][stock]" type="number" min="0" value="{{ $variant['stock'] }}"></label>
                            @endforeach
                        </section>
                        <section>
                            <h2>Display Rule</h2>
                            <p>Women's clothing can show colour and size. Health mix, masala powders and electronics can use only stock or colour when needed.</p>
                        </section>
                    </div>
                    <div class="buy-actions">
                        <button class="button button--primary" type="submit">Save Product</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.products.destroy', $product['slug']) }}">
                    @csrf
                    @method('DELETE')
                    <button class="button button--ghost" type="submit">Delete Product</button>
                </form>
                @foreach ($product['images'] as $image)
                    @if ($image['id'])
                        <form id="delete-product-image-{{ $image['id'] }}" method="POST" action="{{ route('admin.product-images.destroy', $image['id']) }}">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                @endforeach
            </section>
    </x-admin.shell>
</x-layouts.admin>
