<x-layouts.admin title="{{ $productModel->name }} - Product Monitor">
    <x-admin.shell eyebrow="Catalog Monitoring" title="{{ $productModel->name }}" subtitle="Read-only seller product monitoring. Publication and commercial changes remain with the seller." badge="Seller Owned" :show-subtitle="true">
        <x-slot:actions>
            <x-admin.action :href="route('admin.products.index')" tone="secondary" icon="fa-solid fa-arrow-left">Products</x-admin.action>
            <x-admin.action :href="route('admin.sellers.show', $productModel->vendor)" tone="primary" icon="fa-solid fa-store">Open Seller</x-admin.action>
        </x-slot:actions>

        <section class="admin-control-page admin-product-monitor">
            <div class="admin-seller-profile__summary">
                <article><div><p>Seller</p><strong>{{ $product['seller_name'] }}</strong></div></article>
                <article><div><p>Publication</p><strong>{{ $productModel->is_published ? 'Published' : 'Unpublished' }}</strong></div></article>
                <article><div><p>Status</p><strong>{{ str($productModel->seller_status ?: 'draft')->replace('_', ' ')->title() }}</strong></div></article>
                <article><div><p>Stock</p><strong>{{ $product['stock_label'] }}</strong></div></article>
            </div>

            <div class="admin-seller-profile__grid">
                <article class="admin-enterprise-card">
                    <h2>Product details</h2>
                    <dl>
                        <div><dt>Category</dt><dd>{{ $product['category'] ?: 'Not assigned' }}</dd></div>
                        <div><dt>Price</dt><dd>Rs {{ number_format((int) $productModel->selling_price) }}</dd></div>
                        <div><dt>Regular price</dt><dd>Rs {{ number_format((int) $productModel->mrp) }}</dd></div>
                        <div><dt>Created</dt><dd>{{ $productModel->created_at?->format('d M Y, h:i A') ?: 'Not available' }}</dd></div>
                        <div><dt>Updated</dt><dd>{{ $productModel->updated_at?->format('d M Y, h:i A') ?: 'Not available' }}</dd></div>
                    </dl>
                </article>

                <article class="admin-enterprise-card">
                    <h2>Seller control</h2>
                    <p class="lede">This product is owned by {{ $product['seller_name'] }}. The seller controls whether it is published, unpublished, priced, or stocked.</p>
                    <p class="status-banner">Superadmin monitoring is read-only for seller-owned products.</p>
                </article>

                <article class="admin-enterprise-card admin-enterprise-card--wide">
                    <h2>Description</h2>
                    <p>{{ $productModel->short_description ?: 'No short description provided.' }}</p>
                    @if($productModel->full_description)
                        <p>{{ $productModel->full_description }}</p>
                    @endif
                </article>
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
