@props([
    'product',
    'compact' => false,
])

@php
    $bagIcon = <<<'SVG'
<svg class="product-action-icon product-action-icon--bag" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    <path d="M7.5 8.25V7a4.5 4.5 0 0 1 9 0v1.25" />
    <path d="M5.2 8.25h13.6l.85 11.15a2 2 0 0 1-2 2.1H6.35a2 2 0 0 1-2-2.1L5.2 8.25Z" />
</svg>
SVG;
    $displayBadge = $product['seller_official'] ? 'Official Store' : ($product['badge'] ?: ($product['is_best_seller'] ? 'Bestseller' : ($product['is_new'] ? 'New' : null)));
    $sellerTrustLabel = ! $product['seller_official'] && ($product['seller_verified'] ?? false) ? 'Verified Seller' : null;
@endphp

<article
    class="product-card {{ $compact ? 'product-card--compact' : '' }}"
    data-product-card
    data-name="{{ str($product['name'].' '.$product['collection'].' '.$product['subcategory'].' '.$product['brand'].' '.$product['department'])->lower() }}"
    data-department="{{ $product['department'] }}"
    data-department-slug="{{ $product['department_slug'] }}"
    data-category="{{ $product['category'] }}"
    data-subcategory="{{ $product['subcategory'] }}"
    data-brand="{{ $product['brand'] }}"
    data-sizes="{{ implode(',', $product['sizes']) }}"
    data-colours="{{ implode(',', array_keys($product['colours'])) }}"
    data-filters='@json($product['filter_values'])'
    data-price="{{ $product['selling_price'] }}"
    data-rating="{{ $product['rating'] }}"
    data-reviews="{{ $product['reviews'] }}"
    data-discount="{{ $product['discount'] }}"
    data-available="{{ $product['available'] ? '1' : '0' }}"
    data-new="{{ $product['is_new'] ? '1' : '0' }}"
    data-best-selling="{{ $product['is_best_seller'] ? '1' : '0' }}"
>
    <div class="product-card__media">
        <a href="{{ route('products.show', $product['slug']) }}" class="product-card__image">
            <x-product.image :src="data_get($product, 'images.0.path')" :alt="$product['name'].' front view'" :category="$product['category'] ?? $product['department'] ?? null" />
            @if (isset($product['images'][1]))
                <x-product.image class="product-card__image-alt" :src="$product['images'][1]['path']" :alt="$product['name'].' alternate view'" :category="$product['category'] ?? $product['department'] ?? null" />
            @endif
            @if (filled($displayBadge))
                <span>{{ $displayBadge }}</span>
            @endif
            @if ($product['discount'] > 0)
                <strong>{{ $product['discount'] }}% off</strong>
            @endif
        </a>
        <div class="product-card__quick-actions" aria-label="Product actions">
            <button class="icon-button" type="button" data-wishlist data-slug="{{ $product['slug'] }}" aria-label="Add {{ $product['name'] }} to wishlist" title="Wishlist">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
            </button>
            <button class="icon-button" type="button" data-share-url="{{ route('products.show', $product['slug']) }}" data-share-title="{{ $product['name'] }}" aria-label="Share {{ $product['name'] }}" title="Share">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7M16 6l-4-4-4 4M12 2v14"/></svg>
            </button>
        </div>
    </div>

    <div class="product-card__body">
        <h3><a href="{{ route('products.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>

        <div class="product-card__seller product-card__seller--marketplace">
            <span class="product-card__seller-line">
                <small>Sold by</small>
                @if ($product['seller_slug'])
                    <a href="{{ route('stores.show', $product['seller_slug']) }}">{{ $product['seller_name'] }}</a>
                @else
                    <b>{{ $product['seller_name'] }}</b>
                @endif
            </span>
            @if ($sellerTrustLabel)
                <span class="product-card__seller-trust"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>{{ $sellerTrustLabel }}</span>
            @endif
            @if ($product['seller_city'])
                <span class="product-card__seller-meta">{{ $product['seller_city'] }}</span>
            @endif
        </div>

        <div class="price-row">
            @if ($product['has_variant_price_range'])
                <small>From</small>
            @endif
            <strong>&#8377;{{ number_format($product['selling_price']) }}</strong>
            @if ($product['discount'] > 0)
                <s>&#8377;{{ number_format($product['mrp']) }}</s>
                <span>{{ $product['discount'] }}% off</span>
            @endif
        </div>

        <div class="product-card__availability">{{ $product['stock_label'] }}</div>
        @if (filled($product['delivery_label']))
            <div class="product-card__delivery">{{ $product['delivery_label'] }}</div>
        @endif

        <div class="product-card__actions">
            @if ($product['available'])
                <button class="button button--primary add-to-cart-button" type="button" data-add-to-cart data-slug="{{ $product['slug'] }}" data-colour="{{ array_key_first($product['colours']) }}" data-size="{{ $product['sizes'][0] }}" data-quantity="1">
                    {!! $bagIcon !!}
                    <span data-add-to-cart-label>Add to Cart</span>
                </button>
                <form class="product-card__buy-now" method="POST" action="{{ route('cart.buy-now') }}" data-buy-now-form>
                    @csrf
                    <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                    <input type="hidden" name="colour" value="{{ array_key_first($product['colours']) }}">
                    <input type="hidden" name="size" value="{{ $product['sizes'][0] }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="button button--buy-now" type="submit" data-buy-now-submit>
                        <svg class="product-action-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m13 2-8 12h6l-1 8 8-12h-6l1-8Z"/></svg>
                        <span data-buy-now-label>Buy Now</span>
                    </button>
                </form>
            @else
                <button class="button button--ghost" type="button" disabled>Out of Stock</button>
            @endif
        </div>
    </div>
</article>
