@props(['status' => 'default'])

@php
    $tone = str($status)->lower()->replace(' ', '-')->replace('_', '-')->toString();
@endphp

<span {{ $attributes->merge(['class' => 'ui-badge ui-badge--'.$tone]) }}>
    {{ $slot->isEmpty() ? str($status)->replace('_', ' ')->title() : $slot }}
</span>
