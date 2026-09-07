<x-layouts.admin title="Product Collections - Admin">
    <x-admin.shell eyebrow="Marketing" title="Product Collections" subtitle="Create reusable groups from real marketplace products without duplicating product data.">
        <x-slot:actions><x-admin.action :href="route('admin.content.index')" tone="secondary" icon="fa-solid fa-wand-magic-sparkles">Content Creator</x-admin.action></x-slot:actions>
        <section class="admin-dashboard-panel">
            @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
            <form class="admin-edit-form" method="POST" action="{{ route('admin.content.collections.store') }}">@csrf
                <label>Name<input name="name" required maxlength="160"></label><label>Slug<input name="slug" maxlength="180"></label>
                <label>Category<select name="category_id"><option value="">Any category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>@endforeach</select></label>
                <label>Display Order<input type="number" name="display_order" min="0" value="0"></label>
                <label class="admin-field-wide">Description<textarea name="description" maxlength="2000"></textarea></label>
                <label class="admin-field-wide">Products<select name="product_ids[]" multiple size="8">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}{{ $product->vendor ? ' - '.$product->vendor->business_name : '' }}</option>@endforeach</select><small>Hold Ctrl or Command to select multiple products.</small></label>
                <label class="admin-checkbox"><input type="checkbox" name="is_active" value="1" checked> Active</label><div class="admin-form-actions admin-field-wide"><button class="button button--primary" type="submit">Create Collection</button></div>
            </form>
            <div class="admin-request-list">
                @forelse($collections as $collection)
                    <details class="admin-request-card"><summary><span><strong>{{ $collection->name }}</strong><small>{{ $collection->products->count() }} products</small></span><span class="admin-status-badge {{ $collection->is_active ? 'is-active' : 'is-inactive' }}">{{ $collection->is_active ? 'Active' : 'Inactive' }}</span></summary>
                        <form class="admin-edit-form" method="POST" action="{{ route('admin.content.collections.update', $collection) }}">@csrf @method('PUT')
                            <label>Name<input name="name" value="{{ $collection->name }}" required maxlength="160"></label><label>Slug<input name="slug" value="{{ $collection->slug }}" maxlength="180"></label>
                            <label>Category<select name="category_id"><option value="">Any category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($collection->category_id === $category->id)>{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>@endforeach</select></label><label>Display Order<input type="number" name="display_order" min="0" value="{{ $collection->display_order }}"></label>
                            <label class="admin-field-wide">Description<textarea name="description" maxlength="2000">{{ $collection->description }}</textarea></label><label class="admin-field-wide">Products<select name="product_ids[]" multiple size="8">@foreach($products as $product)<option value="{{ $product->id }}" @selected($collection->products->contains($product))>{{ $product->name }}{{ $product->vendor ? ' - '.$product->vendor->business_name : '' }}</option>@endforeach</select></label>
                            <label class="admin-checkbox"><input type="checkbox" name="is_active" value="1" @checked($collection->is_active)> Active</label><button class="button button--primary" type="submit">Save Collection</button>
                        </form>
                    </details>
                @empty
                    <x-ui.empty-state title="No product collections" description="Collections can be used by homepage content when real products are ready to feature." />
                @endforelse
            </div>
            {{ $collections->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
