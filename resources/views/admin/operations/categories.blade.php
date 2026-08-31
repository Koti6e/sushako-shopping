<x-layouts.admin title="Categories - Admin">
    <x-admin.shell
        eyebrow="Catalog Structure"
        title="Categories"
        subtitle="Manage the Sushako marketplace category hierarchy."
    >
        <x-slot:actions>
            <x-admin.action
                :href="route('admin.products.index')"
                tone="secondary"
                icon="fa-solid fa-box"
            >
                Products
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
                <div class="status-banner">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="status-banner status-banner--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <p class="eyebrow">Marketplace Taxonomy</p>

            <h1>Categories</h1>

            <p class="lede">
                Organise the marketplace using parent categories,
                subcategories and deeper category levels.
            </p>

            @php
                $renderCategoryTree = function ($category, $level = 0) use (&$renderCategoryTree) {
                    $children = $category->children ?? collect();
                };
            @endphp

            <div class="admin-table">

                @forelse ($categories as $category)

                    <article>
                        <div style="display:flex;align-items:flex-start;gap:1rem;width:100%;">

                            @if (!empty($category['image']))
                                <img
                                    src="{{ asset($category['image']) }}"
                                    alt="{{ $category['name'] }}"
                                    loading="lazy"
                                    style="width:72px;height:72px;object-fit:cover;border-radius:12px;"
                                >
                            @endif

                            <div style="flex:1;">

                                <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                                    <h2 style="margin:0;">
                                        {{ $category['name'] }}
                                    </h2>

                                    <span class="eyebrow">
                                        Top Level
                                    </span>
                                </div>

                                @if (!empty($category['description']))
                                    <p>
                                        {{ $category['description'] }}
                                    </p>
                                @endif

                                <div style="margin-top:.75rem;">

                                    <a
                                        class="button button--secondary"
                                        href="{{ route('department.show', $category['slug']) }}"
                                    >
                                        Preview
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.categories.destroy', $category['id']) }}"
                                        style="display:inline-block;margin-left:.5rem;"
                                        onsubmit="return confirm('Delete this category? Categories containing products or child categories cannot be deleted.');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="button button--ghost"
                                            type="submit"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </div>

                            </div>
                        </div>
                    </article>

                @empty

                    <article>
                        <div>
                            <h2>No categories found</h2>
                            <p>
                                Create your first marketplace category to begin
                                building the Sushako catalog.
                            </p>
                        </div>
                    </article>

                @endforelse

            </div>

        </section>
    </x-admin.shell>
</x-layouts.admin>