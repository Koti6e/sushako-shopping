<x-layouts.admin title="Products - Admin">
    <x-admin.shell eyebrow="Catalog" title="Marketplace Products" subtitle="Review every seller listing, pricing, stock, and listing status from one catalog.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.categories.create')" tone="secondary" icon="fa-solid fa-layer-group">
                Add Category
            </x-admin.action>

            <x-admin.action :href="route('admin.products.create')" tone="primary" icon="fa-solid fa-plus">
                Add Product
            </x-admin.action>
        </x-slot:actions>

        <section class="admin-dashboard-panel">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            <p class="eyebrow">Product Management</p>
            <h1>Marketplace products</h1>
            <p class="lede">
                Review products from all sellers. Customer visibility still follows each product and seller's publishing status.
            </p>

            <div class="admin-product-list">
                @foreach ($products as $product)
                    <article class="admin-product-row">

                        {{-- Product Image --}}
                        <div class="admin-product-thumb">
                            <x-product.image :src="data_get($product, 'images.0.path')" :alt="$product['name']" />
                        </div>

                        {{-- Product Information --}}
                        <div class="admin-product-info">
                            <h2>{{ $product['name'] }}</h2>

                            <p class="admin-product-meta">
                                {{ $product['category'] }}
                                @if (!empty($product['subcategory']))
                                    <span>·</span> {{ $product['subcategory'] }}
                                @endif
                            </p>
                            <p class="admin-product-meta">Sold by {{ $product['seller_name'] }}</p>
                        </div>

                        {{-- Stock --}}
                        <div class="admin-product-stock">
                            <span>Stock</span>
                            <strong>{{ $product['stock_label'] }}</strong>
                        </div>

                        {{-- Price --}}
                        <div class="admin-product-price">
                            <span>Price</span>
                            <strong>&#8377;{{ number_format($product['selling_price']) }}</strong>
                        </div>

                        {{-- Action --}}
                        <div class="admin-product-action">
                            <a
                                class="button button--primary"
                                href="{{ route('admin.products.edit', $product['slug']) }}"
                            >
                                Edit
                            </a>
                        </div>

                    </article>
                @endforeach
            </div>
            {{ $products->links() }}
        </section>
    </x-admin.shell>
</x-layouts.admin>
