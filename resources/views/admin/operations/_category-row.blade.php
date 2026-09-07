<article class="admin-catalog-row" style="--category-depth: {{ min($level, 3) }}">
    <div class="admin-catalog-row__identity">
        <span class="admin-catalog-row__icon">
            <i class="fa-solid {{ $level ? 'fa-turn-up fa-rotate-90' : 'fa-folder-tree' }}" aria-hidden="true"></i>
        </span>

        <div>
            <strong>{{ $category->name }}</strong>
            <small>{{ $category->slug }}</small>
        </div>
    </div>

    <div class="admin-catalog-row__meta">
        <span class="admin-status-badge {{ $category->is_active ? 'is-active' : 'is-inactive' }}">
            {{ $category->is_active ? 'Active' : 'Inactive' }}
        </span>

        <span class="admin-status-badge {{ $category->available_to_sellers ? 'is-seller-ready' : 'is-muted' }}">
            {{ $category->available_to_sellers ? 'Seller ready' : 'Admin only' }}
        </span>

        <small>{{ $category->products_count }} products</small>
    </div>

    <div class="admin-catalog-row__actions">
        <a class="button button--ghost"
           href="{{ route('category.show', $category->slug) }}"
           target="_blank"
           rel="noreferrer">
            Preview
        </a>

        <a class="button button--secondary"
           href="{{ route('admin.categories.edit', $category) }}">
            Edit
        </a>

        <form method="POST" action="{{ route('admin.categories.status.update', $category) }}">
            @csrf
            @method('PATCH')

            <input type="hidden"
                   name="is_active"
                   value="{{ $category->is_active ? 0 : 1 }}">

            <button class="button button--ghost" type="submit">
                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>

        <form method="POST"
              action="{{ route('admin.categories.destroy', $category) }}"
              onsubmit="return confirm('Delete this category? Categories with products or child categories cannot be deleted.');">
            @csrf
            @method('DELETE')

            <button class="button button--ghost" type="submit">
                Delete
            </button>
        </form>
    </div>
</article>

@foreach ($category->children as $child)
    @include('admin.operations._category-row', [
        'category' => $child,
        'level' => $level + 1,
    ])
@endforeach
