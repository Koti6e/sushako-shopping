<x-layouts.customer title="Empty Cart - Sushako Shopping">
    <section class="content-screen">
        <div class="empty-cart">
            <x-brand.logo context="large" loading="lazy" />
            <p class="eyebrow">Your Cart</p>
            <h1>Sushako products are coming soon.</h1>
            <p class="lede">Homemade health mix, masala powders and women's clothing will be available here after products are added from admin.</p>
            <a href="{{ route('shop') }}" class="button button--primary">Explore Categories</a>
        </div>
    </section>
</x-layouts.customer>
