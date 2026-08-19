@props(['title' => 'Sushako Admin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/brand/sushako-shopping-official-favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/brand/sushako-shopping-official-favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/sushako-shopping-official-apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="preload" as="image" href="{{ asset('assets/brand/sushako-shopping-official-horizontal.webp') }}" type="image/webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <title>{{ $title ?? 'Sushako Admin' }}</title>
    <script>
        (() => {
            if (localStorage.getItem('sushakoAdminSidebar') === 'collapsed') {
                document.documentElement.dataset.adminSidebar = 'collapsed';
            }
        })();
        document.documentElement.classList.add('page-loading');
        window.sushakoPageReady = function () {
            document.documentElement.classList.remove('page-loading', 'page-transitioning');
            document.body?.classList.add('page-ready');
        };
        document.addEventListener('DOMContentLoaded', window.sushakoPageReady, { once: true });
        setTimeout(window.sushakoPageReady, 800);
    </script>
    <style>
        html.page-loading,
        html.page-transitioning {
            cursor: wait;
        }

        html.page-loading body,
        html.page-transitioning body {
            overflow: hidden;
        }

        html.page-loading body > :not(.page-loader),
        html.page-transitioning body > :not(.page-loader) {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 2147483647;
            display: grid;
            place-items: center;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(180deg, #fffdf8, #f8f5ef);
        }

        .page-loader__mark {
            display: grid;
            justify-items: center;
            gap: 20px;
            width: min(300px, calc(100vw - 48px));
            padding: 30px 28px;
            border: 1px solid #e8e1d6;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 24px 70px rgba(20, 33, 61, 0.12);
        }

        .page-loader__logo {
            display: block;
            width: 176px;
            height: 58px;
            object-fit: contain;
            filter: drop-shadow(0 18px 42px rgba(20, 33, 61, 0.12));
        }

        .page-loader__mark > span {
            width: 168px;
            height: 2px;
            overflow: hidden;
            background: rgba(197, 154, 61, 0.24);
        }

        .page-loader__mark > span::before {
            content: "";
            display: block;
            width: 46%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #c59a3d, transparent);
            animation: premium-loader 1.15s ease-in-out infinite;
        }

        @keyframes premium-loader {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(240%);
            }
        }

        html:not(.page-loading):not(.page-transitioning) .page-loader {
            display: none;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="page-loader" data-page-loader aria-hidden="true">
        <div class="page-loader__mark">
            <img class="page-loader__logo" src="{{ asset('assets/brand/sushako-shopping-official-horizontal.webp') }}" alt="Sushako Shopping" width="760" height="220" decoding="async">
            <span></span>
        </div>
    </div>
    {{ $slot }}
    @auth
        <x-admin.mobile-nav />
    @endauth
    <script>
        (() => {
            const sidebar = document.querySelector('[data-admin-sidebar]');
            const backdrop = document.querySelector('[data-admin-sidebar-close].admin-control-backdrop, [data-admin-sidebar-close].admin-sidebar-backdrop');
            const accountMenu = document.querySelector('[data-admin-account-menu]');
            const accountTrigger = document.querySelector('[data-admin-account-trigger]');
            const collapseButtons = document.querySelectorAll('[data-admin-sidebar-collapse]');

            const syncCollapseButtons = () => {
                const collapsed = document.documentElement.dataset.adminSidebar === 'collapsed';
                collapseButtons.forEach((button) => {
                    button.setAttribute('aria-label', collapsed ? 'Expand admin sidebar' : 'Collapse admin sidebar');
                    button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                });
            };

            const closeAccount = () => {
                if (accountMenu) accountMenu.hidden = true;
                if (accountTrigger) accountTrigger.setAttribute('aria-expanded', 'false');
            };

            const closeDrawer = () => {
                document.body.classList.remove('admin-sidebar-open');
                document.body.style.overflow = '';
                document.querySelectorAll('[data-admin-sidebar-close]').forEach((button) => {
                    if (button.classList.contains('admin-sidebar-backdrop')) button.hidden = true;
                });
            };

            document.addEventListener('click', (event) => {
                const open = event.target.closest('[data-admin-sidebar-open]');
                const close = event.target.closest('[data-admin-sidebar-close]');
                const collapse = event.target.closest('[data-admin-sidebar-collapse]');
                const account = event.target.closest('[data-admin-account-trigger]');

                if (open) {
                    document.body.classList.add('admin-sidebar-open');
                    document.querySelectorAll('[data-admin-sidebar-close]').forEach((button) => {
                        if (button.classList.contains('admin-sidebar-backdrop')) button.hidden = false;
                    });
                    document.body.style.overflow = 'hidden';
                    return;
                }

                if (close) {
                    closeAccount();
                    closeDrawer();
                    return;
                }

                if (collapse) {
                    const collapsed = document.documentElement.dataset.adminSidebar === 'collapsed';
                    document.documentElement.dataset.adminSidebar = collapsed ? 'expanded' : 'collapsed';
                    localStorage.setItem('sushakoAdminSidebar', collapsed ? 'expanded' : 'collapsed');
                    syncCollapseButtons();
                    return;
                }

                if (account) {
                    const shouldOpen = accountMenu?.hidden;
                    closeAccount();
                    if (accountMenu) accountMenu.hidden = !shouldOpen;
                    account.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                    return;
                }

                if (sidebar && ! event.target.closest('[data-admin-sidebar]')) {
                    closeAccount();
                }
            });

            sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 980px)').matches) closeDrawer();
            }));

            sidebar?.querySelectorAll('.admin-control-nav details').forEach((details) => {
                details.addEventListener('toggle', () => {
                    if (!details.open) return;
                    sidebar.querySelectorAll('.admin-control-nav details[open]').forEach((openDetails) => {
                        if (openDetails !== details) openDetails.open = false;
                    });
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAccount();
                    closeDrawer();
                }
            });

            syncCollapseButtons();
        })();
    </script>
</body>
</html>
