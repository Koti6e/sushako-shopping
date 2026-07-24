<x-layouts.admin title="Categories - Admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <x-brand.logo context="admin" href="{{ route('admin.dashboard') }}" loading="eager" />
            <nav aria-label="Admin navigation">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.categories.index') }}">Categories</a>
                <a href="{{ route('admin.products.index') }}">Products</a>
                <a href="{{ route('admin.inventory.index') }}">Inventory</a>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
                <a href="{{ route('admin.customers.index') }}">Customers</a>
                <a href="{{ route('admin.settings.company') }}"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <x-admin.side-meta />
        </aside>
        <main class="admin-main">
            <header class="admin-topbar"><x-brand.logo href="{{ route('admin.dashboard') }}" loading="eager" /><span>Categories</span></header>
            <section class="admin-dashboard-panel">
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
                @endif
                <p class="eyebrow">Catalog Structure</p>
                <h1>Categories</h1>
                <a class="button button--primary" href="{{ route('admin.categories.create') }}">Add Category</a>
                <div class="admin-table">
                    @foreach ($categories as $category)
                        <article>
                            <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" loading="lazy">
                            <div>
                                <h2>{{ $category['name'] }}</h2>
                                <p>{{ $category['description'] }}</p>
                            </div>
                            <a class="button button--secondary" href="{{ route('department.show', $category['slug']) }}">Preview</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category['id']) }}">
                                @csrf
                                @method('DELETE')
                                <button class="button button--ghost" type="submit">Delete</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>
    </div>
</x-layouts.admin>
