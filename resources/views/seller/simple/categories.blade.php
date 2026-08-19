<x-layouts.seller title="Categories">
    <x-seller.header title="Categories" subtitle="Select at least one product category before publishing your store." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <form class="seller-form-card seller-form-card--sectioned" method="POST" action="{{ route('seller.categories.update') }}">
            @csrf
            @method('PUT')
            <h2>Product Categories</h2>
            <div class="seller-checkbox-grid seller-field--wide">
                @foreach ($categories as $category)
                    <label><input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, old('categories', $vendor->categories->pluck('id')->all()), true))> {{ $category->name }}</label>
                @endforeach
            </div>
            <div class="seller-form-actions"><button type="submit">Save Categories</button></div>
        </form>

        <section class="seller-card-grid">
            @forelse ($vendor->categories as $category)
                <article class="seller-metric-card"><span>{{ $category->name }}</span><strong>{{ $vendor->products()->where('category_id', $category->id)->count() }}</strong></article>
            @empty
                <article class="seller-panel seller-panel--wide"><h2>No categories selected</h2><p>Choose at least one category to complete publish readiness.</p></article>
            @endforelse
        </section>
    </section>
</x-layouts.seller>
