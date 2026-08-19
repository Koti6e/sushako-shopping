@props(['id', 'title'])

<section id="{{ $id }}" class="ui-modal" data-ui-modal aria-hidden="true">
    <div class="ui-modal__overlay" data-ui-modal-close></div>
    <div class="ui-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <header>
            <h2 id="{{ $id }}-title">{{ $title }}</h2>
            <button type="button" data-ui-modal-close aria-label="Close modal"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </header>
        {{ $slot }}
    </div>
</section>
