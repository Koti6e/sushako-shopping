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
                        {{ $product['seller_return_policy'] }}
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
                <p>{{ $product['seller_return_policy'] }}. Sushako protection still applies for wrong, damaged, missing, counterfeit, fraudulent, or undelivered orders.</p>
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
