@props([
    'href' => route('home'),
    'loading' => 'eager',
    'context' => 'default',
    'class' => '',
])

@php
    $sizeClass = match ($context) {
        'admin' => 'brand-logo--admin',
        'auth' => 'brand-logo--auth',
        'footer' => 'brand-logo--footer',
        'large' => 'brand-logo--large',
        default => 'brand-logo',
    };

    $frameClass = match ($context) {
        'admin' => 'brand-logo-frame--admin',
        'auth' => 'brand-logo-frame--auth',
        'footer' => 'brand-logo-frame--footer',
        'large' => 'brand-logo-frame--large',
        default => 'brand-logo-frame',
    };
@endphp

<a href="{{ $href }}" class="brand-logo-link {{ $frameClass }} {{ $class }}" aria-label="Sushako Shopping home">
    <picture>
        <source srcset="{{ asset('images/brand/sushako-shopping-logo-optimized.webp') }}" type="image/webp">
        <img
            src="{{ asset('images/brand/sushako-shopping-logo-optimized.png') }}"
            alt="Sushako Shopping Logo"
            class="brand-logo-image {{ $sizeClass }}"
            loading="{{ $loading }}"
            decoding="async"
            width="420"
            height="236"
        >
    </picture>
</a>
