<x-layouts.customer title="Marketplace Stores - Sushako">
    <section class="shop-collection-intro">
        <div class="site-shell">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span>Stores</span>
            </nav>
            <div class="shop-collection-intro__content">
                <div>
                    <p class="eyebrow">Marketplace</p>
                    <h1>Marketplace Stores</h1>
                    <p class="lede">Explore independent sellers with products currently available in the Sushako marketplace.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="site-shell store-directory" aria-label="Marketplace stores">
        @forelse ($stores as $store)
            <article class="store-directory__item">
                <a class="store-directory__identity" href="{{ route('stores.show', $store->slug) }}">
                    <span class="store-directory__logo">
                        @if ($store->business_logo_path)
                            <img src="{{ asset('storage/'.$store->business_logo_path) }}" alt="" loading="lazy">
                        @else
                            {{ str($store->store_display_name ?: $store->business_name)->substr(0, 1)->upper() }}
                        @endif
                    </span>
                    <span>
                        <strong>{{ $store->store_display_name ?: $store->business_name }}</strong>
                        <small>{{ $store->city ?: 'Sushako marketplace seller' }}</small>
                    </span>
                </a>
                @if ($store->store_description)
                    <p>{{ $store->store_description }}</p>
                @endif
                <a class="store-directory__visit" href="{{ route('stores.show', $store->slug) }}" aria-label="Visit {{ $store->store_display_name ?: $store->business_name }}">Visit Store <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>
        @empty
            <x-ui.empty-state title="Stores are getting ready" description="Published sellers with customer-visible products will appear here." />
        @endforelse
    </section>

    @if ($stores->hasPages())
        <nav class="site-shell products-pagination" aria-label="Store pagination">{{ $stores->links() }}</nav>
    @endif
</x-layouts.customer>