@props([
    'src' => null,
    'alt' => 'Product image',
    'class' => null,
    'loading' => 'lazy',
    'fallbackClass' => 'product-no-preview',
    'category' => null,
])
@php
    $categoryKey = str($category ?: 'product')->lower()->slug('-')->toString();
    $placeholderUrl = asset('images/sushako-product-placeholder.png');
@endphp

@if (filled($src))
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $loading }}"
        @class([$class])
        onerror="this.onerror=null; this.hidden=true; this.nextElementSibling.hidden=false;"
    >
@endif

<span
    class="{{ $fallbackClass }} product-no-preview--intentional"
    data-fallback-category="{{ $categoryKey }}"
    @if (filled($src)) hidden @endif
>
    <img
        src="{{ $placeholderUrl }}"
        alt=""
        aria-hidden="true"
        loading="lazy"
        draggable="false"
        style="display:block;width:100%;height:100%;object-fit:contain;"
        onerror="this.onerror=null; this.style.display='none';"
    >
</span>
