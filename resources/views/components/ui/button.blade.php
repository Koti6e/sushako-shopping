@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'icon' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $classes = 'ui-button ui-button--'.$variant;
@endphp

@if ($href)
    <a {{ $attributes->merge(['class' => $classes, 'href' => $href]) }} @if($disabled) aria-disabled="true" @endif>
        @if ($loading)<span class="ui-spinner" aria-hidden="true"></span>@endif
        @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => $type]) }} @disabled($disabled || $loading) @if($loading) aria-busy="true" @endif>
        @if ($loading)<span class="ui-spinner" aria-hidden="true"></span>@endif
        @if ($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
        <span>{{ $slot }}</span>
    </button>
@endif
