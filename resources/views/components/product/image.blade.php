@props([
    'src' => null,
    'alt' => 'Product image',
    'class' => null,
    'loading' => 'lazy',
    'fallbackClass' => 'product-no-preview',
])

@if (filled($src))
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $loading }}"
        @class([$class])
        onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
    >
    <span class="{{ $fallbackClass }}" hidden><i class="fa-regular fa-image" aria-hidden="true"></i> No Preview Available</span>
@else
    <span class="{{ $fallbackClass }}"><i class="fa-regular fa-image" aria-hidden="true"></i> No Preview Available</span>
@endif
