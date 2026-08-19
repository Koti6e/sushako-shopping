@php
    $variant = $product->variants->first();
    $isEdit = $product->exists;
    $selectedStatus = old('seller_status', $product->seller_status ?: \App\Models\Product::SELLER_STATUS_ACTIVE);
    $goLiveMode = old('go_live_mode', $product->seller_status === \App\Models\Product::SELLER_STATUS_SCHEDULED ? 'schedule' : 'publish_now');
@endphp

<x-layouts.seller title="{{ $isEdit ? 'Edit Product' : 'Add Product' }}">
    <x-seller.header :title="$isEdit ? 'Edit Product' : 'Add Product'" subtitle="Add the details customers need to buy from your shop." :vendor="$vendor" />

    <section class="seller-content seller-product-editor">
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif

        <form class="seller-product-form seller-product-form--simple" method="POST" action="{{ $isEdit ? route('seller.products.update', $product) : route('seller.products.store') }}" enctype="multipart/form-data" data-seller-product-form>
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <input type="hidden" name="product_condition" value="{{ old('product_condition', $product->product_condition ?: 'new') }}">
            <input type="hidden" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 2) }}">
            <input type="hidden" name="package_contents" value="{{ old('package_contents', $product->package_contents ?: 'Product package') }}">

            <section class="seller-product-form__section">
                <div class="seller-product-form__section-head">
                    <div>
                        <span>Product</span>
                        <h2>Basic Details</h2>
                    </div>
                    <p>Use simple names, clear prices, and one Sushako category.</p>
                </div>

                <div class="seller-product-form__grid">
                    <label class="seller-product-field">
                        <span>Product Name <b>*</b></span>
                        <input name="name" value="{{ old('name', $product->name) }}" required autocomplete="off">
                        @error('name')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>Category <b>*</b></span>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    @if ($storefrontCategories->isNotEmpty())
                        <label class="seller-product-field">
                            <span>Subcategory <small>Optional</small></span>
                            <select name="seller_storefront_category_id">
                                <option value="">No subcategory</option>
                                @foreach ($storefrontCategories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('seller_storefront_category_id', $product->seller_storefront_category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('seller_storefront_category_id')<small class="seller-field-error">{{ $message }}</small>@enderror
                        </label>
                    @endif

                    <label class="seller-product-field">
                        <span>Brand <small>Optional</small></span>
                        <input name="brand" value="{{ old('brand', $product->brand ?: $vendor->store_display_name) }}" autocomplete="organization">
                        @error('brand')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field seller-product-field--full">
                        <span>Description <b>*</b></span>
                        <textarea name="short_description" required>{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field seller-product-field--full">
                        <span>More Description <small>Optional</small></span>
                        <textarea name="full_description">{{ old('full_description', $product->full_description) }}</textarea>
                        @error('full_description')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>
                </div>
            </section>

            <section class="seller-product-form__section">
                <div class="seller-product-form__section-head">
                    <div>
                        <span>Price</span>
                        <h2>Price and Stock</h2>
                    </div>
                    <p>Offer price is what customers pay. Regular price is optional.</p>
                </div>

                <div class="seller-product-form__grid">
                    <label class="seller-product-field">
                        <span>Offer Price <b>*</b></span>
                        <input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="1" required inputmode="numeric" data-price-input>
                        @error('selling_price')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>Buying Price <b>*</b></span>
                        <input type="number" name="buying_price" value="{{ old('buying_price', $product->buying_price) }}" min="0" required inputmode="numeric" data-price-input>
                        @error('buying_price')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>Regular Price <small>Optional</small></span>
                        <input type="number" name="mrp" value="{{ old('mrp', $product->mrp) }}" min="1" inputmode="numeric">
                        @error('mrp')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>Stock <b>*</b></span>
                        <input type="number" name="stock" value="{{ old('stock', $variant?->stock ?? 0) }}" min="0" required inputmode="numeric">
                        @error('stock')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>SKU <small>Optional</small></span>
                        <input name="sku" value="{{ old('sku', $variant?->sku) }}" autocomplete="off">
                        @error('sku')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>Status <b>*</b></span>
                        <select name="seller_status" required>
                            <option value="approved" @selected($selectedStatus === 'approved')>Active</option>
                            <option value="draft" @selected($selectedStatus === 'draft')>Draft</option>
                            <option value="inactive" @selected($selectedStatus === 'inactive')>Inactive</option>
                        </select>
                        @error('seller_status')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field">
                        <span>Go Live <b>*</b></span>
                        <select name="go_live_mode" required data-go-live-mode>
                            <option value="publish_now" @selected($goLiveMode === 'publish_now')>Publish Now</option>
                            <option value="schedule" @selected($goLiveMode === 'schedule')>Schedule</option>
                        </select>
                        @error('go_live_mode')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <label class="seller-product-field" data-go-live-at-field @if ($goLiveMode !== 'schedule') hidden @endif>
                        <span>Go Live Date & Time</span>
                        <input type="datetime-local" name="go_live_at" value="{{ old('go_live_at', $product->scheduled_go_live_at?->format('Y-m-d\\TH:i')) }}">
                        @error('go_live_at')<small class="seller-field-error">{{ $message }}</small>@enderror
                    </label>

                    <div class="seller-product-field seller-product-field--full seller-product-economics" data-economics-preview>
                        <span>Seller Economics</span>
                        <p>Revenue per Sale: <strong data-econ-revenue>Rs 0</strong> · Product Cost: <strong data-econ-cost>Rs 0</strong> · Sushako Commission: <strong data-econ-commission>Rs 1</strong></p>
                        <p>Seller Receives: <strong data-econ-receives>Rs 0</strong> · Estimated Profit: <strong data-econ-profit>Rs 0</strong> · Profit Margin: <strong data-econ-margin>0%</strong></p>
                        <small>Free Plan uses server-side Rs 1 commission per item. COD has no Razorpay online payment charge.</small>
                    </div>

                    <label class="seller-product-toggle seller-product-field--full">
                        <input type="checkbox" name="continue_selling_when_out_of_stock" value="1" @checked(old('continue_selling_when_out_of_stock', $product->continue_selling_when_out_of_stock))>
                        <span class="seller-product-toggle__switch" aria-hidden="true"></span>
                        <span class="seller-product-toggle__copy">
                            <strong>Continue selling after stock reaches zero</strong>
                            <small>Use this only if you can quickly restock.</small>
                        </span>
                    </label>
                </div>
            </section>

            <section class="seller-product-form__section">
                <div class="seller-product-form__section-head">
                    <div>
                        <span>Images</span>
                        <h2>Product Images</h2>
                    </div>
                    <p>Upload clear product photos. The first image becomes the main image.</p>
                </div>

                <div class="seller-product-upload" data-product-upload>
                    <input id="seller-product-images" class="seller-product-upload__input" type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple data-product-upload-input>
                    <label class="seller-product-upload__dropzone" for="seller-product-images" tabindex="0" data-product-upload-dropzone>
                        <span class="seller-product-upload__icon"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
                        <strong>Choose product images</strong>
                        <em>PNG, JPG or WEBP. Maximum 4 images, 4 MB each. The first image becomes primary.</em>
                    </label>

                    @if ($product->images->isNotEmpty())
                        <div class="seller-product-upload__existing" aria-label="Current product images">
                            @foreach ($product->images as $image)
                                <figure>
                                    <img src="{{ asset(str_starts_with($image->path, 'assets/') ? $image->path : 'storage/'.$image->path) }}" alt="{{ $image->label ?: $product->name }}" loading="lazy">
                                    <figcaption>{{ $loop->first ? 'Current main image' : 'Product image' }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif

                    <div class="seller-product-upload__list" data-product-upload-list hidden></div>
                    @error('images')<small class="seller-field-error">{{ $message }}</small>@enderror
                    @error('images.*')<small class="seller-field-error">{{ $message }}</small>@enderror
                </div>
            </section>

            <div class="seller-product-form__actions">
                <a href="{{ route('seller.products.index') }}">Back</a>
                <button type="submit" data-product-submit><span>{{ $isEdit ? 'Update Product' : 'Save Product' }}</span></button>
            </div>
        </form>

        <aside class="seller-category-request-card">
            <div>
                <h2>Can't find your category?</h2>
                <p>Request a new Sushako marketplace category. It will be reviewed before it appears for sellers.</p>
            </div>
            <form method="POST" action="{{ route('seller.category-requests.store') }}">
                @csrf
                <input name="requested_category_name" placeholder="Requested category name" required maxlength="120">
                <textarea name="description" placeholder="Example products or notes" maxlength="1000"></textarea>
                <button type="submit">Request New Category</button>
            </form>
        </aside>
    </section>

    <script>
        (() => {
            const root = document.querySelector('[data-seller-product-form]');
            if (!root) return;
            const input = root.querySelector('[data-product-upload-input]');
            const list = root.querySelector('[data-product-upload-list]');
            const submit = root.querySelector('[data-product-submit]');
            const money = (value) => `Rs ${Number(value || 0).toLocaleString('en-IN')}`;
            const syncEconomics = () => {
                const selling = Number(root.querySelector('[name="selling_price"]')?.value || 0);
                const cost = Number(root.querySelector('[name="buying_price"]')?.value || 0);
                const commission = selling > 0 ? 1 : 0;
                const receives = Math.max(0, selling - commission);
                const profit = receives - cost;
                const margin = selling > 0 ? Math.round((profit / selling) * 100) : 0;
                root.querySelector('[data-econ-revenue]').textContent = money(selling);
                root.querySelector('[data-econ-cost]').textContent = money(cost);
                root.querySelector('[data-econ-commission]').textContent = money(commission);
                root.querySelector('[data-econ-receives]').textContent = money(receives);
                root.querySelector('[data-econ-profit]').textContent = money(profit);
                root.querySelector('[data-econ-margin]').textContent = `${margin}%`;
            };
            const syncSchedule = () => {
                const scheduled = root.querySelector('[data-go-live-mode]')?.value === 'schedule';
                const field = root.querySelector('[data-go-live-at-field]');
                if (field) field.hidden = !scheduled;
            };

            const renderFiles = () => {
                if (!list || !input) return;
                list.innerHTML = '';
                list.hidden = input.files.length === 0;
                if (input.files.length > 4) {
                    input.value = '';
                    list.hidden = true;
                    list.innerHTML = '<small class="seller-field-error">Upload a maximum of 4 product images.</small>';
                    list.hidden = false;
                    return;
                }
                Array.from(input.files).forEach((file, index) => {
                    const item = document.createElement('article');
                    item.className = 'seller-product-upload__item';
                    const image = document.createElement('img');
                    image.alt = '';
                    image.src = URL.createObjectURL(file);
                    image.onload = () => URL.revokeObjectURL(image.src);
                    const meta = document.createElement('span');
                    meta.innerHTML = `<strong>${file.name.replaceAll('<', '&lt;')}</strong><small>${index === 0 ? 'Main image' : 'Product image'}</small>`;
                    item.append(image, meta);
                    list.append(item);
                });
            };

            input?.addEventListener('change', renderFiles);
            root.querySelectorAll('[data-price-input]').forEach((field) => field.addEventListener('input', syncEconomics));
            root.querySelector('[data-go-live-mode]')?.addEventListener('change', syncSchedule);
            root.addEventListener('submit', () => {
                if (submit) {
                    submit.disabled = true;
                    submit.textContent = 'Saving...';
                }
            });
            syncEconomics();
            syncSchedule();
        })();
    </script>
</x-layouts.seller>
