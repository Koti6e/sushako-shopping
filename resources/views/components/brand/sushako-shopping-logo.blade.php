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

    $asset = match ($variant) {
        'icon' => 'assets/brand/sushako-shopping-official-icon.png',
        'compact' => 'assets/brand/sushako-shopping-official-horizontal.webp',
        'mono' => 'assets/brand/sushako-shopping-official-icon.png',
        default => 'assets/brand/sushako-shopping-official-full.webp',
    };

    if ($variant === 'full' && ! $showSubtitle) {
        $asset = 'assets/brand/sushako-shopping-official-horizontal.webp';
        $variant = 'compact';
    }

    $dimensions = match ($variant) {
        'icon' => ['512', '512'],
        'compact' => ['760', '220'],
        default => ['900', '675'],
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
