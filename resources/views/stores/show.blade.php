<x-layouts.customer title="{{ $vendor->store_display_name ?: $vendor->business_name }} - Sushako Store">
    <section class="seller-storefront-hero">
        @if ($vendor->store_banner_path)
            <img src="{{ asset('storage/'.$vendor->store_banner_path) }}" alt="{{ $vendor->store_display_name ?: $vendor->business_name }} store banner" loading="eager" decoding="async">
        @endif
        <div class="site-shell seller-storefront-hero__inner">
            <div class="seller-storefront-identity">
                <span class="seller-storefront-identity__logo">
                    @if ($vendor->business_logo_path)
                        <img src="{{ asset('storage/'.$vendor->business_logo_path) }}" alt="{{ $vendor->store_display_name ?: $vendor->business_name }} logo">
                    @else
                        {{ str($vendor->store_display_name ?: $vendor->business_name)->substr(0, 1)->upper() }}
                    @endif
                </span>
                <div>
                    <nav class="breadcrumbs" aria-label="Breadcrumb">
                        <a href="{{ route('home') }}">Home</a>
                        <span>/</span>
                        <a href="{{ route('shop') }}">Stores</a>
                        <span>/</span>
                        <strong>{{ $vendor->store_display_name ?: $vendor->business_name }}</strong>
                    </nav>
                    <h1>{{ $vendor->store_display_name ?: $vendor->business_name }}</h1>
                    <p>{{ $vendor->store_description ?: 'A verified Sushako marketplace store.' }}</p>
                    <div class="seller-storefront-badges">
                        @if ($isOfficial)
                            <span><i class="fa-solid fa-certificate" aria-hidden="true"></i> Official Store</span>
                        @endif
                        @if ($vendor->store_status === \App\Models\Vendor::STORE_LIVE)
                            <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified Seller</span>
                        @endif
                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $vendor->city ?: 'India' }}</span>
                        <span><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Joined {{ $vendor->created_at?->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="site-shell seller-storefront-content">
        <aside class="seller-storefront-panel">
            <h2>Store Categories</h2>
            <div class="seller-storefront-category-list">
                @forelse ($vendor->storefrontCategories as $category)
                    <a href="#products">{{ $category->name }}</a>
                @empty
                    <p>Categories will appear as this seller organizes their catalog.</p>
                @endforelse
            </div>
            <h2>Store Policies</h2>
            <p>Orders are fulfilled by the seller through Sushako marketplace standards. Customer support and invoices remain available through Sushako.</p>
        </aside>

        <div class="seller-storefront-products" id="products">
            <div class="section-heading section-heading--editorial">
                <div>
                    <p class="eyebrow">All Products</p>
                    <h2>Browse {{ $vendor->store_display_name ?: $vendor->business_name }}</h2>
                </div>
                <a href="{{ route('shop') }}">All Marketplace Products</a>
            </div>

            @if ($products->count())
                <div class="product-grid products-grid">
                    @foreach ($products as $product)
                        <x-product.card :product="$product" compact />
                    @endforeach
                </div>
                @if ($products->hasPages())
                    <nav class="products-pagination" aria-label="Seller product pagination">
                        {{ $products->links() }}
                    </nav>
                @endif
            @else
                <div class="empty-cart products-no-results">
                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                    <h2>No products are published yet</h2>
                    <p class="lede">This store is preparing its catalog. Explore other Sushako marketplace products meanwhile.</p>
                    <a class="button button--primary" href="{{ route('shop') }}">Explore Products</a>
                </div>
            @endif
        </div>
    </section>
</x-layouts.customer>
