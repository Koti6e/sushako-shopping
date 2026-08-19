@props([
    'label' => 'More',
])

<details class="admin-action-menu" data-admin-action-menu>
    <summary aria-haspopup="menu">
        <span>{{ $label }}</span>
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
    </summary>
    <div class="admin-action-menu__panel" role="menu">
        {{ $slot }}
    </div>
</details>
