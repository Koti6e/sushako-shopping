@php
    $defaultColour = array_key_first($product['colours']) ?: 'Standard';
    $defaultSize = $product['sizes'][0] ?? 'Standard';
    $defaultPrice = $product['variant_prices'][$defaultSize] ?? $product['selling_price'];
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'description' => $product['full_description'] ?? $product['short_description'] ?? null,
        'image' => collect($product['images'] ?? [])->pluck('path')->values()->all(),
        'brand' => ['@type' => 'Brand', 'name' => $product['brand'] ?? 'Sushako'],
        'offers' => [
            '@type' => 'Offer',
            'url' => url()->current(),
            'priceCurrency' => 'INR',
            'price' => (string) ($defaultPrice ?? $product['selling_price']),
            'availability' => ($product['available'] ?? false)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => $product['seller_name'] ?? 'Sushako seller',
            ],
        ],
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

<x-layouts.customer :title="$product['name'].' - Sushako Shopping'" :description="$product['short_description'] ?: $product['full_description']">
    <section class="site-shell product-detail">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('department.show', $product['department_slug']) }}">{{ $product['department'] }}</a>
            <span>/</span>
            <span>{{ $product['name'] }}</span>
        </nav>

        @if (session('status'))
            <div class="status-banner">{{ session('status') }}</div>
        @endif

        <div class="product-detail__grid product-detail__grid--premium">
            <div class="product-gallery product-gallery--carousel product-detail-card" aria-label="Product gallery" data-product-gallery>
                <div class="product-gallery__stage">
                    @forelse ($product['images'] as $image)
                        <figure class="product-gallery__slide @if($loop->first) is-active @endif" data-product-slide>
                            <x-product.image :src="$image['path']" :alt="$product['name'].' '.$image['label']" :loading="$loop->first ? 'eager' : 'lazy'" :category="$product['category'] ?? $product['department'] ?? null" fallback-class="product-gallery__no-preview" />
                            <figcaption><span>{{ $image['label'] }}</span><strong>{{ $product['name'] }}</strong></figcaption>
                        </figure>
                    @empty
                        <x-product.image :category="$product['category'] ?? $product['department'] ?? null" fallback-class="product-gallery__no-preview" />
                    @endforelse
                    @if (count($product['images']) > 1)
                        <button class="product-gallery__control product-gallery__control--prev" type="button" data-product-gallery-prev aria-label="Previous image">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button class="product-gallery__control product-gallery__control--next" type="button" data-product-gallery-next aria-label="Next image">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    @endif
                </div>
                @if (count($product['images']) > 1)
                    <div class="product-gallery__thumbs" aria-label="Product image thumbnails">
                        @foreach ($product['images'] as $image)
                            <button class="@if($loop->first) is-active @endif" type="button" data-product-gallery-thumb aria-label="Show {{ $image['label'] }}">
                                <x-product.image :src="$image['path']" :alt="$image['label']" :category="$product['category'] ?? $product['department'] ?? null" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product-buy-panel product-detail-card product-detail-card--buy">
                <p class="eyebrow">{{ $product['collection'] }}</p>
                <h1>{{ $product['name'] }}</h1>
                <p class="lede">{{ $product['short_description'] }}</p>
                @if ($product['seller_slug'])
                    <p class="product-detail-seller-line">
                        Sold by <a href="{{ route('stores.show', $product['seller_slug']) }}">{{ $product['seller_name'] }}</a>
                        @if ($product['seller_city'])
                            <span>{{ $product['seller_city'] }}</span>
                        @endif
                    </p>
                @endif
                <div class="product-meta-row">
                    <span>{{ $product['badge'] }}</span>
                    @if ($product['seller_official'])
                        <span><i class="fa-solid fa-certificate" aria-hidden="true"></i> Official Store</span>
                    @elseif ($product['seller_verified'])
                        <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified Seller</span>
                    @endif
                    @if ((int) $product['reviews'] > 0)
                        <span>{{ $product['rating'] }} stars</span>
                        <span>{{ $product['reviews'] }} reviews</span>
                    @endif
                    <span>{{ $product['stock_label'] }}</span>
                </div>
                <div class="price-row price-row--large">
                    <strong data-product-price>&#8377;{{ number_format($defaultPrice) }}</strong>
                    <s>&#8377;{{ number_format($product['mrp']) }}</s>
                    <span>{{ $product['discount'] }}% off</span>
                </div>

                @if ($product['has_colour_options'])
                    <div class="option-group" aria-label="Colour options">
                        <div class="option-group__label">Colour</div>
                        <div class="swatch-row" data-product-colours>
                            @foreach ($product['colours'] as $colour => $meta)
                                <label>
                                    <input type="radio" name="colour" value="{{ $colour }}" @checked($loop->first)>
                                    <span style="--swatch: {{ $meta['hex'] }}"></span>
                                    {{ $colour }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product['has_custom_options'])
                    <div class="option-group" aria-label="{{ $product['option_name'] }} options">
                        <div class="option-group__label">{{ $product['option_name'] }}</div>
                        <div class="size-row size-row--storage" data-product-options>
                            @foreach ($product['option_values'] as $option)
                                <label>
                                    <input
                                        type="radio"
                                        name="size"
                                        value="{{ $option }}"
                                        data-option-price="{{ $product['variant_prices'][$option] ?? $product['selling_price'] }}"
                                        @checked($loop->first)
                                    >
                                    <span>{{ $option }}</span>
                                    <strong>&#8377;{{ number_format($product['variant_prices'][$option] ?? $product['selling_price']) }}</strong>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product['has_size_options'])
                    <div class="option-group" aria-label="Size options">
                        <div class="option-group__label">Size</div>
                        <div class="size-row" data-product-sizes>
                            @foreach ($product['sizes'] as $size)
                                <label><input type="radio" name="size" value="{{ $size }}" @checked($loop->first)> {{ $size }}</label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <label for="quantity">Quantity</label>
                <div class="quantity-row">
                    <input id="quantity" type="number" name="quantity" min="1" max="10" value="1" @disabled(! $product['available'])>
                </div>

                @unless ($product['available'])
                    <div class="status-banner status-banner--warning">
                        @if ($product['coming_soon'] ?? false)
                            Coming Soon. Launches {{ $product['go_live_label'] }}. Purchasing opens automatically at launch time.
                        @else
                            Out of Stock. You can still wishlist or share this product, and purchasing will reopen when the seller updates inventory.
                        @endif
                    </div>
                @endunless

                <div class="buy-actions">
                    @if ($product['available'])
                        <button
                            class="button button--primary add-to-cart-button"
                            type="button"
                            data-add-to-cart
                            data-slug="{{ $product['slug'] }}"
                            data-colour="{{ $defaultColour }}"
                            data-size="{{ $defaultSize }}"
                            data-quantity-target="#quantity"
                        >
                            <svg class="product-action-icon product-action-icon--bag" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M7.5 8.25V7a4.5 4.5 0 0 1 9 0v1.25" />
                                <path d="M5.2 8.25h13.6l.85 11.15a2 2 0 0 1-2 2.1H6.35a2 2 0 0 1-2-2.1L5.2 8.25Z" />
                            </svg>
                            <span data-add-to-cart-label>Add to Bag</span>
                        </button>
                        <form class="buy-now-form" method="POST" action="{{ route('cart.buy-now') }}">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                            <input type="hidden" name="colour" value="{{ $defaultColour }}" data-buy-now-colour>
                            <input type="hidden" name="size" value="{{ $defaultSize }}" data-buy-now-size>
                            <input type="hidden" name="quantity" value="1" data-buy-now-quantity>
                            <button class="button button--secondary" type="submit" data-buy-now-submit><span data-buy-now-label>Buy Now</span></button>
                        </form>
                    @else
                        <button class="button button--primary" type="button" disabled>Out of Stock</button>
                    @endif
                    <button class="button button--ghost" type="button" data-wishlist data-slug="{{ $product['slug'] }}">Wishlist</button>
                    <button class="button button--ghost" type="button" data-share-url="{{ route('products.show', $product['slug']) }}" data-share-title="{{ $product['name'] }}">Share</button>
                    <a class="button button--secondary" href="https://wa.me/{{ config('services.whatsapp.support_number') }}?text={{ rawurlencode("Hello Sushako Shopping,\n\nI would like to enquire about:\n".$product['name']."\n".route('products.show', $product['slug'])."\nSelected Option: ".($product['has_colour_options'] ? $defaultColour : 'Standard').(($product['has_size_options'] || $product['has_custom_options']) ? ' / '.$defaultSize : '')."\nPrice: INR ".$defaultPrice) }}" target="_blank" rel="noopener noreferrer">WhatsApp Enquiry</a>
                </div>

                <div class="product-trust-strip" aria-label="Customer reassurance">
                    <span class="product-trust-item">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        <span>Secure payments</span>
                    </span>
                    @if (filled(config('services.whatsapp.support_number')))
                        <a class="product-trust-item" href="https://wa.me/{{ config('services.whatsapp.support_number') }}?text={{ rawurlencode(config('services.whatsapp.support_message')) }}" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 11.5a8 8 0 0 1-11.8 7.1L4 20l1.4-4A8 8 0 1 1 20 11.5Z"/><path d="M9 8.8c.2-.4.4-.4.7-.4h.5c.2 0 .4.1.5.4l.6 1.3c.1.2.1.4-.1.6l-.5.6c.5 1 1.2 1.7 2.2 2.2l.6-.5c.2-.2.4-.2.6-.1l1.3.6c.3.1.4.3.4.5v.5c0 .3 0 .5-.4.7-1 .5-2.1.2-3.1-.3-1.7-.8-3-2.1-3.8-3.8-.5-1-.8-2.1-.3-3.1Z"/></svg>
                            <span>WhatsApp support</span>
                        </a>
                    @endif
                    <a class="product-trust-item" href="{{ route('orders.track') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 6h10a3 3 0 0 1 3 3v7"/><path d="m14 13 3 3 3-3"/><path d="M4 6v13h5"/></svg>
                        <span>Track order</span>
                    </a>
                    <a class="product-trust-item" href="{{ route('policies.return-refund') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 5h12v14H6z"/><path d="m9 9 2 2 4-4M9 15h6"/></svg>
                        <span>{{ $product['seller_return_policy'] }}</span>
                    </a>
                </div>
            </div>
        </div>

        <section class="product-info-grid product-info-grid--premium">
            <div>
                <h2>Description</h2>
                <p>{{ $product['full_description'] }}</p>
            </div>
            <div>
                <h2>Details</h2>
                <dl>
                    @foreach ($product['details'] as $label => $value)
                        <dt>{{ $label }}</dt><dd>{{ $value }}</dd>
                    @endforeach
                </dl>
            </div>
            <div>
                <h2>Customer Reviews</h2>
                @forelse ($product['reviews_list'] as $review)
                    <blockquote><strong>{{ $review['name'] }} · {{ $review['rating'] }} stars</strong><p>{{ $review['text'] }}</p></blockquote>
                @empty
                    <p>No customer reviews have been added for this product yet.</p>
                @endforelse
            </div>
            <div>
                <h2>Delivery Information</h2>
                <p>{{ $product['delivery_label'] }}. Delivery and order updates are available through order tracking.</p>
                <a class="product-card__link" href="{{ route('policies.shipping') }}">Shipping and delivery policy</a>
                <div class="payment-icons payment-icons--light" aria-label="Accepted payment symbols">
                    <span><img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay"></span>
                    <span><img src="{{ asset('assets/payments/upi.svg') }}" alt="UPI"></span>
                    <span><img src="{{ asset('assets/payments/visa.svg') }}" alt="Visa"></span>
                    <span><img src="{{ asset('assets/payments/mastercard.svg') }}" alt="Mastercard"></span>
                    <span><img src="{{ asset('assets/payments/rupay.svg') }}" alt="RuPay"></span>
                </div>
            </div>
            <div>
                <h2>Return Summary</h2>
                <p>{{ $product['seller_return_policy'] }}</p>
                <a class="product-card__link" href="{{ route('policies.return-refund') }}">View Full Return Policy</a>
            </div>
        </section>

        <section class="section-block">
            <div class="section-heading">
                <p class="eyebrow">Related Products</p>
                <h2>Complete the look</h2>
            </div>
            <div class="product-grid">
                @foreach ($relatedProducts as $related)
                    <x-product.card :product="$related" compact />
                @endforeach
            </div>
        </section>
    </section>
</x-layouts.customer>
