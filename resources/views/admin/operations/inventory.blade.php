<x-layouts.admin title="Inventory - Admin">
    <x-admin.shell eyebrow="Inventory Management" title="Stock overview" subtitle="Monitor product stock and variant availability.">
            <section class="admin-dashboard-panel">
                <p class="eyebrow">Inventory Management</p>
                <h1>Stock overview</h1>
                <div class="admin-table">
                    @foreach ($products as $product)
                        <article>
                            <x-product.image :src="data_get($product, 'images.0.path')" :alt="$product['name']" />
                            <div>
                                <h2>{{ $product['name'] }}</h2>
                                <p>{{ $product['stock_label'] }} · {{ count($product['variants']) }} variants</p>
                            </div>
                            <a class="button button--primary" href="{{ route('admin.products.edit', $product['slug']) }}">Manage</a>
                        </article>
                    @endforeach
                </div>
            </section>
    </x-admin.shell>
</x-layouts.admin>
