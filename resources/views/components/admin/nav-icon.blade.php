@props(['name'])

@php
    $paths = [
        'dashboard' => '<path d="M4 13a8 8 0 1 1 16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5Z"/><path d="m12 13 3-4"/><path d="M8 17h8"/>',
        'products' => '<path d="M5 8.5 12 4l7 4.5v7L12 20l-7-4.5v-7Z"/><path d="m5 8.5 7 4.5 7-4.5"/><path d="M12 13v7"/>',
        'inventory' => '<path d="M4 9h16v11H4z"/><path d="M6 9V5h12v4"/><path d="M9 13h6"/>',
        'orders' => '<path d="M7 3.5h10v17l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2v-17Z"/><path d="M10 8h4"/><path d="M10 12h4"/><path d="M10 16h2"/>',
        'labels' => '<path d="M4 7V4h3"/><path d="M17 4h3v3"/><path d="M20 17v3h-3"/><path d="M7 20H4v-3"/><path d="M8 8h8v8H8z"/><path d="M10 11h4"/>',
        'customers' => '<path d="M16 20a4 4 0 0 0-8 0"/><path d="M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M20 19a3 3 0 0 0-3-3"/><path d="M17 7a2.5 2.5 0 0 1 0 5"/>',
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19 12a7.5 7.5 0 0 0-.1-1l2-1.5-2-3.4-2.4 1a8 8 0 0 0-1.8-1L14.4 3h-4.8l-.3 3.1a8 8 0 0 0-1.8 1l-2.4-1-2 3.4 2 1.5a7.5 7.5 0 0 0 0 2l-2 1.5 2 3.4 2.4-1a8 8 0 0 0 1.8 1l.3 3.1h4.8l.3-3.1a8 8 0 0 0 1.8-1l2.4 1 2-3.4-2-1.5c.1-.3.1-.7.1-1Z"/>',
        'storefront' => '<path d="M5 10h14l-1-5H6l-1 5Z"/><path d="M6 10v10h12V10"/><path d="M9 20v-5h6v5"/><path d="M4 10a2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0"/>',
        'logout' => '<path d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/><path d="M14 16l4-4-4-4"/><path d="M18 12H9"/>',
        'chevron' => '<path d="m6 9 6 6 6-6"/>',
        'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
        'close' => '<path d="m6 6 12 12"/><path d="M18 6 6 18"/>',
        'categories' => '<path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 12 8 4 8-4"/><path d="m4 17 8 4 8-4"/>',
    ];
@endphp

<svg class="admin-nav-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    {!! $paths[$name] ?? '<path d="M12 12h.01"/>' !!}
</svg>
