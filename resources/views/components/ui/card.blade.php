@props(['title' => null, 'subtitle' => null, 'icon' => null, 'tone' => 'default'])

<section {{ $attributes->merge(['class' => 'ui-card ui-card--'.$tone]) }}>
    @if ($title || $subtitle || $icon)
        <header class="ui-card__header">
            @if ($icon)<span class="ui-card__icon"><i class="{{ $icon }}" aria-hidden="true"></i></span>@endif
            <div>
                @if ($title)<h2>{{ $title }}</h2>@endif
                @if ($subtitle)<p>{{ $subtitle }}</p>@endif
            </div>
        </header>
    @endif
    {{ $slot }}
</section>
