@props([
    'href' => null,
    'type' => 'button',
    'tone' => 'secondary',
    'icon' => null,
    'disabled' => false,
])

@php
    $classes = "admin-action admin-action--{$tone}";
@endphp

@if ($href)
    <a {{ $attributes->merge(['class' => $classes, 'href' => $href]) }}>
        @if ($icon)
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => $type]) }} @disabled($disabled)>
        @if ($icon)
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
