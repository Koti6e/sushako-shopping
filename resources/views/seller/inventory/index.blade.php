<x-layouts.seller title="Inventory">
    <x-seller.header title="Inventory" subtitle="Adjust stock with audit reasons. Negative inventory is blocked." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @foreach ($variants as $variant)
            <article class="seller-inventory-row">
                <div><strong>{{ $variant->product->name }}</strong><span>{{ $variant->sku }} · Available {{ $variant->stock }}</span></div>
                <form method="POST" action="{{ route('seller.inventory.adjust', $variant) }}">
                    @csrf
                    <select name="mode"><option value="increase">Increase</option><option value="decrease">Decrease</option><option value="set">Set exact</option></select>
                    <input type="number" name="quantity" min="0" required>
                    <input name="reason" placeholder="Adjustment reason" required>
                    <button type="submit">Update</button>
                </form>
            </article>
        @endforeach
        {{ $variants->links() }}
    </section>
</x-layouts.seller>
