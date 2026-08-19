@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'badge' => null,
    'breadcrumbs' => null,
    'showSubtitle' => false,
])

<section class="admin-shell">
    <x-admin.sidebar />
    <main class="admin-main">
        <x-admin.header :eyebrow="$eyebrow" :title="$title" :subtitle="$subtitle" :badge="$badge" :breadcrumbs="$breadcrumbs" :show-subtitle="$showSubtitle">
            @if (isset($actions))
                <x-slot:actions>{{ $actions }}</x-slot:actions>
            @endif
        </x-admin.header>
        {{ $slot }}
    </main>
</section>
