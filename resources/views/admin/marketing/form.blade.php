@php
    $isEdit = $content->exists;
    $active = old('is_active', $content->is_active ?? true);
@endphp

<x-layouts.admin title="{{ $isEdit ? 'Edit Content' : 'Create Content' }} - Admin">
    <x-admin.shell eyebrow="Marketing" title="{{ $isEdit ? 'Edit Content' : 'Create Content' }}" subtitle="Use only real marketplace destinations and content that is ready to publish.">
        <x-slot:actions><x-admin.action :href="route('admin.content.index')" tone="secondary" icon="fa-solid fa-arrow-left">Content Creator</x-admin.action></x-slot:actions>
        <section class="admin-dashboard-panel">
            @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
            <form class="admin-edit-form admin-marketing-form" method="POST" action="{{ $isEdit ? route('admin.content.update', $content) : route('admin.content.store') }}" enctype="multipart/form-data">
                @csrf @if($isEdit) @method('PUT') @endif
                <label>Content Type<select name="type" required>@foreach($contentTypes as $value => $label)<option value="{{ $value }}" @selected(old('type', $content->type) === $value)>{{ $label }}</option>@endforeach</select></label>
                <label>Homepage Placement<select name="placement" required>@foreach($placements as $value => $label)<option value="{{ $value }}" @selected(old('placement', $content->placement) === $value)>{{ $label }}</option>@endforeach</select></label>
                <label>Title<input name="title" value="{{ old('title', $content->title) }}" required maxlength="160"></label>
                <label>Slug<input name="slug" value="{{ old('slug', $content->slug) }}" maxlength="180"><small>Leave blank to create a URL-safe identifier.</small></label>
                <label class="admin-field-wide">Subtitle<input name="subtitle" value="{{ old('subtitle', $content->subtitle) }}" maxlength="240"></label>
                <label class="admin-field-wide">Description<textarea name="description" maxlength="2000">{{ old('description', $content->description) }}</textarea></label>
                <label>CTA Text<input name="cta_label" value="{{ old('cta_label', $content->cta_label) }}" maxlength="80"></label>
                <label>Destination<select name="destination_type" required>@foreach($destinationTypes as $value => $label)<option value="{{ $value }}" @selected(old('destination_type', $content->destination_type) === $value)>{{ $label }}</option>@endforeach</select></label>
                <label>Category<select name="category_id"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $content->category_id) === (string) $category->id)>{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>@endforeach</select></label>
                <label>Product<select name="product_id"><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string) old('product_id', $content->product_id) === (string) $product->id)>{{ $product->name }}{{ $product->vendor ? ' - '.$product->vendor->business_name : '' }}</option>@endforeach</select></label>
                <label>Product Collection<select name="product_collection_id"><option value="">Select collection</option>@foreach($collections as $collection)<option value="{{ $collection->id }}" @selected((string) old('product_collection_id', $content->product_collection_id) === (string) $collection->id)>{{ $collection->name }}</option>@endforeach</select></label>
                <label>Custom URL<input name="destination_url" value="{{ old('destination_url', $content->destination_url) }}" maxlength="500"><small>Only used when Custom URL is selected.</small></label>
                <x-ui.file-upload name="image" label="Desktop image" hint="Optional. Leave blank for the intentional no-image presentation." accept="image/jpeg,image/png,image/webp" />
                <x-ui.file-upload name="mobile_image" label="Mobile image" hint="Optional. JPG, PNG or WebP up to 4 MB." accept="image/jpeg,image/png,image/webp" />
                <label>Display Order<input type="number" name="display_order" min="0" max="9999" value="{{ old('display_order', $content->display_order ?? 0) }}"></label>
                <label>Start Date<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $content->starts_at?->format('Y-m-d\\TH:i')) }}"></label>
                <label>End Date<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $content->ends_at?->format('Y-m-d\\TH:i')) }}"></label>
                <label class="admin-checkbox"><input type="checkbox" name="is_active" value="1" @checked($active)> Active</label>
                <div class="admin-form-actions admin-field-wide"><a class="button button--ghost" href="{{ route('admin.content.index') }}">Cancel</a><button class="button button--primary" type="submit">{{ $isEdit ? 'Save Content' : 'Create Content' }}</button></div>
            </form>
            @if($isEdit)<form class="admin-danger-zone" method="POST" action="{{ route('admin.content.destroy', $content) }}" onsubmit="return confirm('Delete this content item? This cannot be undone.');">@csrf @method('DELETE')<button class="button button--ghost" type="submit">Delete Content</button></form>@endif
        </section>
    </x-admin.shell>
</x-layouts.admin>
