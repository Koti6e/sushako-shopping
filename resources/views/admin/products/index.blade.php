<x-layouts.admin title="Products - Admin">
    <x-admin.shell eyebrow="Product Management" title="Own-store products" subtitle="Review products, pricing, stock status and quick actions.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.categories.create')" tone="secondary" icon="fa-solid fa-layer-group">Add Category</x-admin.action>
            <x-admin.action :href="route('admin.products.create')" tone="primary" icon="fa-solid fa-plus">Add Product</x-admin.action>
        </x-slot:actions>
            <section class="admin-dashboard-panel">
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif
                <p class="eyebrow">Product Management</p>
                <h1>Own-store products</h1>
                <p class="lede">Review Sushako products, pricing, stock status and quick actions from one focused catalog view.</p>
                <div class="admin-table">
                    @foreach ($products as $product)
                        <article>
                            <img src="{{ $product['images'][0]['path'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            <div>
                                <h2>{{ $product['name'] }}</h2>
                                <p>{{ $product['category'] }} / {{ $product['subcategory'] }} · {{ $product['stock_label'] }}</p>
                                <strong>&#8377;{{ number_format($product['selling_price']) }}</strong>
                            </div>
                            <a class="button button--primary" href="{{ route('admin.products.edit', $product['slug']) }}">Edit</a>
                        </article>
                    @endforeach
                </div>
            </section>
    </x-admin.shell>
</x-layouts.admin>
