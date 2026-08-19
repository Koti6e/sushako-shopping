@props([
    'name',
    'label' => 'Choose file',
    'hint' => 'PNG, JPG or WebP.',
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'id' => null,
    'icon' => 'fa-solid fa-cloud-arrow-up',
])

@php
    $inputId = $id ?: 'file-upload-'.str($name)->replace(['[', ']'], '-')->slug('-').'-'.uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'ui-file-upload']) }} data-ui-file-upload>
    <input
        id="{{ $inputId }}"
        class="ui-file-upload__input"
        type="file"
        name="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @if ($multiple) multiple @endif
        @required($required)
        data-ui-file-input
    >
    <label class="ui-file-upload__dropzone" for="{{ $inputId }}" data-ui-file-trigger>
        <span class="ui-file-upload__icon"><i class="{{ $icon }}" aria-hidden="true"></i></span>
        <span class="ui-file-upload__copy">
            <strong>{{ $label }}</strong>
            <small>{{ $hint }}</small>
        </span>
        <span class="ui-file-upload__button">Choose</span>
    </label>
    <div class="ui-file-upload__list" data-ui-file-list hidden></div>
</div>
