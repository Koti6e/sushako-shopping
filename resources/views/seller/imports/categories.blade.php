<x-layouts.seller title="Category Explorer">
    <x-seller.header title="Category Explorer" subtitle="Choose exactly which source categories this discovery session may process." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        <section class="seller-panel seller-panel--wide">
            <div class="seller-panel__heading"><div><p class="eyebrow">{{ $session->source->domain }}</p><h2>Session #{{ $session->id }} categories</h2></div><span class="status-badge">{{ str($session->status)->title() }}</span></div>
            <p>Selections are stored against this seller-owned session and can be changed before a discovery retry.</p>
            <form method="POST" action="{{ route('seller.imports.categories.select', $session) }}" data-category-selection>
                @csrf
                <label class="seller-search-field">Search categories <input type="search" placeholder="Search by category name" data-category-search></label>
                <div class="seller-form-actions"><button type="button" class="button button--secondary" data-category-select-all>Select all visible</button><button type="button" class="button button--secondary" data-category-clear>Clear visible</button><button type="submit" class="button button--primary">Save Selection</button></div>
                <div class="seller-category-tree">
                    @forelse ($session->categories->whereNull('parent_id') as $category)
                        @include('seller.imports.partials.category-row', ['category' => $category, 'children' => $session->categories->where('parent_id', $category->id)])
                    @empty
                        <x-ui.empty-state title="No categories discovered" description="Analyse the source again or review its public navigation and sitemap availability." />
                    @endforelse
                </div>
            </form>
        </section>
    </section>
    <script>
        (() => {
            const root = document.querySelector('[data-category-selection]');
            if (!root) return;
            const rows = () => [...root.querySelectorAll('[data-category-row]')];
            root.querySelector('[data-category-search]')?.addEventListener('input', (event) => {
                const query = event.target.value.toLowerCase().trim();
                rows().forEach((row) => { row.hidden = query !== '' && !row.dataset.categoryName.includes(query); });
            });
            root.querySelector('[data-category-select-all]')?.addEventListener('click', () => rows().filter((row) => !row.hidden).forEach((row) => row.querySelector('[data-category-selected]').checked = true));
            root.querySelector('[data-category-clear]')?.addEventListener('click', () => rows().filter((row) => !row.hidden).forEach((row) => row.querySelector('[data-category-selected]').checked = false));
        })();
    </script>
</x-layouts.seller>
