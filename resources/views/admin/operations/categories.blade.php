<x-layouts.admin title="Categories - Admin">
    <x-admin.shell eyebrow="Catalog Structure" title="Categories" subtitle="Manage storefront categories and merchandising structure.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.products.index')" tone="secondary" icon="fa-solid fa-box">Products</x-admin.action>
            <x-admin.action :href="route('admin.categories.create')" tone="primary" icon="fa-solid fa-plus">Add Category</x-admin.action>
        </x-slot:actions>
            <section class="admin-dashboard-panel">
                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
                @endif
                <p class="eyebrow">Catalog Structure</p>
                <h1>Categories</h1>
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
    </x-admin.shell>
</x-layouts.admin>
