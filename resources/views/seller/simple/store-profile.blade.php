<x-layouts.seller title="Store Profile">
    <x-seller.header title="Store Profile" subtitle="Update business identity, storefront details, and legal basics." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <form class="seller-form-card seller-form-card--sectioned" method="POST" action="{{ route('seller.store-profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <h2>Business Profile</h2>
            <label>Business name<input name="business_name" value="{{ old('business_name', $vendor->business_name) }}" required><small>{{ max(0, 3 - (int) $vendor->business_name_edit_count) }} edits remaining</small></label>
            <x-ui.file-upload class="seller-field--wide" name="logo" label="Store logo" hint="PNG, JPG or WebP logo customers will recognise." accept="image/*" />
            <label>Store display name<input name="store_display_name" value="{{ old('store_display_name', $vendor->store_display_name) }}" required></label>
            <label>Owner name<input name="owner_name" value="{{ old('owner_name', $vendor->owner_name) }}" required></label>
            <label>Seller category
                <select name="business_category" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->name }}" @selected(old('business_category', $vendor->business_category) === $category->name)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Email<input type="email" name="email" value="{{ old('email', $vendor->email) }}" required></label>
            <label>Mobile<input name="phone" value="{{ old('phone', $vendor->phone) }}" required></label>
            <label>Legal business name<input name="legal_name" value="{{ old('legal_name', $vendor->legal_name) }}"></label>
            <label>Website<input type="url" name="website" value="{{ old('website', $vendor->website) }}" placeholder="https://example.com"></label>
            <label class="seller-field--wide">Store description<textarea name="store_description" required>{{ old('store_description', $vendor->store_description) }}</textarea></label>
            <label>Store tagline<input name="store_tagline" value="{{ old('store_tagline', $vendor->store_tagline) }}"></label>
            <label>GST status<select name="gst_status"><option value="">Select status</option>@foreach (['registered' => 'GST Registered', 'not_registered' => 'Not Registered', 'applied' => 'Applied', 'not_applicable' => 'Not Applicable'] as $value => $label)<option value="{{ $value }}" @selected(old('gst_status', $vendor->gst_status) === $value)>{{ $label }}</option>@endforeach</select></label>
            <label>GSTIN<input name="gstin" value="{{ old('gstin', $vendor->gstin) }}"></label>
            <label>PAN<input name="pan_number" value="{{ old('pan_number', $vendor->pan_number) }}"></label>
            <div class="seller-form-actions"><button type="submit">Save Store Profile</button></div>
        </form>
    </section>
</x-layouts.seller>
