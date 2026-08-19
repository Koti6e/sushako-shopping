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
    <a href="{{ route('products.show', $product['slug']) }}" class="product-card__image">
        <img src="{{ $product['images'][0]['path'] }}" alt="{{ $product['name'] }} front view" loading="lazy">
        @if (isset($product['images'][1]))
            <img class="product-card__image-alt" src="{{ $product['images'][1]['path'] }}" alt="{{ $product['name'] }} alternate view" loading="lazy">
        @endif
        @if (filled($displayBadge))
            <span>{{ $displayBadge }}</span>
        @endif
        @if (! $compact && $product['discount'] > 0)
            <strong>{{ $product['discount'] }}% off</strong>
        @endif
    </a>
    @if ($compact)
        <div class="product-card__quick-actions" aria-label="Product actions">
            <button class="icon-button" type="button" data-wishlist data-slug="{{ $product['slug'] }}" aria-label="Add {{ $product['name'] }} to wishlist" title="Wishlist">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
            </button>
            <button class="icon-button" type="button" data-share-url="{{ route('products.show', $product['slug']) }}" data-share-title="{{ $product['name'] }}" aria-label="Share {{ $product['name'] }}" title="Share">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7M16 6l-4-4-4 4M12 2v14"/></svg>
            </button>
        </div>
    @endif
    <div class="product-card__body">
        @unless ($compact)
            <div class="product-card__topline">
                <p>{{ $product['brand'] }}</p>
                <small>{{ $product['stock_label'] }}</small>
            </div>
        @endunless
        <h3><a href="{{ route('products.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>
        <div class="product-card__seller product-card__seller--marketplace">
            <span class="product-card__seller-line">
                <small>Sold by</small>
                @if ($product['seller_slug'])
                    <a href="{{ route('stores.show', $product['seller_slug']) }}">{{ $product['seller_name'] }}</a>
                @else
                    <b>{{ $product['seller_name'] }}</b>
                @endif
                @if ($product['seller_trust_label'])
                    <em>{{ $product['seller_trust_label'] }}</em>
                @endif
            </span>
            <span class="product-card__seller-meta">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                {{ $product['seller_city'] ?: 'India' }}
            </span>
        </div>
        <div class="product-card__rating">
            @if ((int) $product['reviews'] > 0)
                <span>{{ $product['rating'] }} <i class="fa-solid fa-star" aria-hidden="true"></i></span>
                <small>{{ $product['reviews'] }} reviews</small>
            @else
                <span>New Seller</span>
            @endif
        </div>
        <div class="price-row">
            @if ($product['has_variant_price_range'])
                <small>From</small>
            @endif
            <strong>&#8377;{{ number_format($product['selling_price']) }}</strong>
            @if (! $compact && $product['discount'] > 0)
                <s>&#8377;{{ number_format($product['mrp']) }}</s>
                <span>{{ $product['discount'] }}% off</span>
            @endif
        </div>
        <div class="product-card__delivery">
            <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
            <span>{{ $product['delivery_label'] }}</span>
        </div>
        @unless ($compact)
            <div class="product-card__options">
                @if ($product['has_colour_options'])
                    <span>{{ array_key_first($product['colours']) }}</span>
                @endif
                @if ($product['has_size_options'])
                    <span>{{ $product['sizes'][0] }}</span>
                @endif
                @if ($product['has_custom_options'])
                    <span>{{ $product['option_name'] }}: {{ $product['option_values'][0] ?? $product['sizes'][0] }}</span>
                @endif
                @unless ($product['has_colour_options'] || $product['has_size_options'] || $product['has_custom_options'])
                    <span>{{ $product['fulfillment_scope'] }}</span>
                @endunless
            </div>
        @endunless
        <div class="product-card__actions">
            @if ($product['available'])
                <button
                    class="button button--primary add-to-cart-button"
                    type="button"
                    data-add-to-cart
                    data-slug="{{ $product['slug'] }}"
                    data-colour="{{ array_key_first($product['colours']) }}"
                    data-size="{{ $product['sizes'][0] }}"
                    data-quantity="1"
                >
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
                        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                        <span data-buy-now-label>Buy Now</span>
                    </button>
                </form>
            @else
                <button class="button button--ghost" type="button" disabled>Out of Stock</button>
            @endif
        </div>
        @unless ($compact)
            <div class="product-card__utility">
                <button class="icon-button" type="button" data-wishlist data-slug="{{ $product['slug'] }}" aria-label="Add {{ $product['name'] }} to wishlist">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                </button>
                <button class="icon-button" type="button" data-share-url="{{ route('products.show', $product['slug']) }}" data-share-title="{{ $product['name'] }}" aria-label="Share {{ $product['name'] }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7M16 6l-4-4-4 4M12 2v14"/></svg>
                </button>
                <a class="icon-button" href="https://wa.me/{{ config('services.whatsapp.support_number') }}?text={{ rawurlencode("Hello Sushako Shopping,\n\nI would like to enquire about:\n".$product['name']."\n".route('products.show', $product['slug'])."\nVariant: ".array_key_first($product['colours'])." / ".$product['sizes'][0]."\nPrice: INR ".$product['selling_price']) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp enquiry for {{ $product['name'] }}">
                    <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16.02 3.2A12.67 12.67 0 0 0 5.1 22.26L3.2 29l6.9-1.82A12.68 12.68 0 1 0 16.02 3.2Zm-4.16 7.7c-.25 0-.64.1-.97.46-.34.37-1.28 1.25-1.28 3.05 0 1.79 1.31 3.53 1.49 3.77.18.24 2.53 4.05 6.25 5.52 3.08 1.22 3.72.98 4.39.92.67-.06 2.16-.88 2.46-1.74.31-.86.31-1.6.22-1.75-.09-.15-.33-.24-.7-.43-.36-.18-2.16-1.06-2.49-1.18-.33-.13-.58-.18-.82.18-.24.37-.94 1.18-1.15 1.43-.21.24-.42.27-.79.09-.36-.18-1.54-.57-2.94-1.82-1.09-.97-1.82-2.17-2.03-2.54-.21-.36-.02-.56.16-.74.16-.16.36-.42.55-.64.18-.21.24-.36.36-.61.12-.24.06-.46-.03-.64-.09-.18-.82-1.98-1.12-2.71-.3-.71-.6-.61-.82-.62h-.74Z"/></svg>
                </a>
            </div>
        @endunless
    </div>
</article>
