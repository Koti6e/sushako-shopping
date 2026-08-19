@props([
    'href' => route('home'),
    'loading' => 'eager',
    'context' => 'default',
    'class' => '',
])

@php
    $variant = match ($context) {
        'footer', 'large' => 'full',
        'compact' => 'compact',
        'icon' => 'icon',
        default => 'compact',
    };

    $size = match ($context) {
        'admin' => 'md',
        'auth' => 'lg',
        'footer' => 'sm',
        'large' => 'xl',
        default => 'md',
    };

    $theme = 'light';
@endphp

<x-brand.sushako-shopping-logo
    :href="$href"
    :variant="$variant"
    :theme="$theme"
    :size="$size"
    :loading="$loading"
    alt="Sushako Shopping"
    :class="$class"
/>
