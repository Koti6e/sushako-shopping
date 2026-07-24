@php
    $defaultColour = array_key_first($product['colours']) ?: 'Standard';
    $defaultSize = $product['sizes'][0] ?? 'Standard';
    $defaultPrice = $product['variant_prices'][$defaultSize] ?? $product['selling_price'];
@endphp

<x-layouts.customer title="{{ $product['name'] }} - Sushako Shopping">
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
                    @foreach ($product['images'] as $image)
                        <figure class="product-gallery__slide @if($loop->first) is-active @endif" data-product-slide>
                            <img src="{{ $image['path'] }}" alt="{{ $product['name'] }} {{ $image['label'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                            <figcaption>
                                <span>{{ $image['label'] }}</span>
                                <strong>{{ $product['name'] }}</strong>
                            </figcaption>
                        </figure>
                    @endforeach
                    @if (count($product['images']) > 1)
                        <button class="product-gallery__control product-gallery__control--prev" type="button" data-product-gallery-prev aria-label="Previous image">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button class="product-gallery__control product-gallery__control--next" type="button" data-product-gallery-next aria-label="Next image">
                            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
                @if (count($product['images']) > 1)
                    <div class="product-gallery__thumbs" aria-label="Product image thumbnails">
                        @foreach ($product['images'] as $image)
                            <button class="@if($loop->first) is-active @endif" type="button" data-product-gallery-thumb aria-label="Show {{ $image['label'] }}">
                                <img src="{{ $image['path'] }}" alt="{{ $image['label'] }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product-buy-panel product-detail-card product-detail-card--buy">
                <p class="eyebrow">{{ $product['collection'] }}</p>
                <h1>{{ $product['name'] }}</h1>
                <p class="lede">{{ $product['short_description'] }}</p>
                <div class="product-meta-row">
                    <span>{{ $product['badge'] }}</span>
                    <span>{{ $product['rating'] }} stars</span>
                    <span>{{ $product['reviews'] }} reviews</span>
                    <span>{{ $product['stock_label'] }}</span>
                </div>
                <div class="price-row price-row--large">
                    <strong data-product-price>&#8377;{{ number_format($defaultPrice) }}</strong>
                    <s>&#8377;{{ number_format($product['mrp']) }}</s>
                    <span>{{ $product['discount'] }}% off</span>
                </div>

                @if ($product['has_colour_options'])
                    <fieldset>
                        <legend>Colour</legend>
                        <div class="swatch-row" data-product-colours>
                            @foreach ($product['colours'] as $colour => $meta)
                                <label>
                                    <input type="radio" name="colour" value="{{ $colour }}" @checked($loop->first)>
                                    <span style="--swatch: {{ $meta['hex'] }}"></span>
                                    {{ $colour }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                @if ($product['has_custom_options'])
                    <fieldset>
                        <legend>{{ $product['option_name'] }}</legend>
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
                    </fieldset>
                @endif

                @if ($product['has_size_options'])
                    <fieldset>
                        <legend>Size</legend>
                        <div class="size-row" data-product-sizes>
                            @foreach ($product['sizes'] as $size)
                                <label><input type="radio" name="size" value="{{ $size }}" @checked($loop->first)> {{ $size }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <label for="quantity">Quantity</label>
                <div class="quantity-row">
                    <input id="quantity" type="number" name="quantity" min="1" max="10" value="1">
                </div>

                <div class="buy-actions">
                    <button
                        class="button button--primary add-to-cart-button"
                        type="button"
                        data-add-to-cart
                        data-slug="{{ $product['slug'] }}"
                        data-colour="{{ $defaultColour }}"
                        data-size="{{ $defaultSize }}"
                        data-quantity-target="#quantity"
                    >Add to Cart</button>
                    <form class="buy-now-form" method="POST" action="{{ route('cart.buy-now') }}">
                        @csrf
                        <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                        <input type="hidden" name="colour" value="{{ $defaultColour }}" data-buy-now-colour>
                        <input type="hidden" name="size" value="{{ $defaultSize }}" data-buy-now-size>
                        <input type="hidden" name="quantity" value="1" data-buy-now-quantity>
                        <button class="button button--secondary" type="submit">Buy Now</button>
                    </form>
                    <button class="button button--ghost" type="button" data-wishlist data-slug="{{ $product['slug'] }}">Wishlist</button>
                    <button class="button button--ghost" type="button" data-share-url="{{ route('products.show', $product['slug']) }}" data-share-title="{{ $product['name'] }}">Share</button>
                    <a class="button button--secondary" href="https://wa.me/{{ config('services.whatsapp.support_number') }}?text={{ rawurlencode("Hello Sushako Shopping,\n\nI would like to enquire about:\n".$product['name']."\n".route('products.show', $product['slug'])."\nSelected Option: ".($product['has_colour_options'] ? $defaultColour : 'Standard').(($product['has_size_options'] || $product['has_custom_options']) ? ' / '.$defaultSize : '')."\nPrice: INR ".$defaultPrice) }}" target="_blank" rel="noopener noreferrer">WhatsApp Enquiry</a>
                </div>

                <div class="trust-grid">
                    <span class="trust-badge trust-badge--image">
                        <img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay">
                        Secure payments
                    </span>
                    <span class="trust-badge">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        WhatsApp support
                    </span>
                    <span class="trust-badge">
                        <i class="fa-solid fa-route" aria-hidden="true"></i>
                        Track order
                    </span>
                    <span class="trust-badge">
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        24 hour returns
                    </span>
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
                @foreach ($product['reviews_list'] as $review)
                    <blockquote>
                        <strong>{{ $review['name'] }} · {{ $review['rating'] }} stars</strong>
                        <p>{{ $review['text'] }}</p>
                    </blockquote>
                @endforeach
            </div>
            <div>
                <h2>Delivery Information</h2>
                <p>Same-day dispatch before 2 PM. Secure prepaid checkout powered by Razorpay. Free shipping above INR 999.</p>
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
                <p>Returns are accepted within 24 hours from delivery timestamp. Please record an unboxing video for quickest verification. Approved returns are credited to Sushako Wallet.</p>
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
                    <x-product.card :product="$related" />
                @endforeach
            </div>
        </section>
    </section>
</x-layouts.customer>
