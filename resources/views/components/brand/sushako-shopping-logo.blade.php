@props([
    'href' => route('home'),
    'variant' => 'full',
    'theme' => 'light',
    'size' => 'md',
    'showSubtitle' => true,
    'alt' => 'Sushako Shopping',
    'loading' => 'lazy',
    'linked' => true,
])

@php
    $variant = in_array($variant, ['full', 'compact', 'icon', 'mono'], true) ? $variant : 'full';
    $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
    $size = in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'], true) ? $size : 'md';

    // The supplied crest is the canonical artwork. Keep one faithful raster source
    // across contexts so CSS controls sizing without introducing a redraw.
    $asset = 'assets/brand/sushako-shopping-crest-transparent.png';

    if ($variant === 'full' && ! $showSubtitle) {
        $variant = 'compact';
    }

    $dimensions = match ($variant) {
        'icon' => ['512', '512'],
        default => ['1254', '1254'],
    };

    $classes = trim('brand-logo-link brand-logo-link--'.$variant.' brand-logo-link--'.$theme.' brand-logo-link--'.$size.' '.$attributes->get('class'));
@endphp

@if ($linked)
    <a href="{{ $href }}" class="{{ $classes }}" aria-label="{{ $alt }} home">
        <img
            src="{{ asset($asset) }}"
            alt="{{ $alt }}"
            class="brand-logo-image"
            loading="{{ $loading }}"
            decoding="async"
            width="{{ $dimensions[0] }}"
            height="{{ $dimensions[1] }}"
        >
    </a>
@else
    <span class="{{ $classes }}" role="img" aria-label="{{ $alt }}">
        <img
            src="{{ asset($asset) }}"
            alt="{{ $alt }}"
            class="brand-logo-image"
            loading="{{ $loading }}"
            decoding="async"
            width="{{ $dimensions[0] }}"
            height="{{ $dimensions[1] }}"
        >
    </span>
@endif
