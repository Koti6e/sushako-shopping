<x-layouts.seller title="Products">
    <x-seller.header title="Products" subtitle="Manage your seller catalog, stock, and listing status." :vendor="$vendor" />

    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif

        <form class="seller-product-filters" method="GET" action="{{ route('seller.products.index') }}">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products">
            <select name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="stock">
                <option value="">All stock</option>
                <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
            </select>
            <select name="status">
                <option value="">All status</option>
                <option value="approved" @selected(request('status') === 'approved')>Active</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="pending_review" @selected(request('status') === 'pending_review')>Pending Review</option>
            </select>
            <button type="submit">Filter</button>
            <a href="{{ route('seller.products.create') }}">Add Product</a>
        </form>

        <div class="seller-table-card seller-products-table-card">
            @forelse ($products->getCollection()->groupBy(fn ($product) => $product->sellerStorefrontCategory?->name ?: ($product->category?->name ?: 'Uncategorised')) as $categoryName => $categoryProducts)
                <details class="seller-product-category-group" open>
                    <summary>{{ $categoryName }} — {{ $categoryProducts->count() }} Product{{ $categoryProducts->count() === 1 ? '' : 's' }}</summary>
                    @foreach ($categoryProducts as $product)
                        @php $image = $product->images->first(); @endphp
                        <a class="seller-product-row seller-product-row--rich" href="{{ route('seller.products.edit', $product) }}">
                            <span class="seller-product-thumb">
                                <x-product.image :src="$image?->url()" :alt="$product->name" fallback-class="seller-product-no-preview" />
                            </span>
                            <strong>{{ $product->name }}</strong>
                            <span>Rs {{ number_format($product->selling_price) }}</span>
                            <span>{{ $product->variants->sum('stock') }} in stock</span>
                            <span>{{ str($product->seller_status)->replace('_', ' ')->title() }}</span>
                            <span>{{ number_format((int) $product->seller_product_views) }} views</span>
                            <b>Edit</b>
                        </a>
                    @endforeach
                </details>
            @empty
                <div class="seller-empty-state seller-empty-state--compact">
                    <strong>No products yet</strong>
                    <p>Add your first product to start selling on Sushako.</p>
                    <a href="{{ route('seller.products.create') }}">Add Product</a>
                </div>
            @endforelse
        </div>

        {{ $products->links() }}
    </section>
</x-layouts.seller>
