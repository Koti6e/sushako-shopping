<div class="seller-category-tree__row" data-category-row data-category-name="{{ str($category->name)->lower() }}">
    <label><input type="checkbox" name="selected[]" value="{{ $category->id }}" @checked($category->selected && ! $category->excluded) data-category-selected><span><strong>{{ $category->name }}</strong><small>{{ number_format($category->product_count) }} discovered URLs · {{ str($category->status)->title() }}</small></span></label>
    <label class="seller-category-tree__exclude"><input type="checkbox" name="excluded[]" value="{{ $category->id }}" @checked($category->excluded)> Exclude</label>
    @foreach ($children as $child)
        @include('seller.imports.partials.category-row', ['category' => $child, 'children' => $session->categories->where('parent_id', $child->id)])
    @endforeach
</div>
