@props(['rows' => 3])

<div {{ $attributes->merge(['class' => 'ui-skeleton-stack']) }} aria-hidden="true">
    @for ($i = 0; $i < $rows; $i++)
        <span class="ui-skeleton"></span>
    @endfor
</div>
