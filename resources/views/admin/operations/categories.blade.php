<x-layouts.admin title="Categories - Admin">
    <x-admin.shell
        eyebrow="Catalog"
        title="Master Categories"
        subtitle="One marketplace hierarchy shared by sellers, marketing, and customers."
    >
        <x-slot:actions>
            <x-admin.action
                :href="route('admin.category-requests.index')"
                tone="secondary"
                icon="fa-solid fa-inbox"
            >
                Category Requests
            </x-admin.action>

            <x-admin.action
                :href="route('admin.categories.create')"
                tone="primary"
                icon="fa-solid fa-plus"
            >
                Add Category
            </x-admin.action>
        </x-slot:actions>

        <section class="admin-dashboard-panel">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
            @endif

            <div class="admin-catalog-intro">
                <div>
                    <p class="eyebrow">Marketplace taxonomy</p>
                    <h1>Categories</h1>
                    <p class="lede">
                        Categories marked available are immediately selectable when a seller lists a product.
                    </p>
                </div>

                <span class="admin-catalog-intro__count">
                    {{ $categories->count() }} top-level
                </span>
            </div>

            <div class="admin-catalog-tree">
                @forelse ($categories as $category)
                    @include('admin.operations._category-row', [
                        'category' => $category,
                        'level' => 0,
                    ])
                @empty
                    <x-ui.empty-state
                        title="No categories yet"
                        description="Create the first marketplace category to begin organising seller products."
                    />
                @endforelse
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
