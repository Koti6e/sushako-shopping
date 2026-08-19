@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'helper' => null,
])

<label {{ $attributes->merge(['class' => 'ui-field']) }}>
    <span>{{ $label }}</span>
    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}">
    @error($name)<small class="ui-field__error">{{ $message }}</small>@else
        @if ($helper)<small>{{ $helper }}</small>@endif
    @enderror
</label>
