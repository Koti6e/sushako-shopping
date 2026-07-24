@props(['title' => 'Sushako Admin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/brand/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">
    <link rel="preload" as="image" href="{{ asset('images/brand/sushako-shopping-logo-optimized.webp') }}" type="image/webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <title>{{ $title ?? 'Sushako Admin' }}</title>
    <script>
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
            <picture>
                <source srcset="{{ asset('images/brand/sushako-shopping-logo-optimized.webp') }}" type="image/webp">
                <img class="page-loader__logo" src="{{ asset('images/brand/sushako-shopping-logo-optimized.png') }}" alt="Sushako Shopping" width="420" height="236" decoding="async">
            </picture>
            <span></span>
        </div>
    </div>
    {{ $slot }}
</body>
</html>
