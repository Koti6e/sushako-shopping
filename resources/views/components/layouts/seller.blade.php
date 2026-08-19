@props(['title' => 'Seller Portal', 'minimal' => false])
@php
    $vendor = request()->attributes->get('vendor') ?? auth()->user()?->vendor;
    $sidebarData = $vendor ? app(\App\Services\SellerSidebarService::class)->data($vendor) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Sushako Seller</title>
    <script>
        (() => {
            if (localStorage.getItem('sushakoSellerSidebar') === 'collapsed') {
                document.documentElement.dataset.sellerSidebar = 'collapsed';
            }
        })();
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/brand/sushako-shopping-official-favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/brand/sushako-shopping-official-favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/sushako-shopping-official-apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="seller-body seller-body--elite">
    <div class="seller-mobile-backdrop" data-seller-backdrop hidden></div>
    <section @class(['seller-shell', 'seller-shell--elite', 'seller-shell--portal' => ! $minimal, 'seller-shell--onboarding' => $minimal])>
        @if (! $minimal && $sidebarData)
            <x-seller.sidebar :data="$sidebarData" />
        @endif
        <main @class(['seller-main', 'seller-main--portal' => ! $minimal, 'seller-main--onboarding' => $minimal])>
            {{ $slot }}
        </main>
    </section>
    @auth
        @unless ($minimal)
        <x-seller.mobile-nav />
        @endunless
    @endauth
    <script>
        (() => {
            const sidebar = document.querySelector('[data-seller-sidebar]');
            const backdrop = document.querySelector('[data-seller-backdrop]');
            if (!sidebar) return;
            const collapseButtons = document.querySelectorAll('[data-sidebar-collapse]');

            const syncCollapseButtons = () => {
                const collapsed = document.documentElement.dataset.sellerSidebar === 'collapsed';
                collapseButtons.forEach((button) => {
                    button.setAttribute('aria-label', collapsed ? 'Expand seller sidebar' : 'Collapse seller sidebar');
                    button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                });
            };

            const closeMenus = () => {
                document.querySelectorAll('[data-quick-action-menu], [data-account-menu]').forEach((menu) => menu.hidden = true);
                document.querySelectorAll('[data-quick-action-trigger], [data-account-trigger]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
            };

            const closeDrawer = () => {
                document.body.classList.remove('seller-drawer-open');
                if (backdrop) backdrop.hidden = true;
                document.body.style.overflow = '';
            };

            document.addEventListener('click', (event) => {
                const quickTrigger = event.target.closest('[data-quick-action-trigger]');
                const accountTrigger = event.target.closest('[data-account-trigger]');
                const collapse = event.target.closest('[data-sidebar-collapse]');
                const mobileOpen = event.target.closest('[data-seller-menu-open]');

                if (quickTrigger) {
                    const menu = document.querySelector('[data-quick-action-menu]');
                    const open = menu?.hidden;
                    closeMenus();
                    if (menu) menu.hidden = !open;
                    quickTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                    return;
                }

                if (accountTrigger) {
                    const menu = document.querySelector('[data-account-menu]');
                    const open = menu?.hidden;
                    closeMenus();
                    if (menu) menu.hidden = !open;
                    accountTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                    return;
                }

                if (collapse) {
                    const collapsed = document.documentElement.dataset.sellerSidebar === 'collapsed';
                    document.documentElement.dataset.sellerSidebar = collapsed ? 'expanded' : 'collapsed';
                    localStorage.setItem('sushakoSellerSidebar', collapsed ? 'expanded' : 'collapsed');
                    syncCollapseButtons();
                    return;
                }

                if (mobileOpen) {
                    document.body.classList.add('seller-drawer-open');
                    if (backdrop) backdrop.hidden = false;
                    document.body.style.overflow = 'hidden';
                    return;
                }

                if (event.target.closest('[data-seller-backdrop]')) {
                    closeMenus();
                    closeDrawer();
                    return;
                }

                if (!event.target.closest('[data-seller-sidebar]')) {
                    closeMenus();
                }
            });

            sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 900px)').matches) closeDrawer();
            }));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenus();
                    closeDrawer();
                }
            });

            syncCollapseButtons();
        })();
    </script>
</body>
</html>
