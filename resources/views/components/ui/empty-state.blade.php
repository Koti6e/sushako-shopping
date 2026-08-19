@props([
    'title' => 'No records found',
    'description' => 'Try changing the filters or create a new record.',
    'icon' => 'fa-regular fa-folder-open',
    'actionHref' => null,
    'actionLabel' => null,
])

<section {{ $attributes->merge(['class' => 'ui-empty-state']) }}>
    <span class="ui-empty-state__icon"><i class="{{ $icon }}" aria-hidden="true"></i></span>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    @if ($actionHref && $actionLabel)
        <x-ui.button :href="$actionHref" variant="primary">{{ $actionLabel }}</x-ui.button>
    @endif
</section>
