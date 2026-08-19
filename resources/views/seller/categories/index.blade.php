@php
    $statusOptions = [
        \App\Models\SellerStorefrontCategory::STATUS_ACTIVE => 'Active',
        \App\Models\SellerStorefrontCategory::STATUS_DRAFT => 'Draft',
        \App\Models\SellerStorefrontCategory::STATUS_HIDDEN => 'Inactive',
    ];
    $selectedMarketplaceIds = old('categories', $vendor->categories->pluck('id')->all());
@endphp

<x-layouts.seller title="Categories">
    <x-seller.header title="Categories" subtitle="Create storefront categories and connect them to marketplace discovery." :vendor="$vendor" />

    <section class="seller-content seller-category-workspace seller-category-workspace--production">
        <nav class="seller-breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('seller.dashboard') }}">Dashboard</a>
            <span>/</span>
            <strong>Inventory</strong>
            <span>/</span>
            <strong>Categories</strong>
        </nav>

        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <section class="seller-category-section seller-category-section--primary">
            <div class="seller-category-section__head">
                <div>
                    <p class="eyebrow">Storefront Categories</p>
                    <h2>Create and organize categories for your store and products.</h2>
                    <small>Seller categories belong only to your store. Marketplace mapping is optional.</small>
                </div>
                <a class="seller-category-create-link" href="#create-category"><i class="fa-solid fa-plus" aria-hidden="true"></i>Create Category</a>
            </div>

            <details id="create-category" class="seller-category-editor" @if($errors->any() && old('name')) open @endif>
                <summary><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Create Category</summary>
                <form method="POST" action="{{ route('seller.categories.subcategories.store') }}" enctype="multipart/form-data" data-seller-submit>
                    @csrf
                    <label>Category Name<input name="name" value="{{ old('name') }}" required maxlength="120" data-slug-source="#create-category-slug"></label>
                    <label>Slug<input id="create-category-slug" name="slug" value="{{ old('slug') }}" maxlength="140" placeholder="auto-generated-if-empty"></label>
                    <label>Parent Marketplace Category
                        <select name="category_id">
                            <option value="">No marketplace mapping</option>
                            @foreach ($marketplaceCategories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Status
                        <select name="status" required>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', \App\Models\SellerStorefrontCategory::STATUS_ACTIVE) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Display Order<input type="number" min="0" max="9999" name="display_order" value="{{ old('display_order', 0) }}"></label>
                    <label class="seller-field--wide">Description<textarea name="description">{{ old('description') }}</textarea></label>
                    <label class="seller-upload-control seller-field--wide">
                        <span>Category Image/Icon</span>
                        <input class="seller-upload-control__input" type="file" name="image" accept="image/*">
                        <span class="seller-upload-control__button"><i class="fa-solid fa-image" aria-hidden="true"></i> Browse Image</span>
                        <small data-upload-name>No image selected. JPG, PNG, WebP or SVG up to 2 MB.</small>
                    </label>
                    <div class="seller-form-actions seller-field--wide">
                        <a href="{{ route('seller.categories.index') }}">Cancel</a>
                        <button type="submit">Create Category</button>
                    </div>
                </form>
            </details>

            <form class="seller-category-filters seller-category-filters--production" method="GET" action="{{ route('seller.categories.index') }}">
                <input name="category_search" value="{{ $filters['category_search'] ?? '' }}" placeholder="Search storefront categories">
                <select name="category_status">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['category_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Filter</button>
            </form>

            <div class="seller-category-table" role="table" aria-label="Storefront categories">
                <div class="seller-category-table__row seller-category-table__row--head" role="row">
                    <span role="columnheader">Category</span>
                    <span role="columnheader">Marketplace Category</span>
                    <span role="columnheader">Products</span>
                    <span role="columnheader">Status</span>
                    <span role="columnheader">Display Order</span>
                    <span role="columnheader">Actions</span>
                </div>
                @forelse ($storefrontCategories as $category)
                    <article class="seller-category-table__row" role="row">
                        <div class="seller-category-name" role="cell">
                            <span class="seller-category-name__icon">
                                @if ($category->image_path)
                                    <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}">
                                @else
                                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                                @endif
                            </span>
                            <span>
                                <strong>{{ $category->name }}</strong>
                                <small>{{ $category->slug }}</small>
                            </span>
                        </div>
                        <span role="cell">{{ $category->marketplaceCategory?->name ?: 'Not mapped' }}</span>
                        <span role="cell">{{ $category->products_count }}</span>
                        <span role="cell"><b class="seller-category-status seller-category-status--{{ $category->status }}">{{ $statusOptions[$category->status] ?? str($category->status)->replace('_', ' ')->title() }}</b></span>
                        <span role="cell">{{ $category->display_order }}</span>
                        <div class="seller-category-actions" role="cell">
                            <details>
                                <summary>Edit</summary>
                                <form method="POST" action="{{ route('seller.categories.subcategories.update', $category) }}" enctype="multipart/form-data" data-seller-submit>
                                    @csrf
                                    @method('PUT')
                                    <label>Name<input name="name" value="{{ $category->name }}" required maxlength="120"></label>
                                    <label>Slug<input name="slug" value="{{ $category->slug }}" maxlength="140"></label>
                                    <label>Marketplace
                                        <select name="category_id">
                                            <option value="">No marketplace mapping</option>
                                            @foreach ($marketplaceCategories as $market)
                                                <option value="{{ $market->id }}" @selected($category->category_id === $market->id)>{{ $market->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>Status
                                        <select name="status" required>
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($category->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>Display Order<input type="number" min="0" max="9999" name="display_order" value="{{ $category->display_order }}"></label>
                                    <label>Description<textarea name="description">{{ $category->description }}</textarea></label>
                                    <label class="seller-upload-control">
                                        <span>Replace Image</span>
                                        <input class="seller-upload-control__input" type="file" name="image" accept="image/*">
                                        <span class="seller-upload-control__button"><i class="fa-solid fa-upload" aria-hidden="true"></i> Browse Image</span>
                                        <small data-upload-name>Keep current image or upload a new one.</small>
                                    </label>
                                    <button type="submit">Save Changes</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('seller.categories.subcategories.update', $category) }}" data-seller-submit>
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $category->name }}">
                                <input type="hidden" name="slug" value="{{ $category->slug }}">
                                <input type="hidden" name="category_id" value="{{ $category->category_id }}">
                                <input type="hidden" name="display_order" value="{{ $category->display_order }}">
                                <input type="hidden" name="description" value="{{ $category->description }}">
                                <input type="hidden" name="status" value="{{ $category->status === \App\Models\SellerStorefrontCategory::STATUS_ACTIVE ? \App\Models\SellerStorefrontCategory::STATUS_HIDDEN : \App\Models\SellerStorefrontCategory::STATUS_ACTIVE }}">
                                <button type="submit">{{ $category->status === \App\Models\SellerStorefrontCategory::STATUS_ACTIVE ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('seller.categories.subcategories.destroy', $category) }}" data-seller-submit>
                                @csrf
                                @method('DELETE')
                                <button type="submit" @disabled($category->products_count > 0) title="{{ $category->products_count > 0 ? 'Reassign '.$category->products_count.' product(s) before deleting.' : 'Delete category' }}">Delete</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="seller-empty-state">
                        <strong>No storefront categories yet.</strong>
                        <p>Create your first category to organize products inside your public seller store.</p>
                    </div>
                @endforelse
            </div>

            {{ $storefrontCategories->links() }}
        </section>

        <form class="seller-category-section seller-category-section--secondary" method="POST" action="{{ route('seller.categories.update') }}" data-seller-submit data-marketplace-mapping-form>
            @csrf
            @method('PUT')
            <div class="seller-category-section__head">
                <div>
                    <p class="eyebrow">Marketplace Categories</p>
                    <h2>Connect your store to Sushako marketplace discovery.</h2>
                    <small>Optional master-category mapping improves navigation, search filters and future SEO pages.</small>
                </div>
                <button class="seller-category-save-mapping" type="submit" data-marketplace-save hidden>Save Mapping</button>
            </div>

            <div class="seller-marketplace-list">
                @forelse ($marketplaceCategories as $category)
                    <label>
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedMarketplaceIds, true)) data-marketplace-checkbox>
                        <span>
                            <strong>{{ $category->name }}</strong>
                            <small>{{ $category->description ?: $category->tagline ?: 'Marketplace discovery category' }}</small>
                        </span>
                        <em>{{ (int) ($marketplaceMappingCounts[$category->id] ?? 0) }} mapped</em>
                    </label>
                @empty
                    <p class="seller-empty-note">No marketplace categories are available for sellers yet.</p>
                @endforelse
            </div>
        </form>
    </section>

    <script>
        (() => {
            const slugify = (value) => value.toString().toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

            document.querySelectorAll('[data-slug-source]').forEach((input) => {
                input.addEventListener('input', () => {
                    const target = document.querySelector(input.dataset.slugSource);
                    if (target && !target.dataset.touched) target.value = slugify(input.value);
                });
            });

            document.querySelectorAll('input[name="slug"]').forEach((input) => {
                input.addEventListener('input', () => input.dataset.touched = '1');
            });

            document.querySelectorAll('[data-seller-submit]').forEach((form) => {
                form.addEventListener('submit', () => {
                    const button = form.querySelector('button[type="submit"]');
                    if (!button) return;
                    button.disabled = true;
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Saving...';
                });
            });

            document.querySelectorAll('.seller-upload-control__input').forEach((input) => {
                input.addEventListener('change', () => {
                    const name = input.closest('.seller-upload-control')?.querySelector('[data-upload-name]');
                    if (name) name.textContent = input.files?.[0]?.name || 'No image selected.';
                });
            });

            const mappingForm = document.querySelector('[data-marketplace-mapping-form]');
            if (mappingForm) {
                const button = mappingForm.querySelector('[data-marketplace-save]');
                const initial = Array.from(mappingForm.querySelectorAll('[data-marketplace-checkbox]')).map((input) => `${input.value}:${input.checked}`).join('|');
                mappingForm.addEventListener('change', () => {
                    const current = Array.from(mappingForm.querySelectorAll('[data-marketplace-checkbox]')).map((input) => `${input.value}:${input.checked}`).join('|');
                    if (button) button.hidden = current === initial;
                });
            }
        })();
    </script>
</x-layouts.seller>
