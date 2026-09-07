<x-layouts.customer title="{{ $collection->name }} - Sushako Shopping">
    <section class="site-shell section-block section-block--storefront collection-page">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('shop') }}">Shop</a><span>/</span><span>{{ $collection->name }}</span></nav>
        <div class="section-heading section-heading--editorial"><div><p class="eyebrow">Product Collection</p><h1>{{ $collection->name }}</h1>@if($collection->description)<p class="lede">{{ $collection->description }}</p>@endif</div><a href="{{ route('shop') }}">All Products</a></div>
        <div class="product-grid product-grid--available">@forelse($products as $product)<x-product.card :product="$product" />@empty<x-ui.empty-state title="No available products" description="Products will appear in this collection when they are published and available." />@endforelse</div>
    </section>
</x-layouts.customer>
