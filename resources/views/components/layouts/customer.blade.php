@props(['title' => 'Sushako Shopping'])
@php
    $authUser = auth()->user();
    $accountHref = route('login');
    $accountLabel = 'Login';

    if ($authUser?->role === \App\Models\User::ROLE_CUSTOMER) {
        $accountHref = route('account.show');
        $accountLabel = $authUser->name ?: 'My Account';
    } elseif ($authUser?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN)) {
        $accountHref = route('admin.dashboard');
        $accountLabel = 'Admin Dashboard';
    }

    $departments = \App\Support\ProductCatalog::departments();
    $storeProducts = \App\Support\ProductCatalog::products();
    $navigationDepartments = $departments;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sushako Shopping - Premium own-store ecommerce with secure payments and support">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:title" content="{{ $title ?? 'Sushako Shopping' }}">
    <meta property="og:description" content="Premium multi-category shopping from Sushako with secure payments and support">
    <meta property="og:image" content="{{ asset('images/brand/sushako-shopping-logo-optimized.png') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/brand/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">
    <link rel="preload" as="image" href="{{ asset('images/brand/sushako-shopping-logo-optimized.webp') }}" type="image/webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <title>{{ $title ?? 'Sushako Shopping' }}</title>
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
<body class="customer-body">
    <div class="page-loader" data-page-loader aria-hidden="true">
        <div class="page-loader__mark">
            <picture>
                <source srcset="{{ asset('images/brand/sushako-shopping-logo-optimized.webp') }}" type="image/webp">
                <img class="page-loader__logo" src="{{ asset('images/brand/sushako-shopping-logo-optimized.png') }}" alt="Sushako Shopping" width="420" height="236" decoding="async">
            </picture>
            <span></span>
        </div>
    </div>
    <div class="announcement-bar" aria-label="Store announcements">
        <span>Same Day Dispatch Before 2 PM</span>
        <span>Free Shipping Above &#8377;999</span>
        <span>Secure Payments Powered by Razorpay</span>
        <span>Easy Returns</span>
        <span>Sushako Store Support</span>
    </div>
    <header class="site-header" data-surface="customer-header">
        <div class="site-shell site-header__inner">
            <x-brand.logo loading="eager" />
            <nav class="desktop-nav desktop-nav--mega" aria-label="Primary navigation">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('shop') }}">Products</a>
                @foreach ($navigationDepartments as $department)
                    @php
                        $popularProducts = $storeProducts->where('department_slug', $department['slug'])->take(3);
                    @endphp
                    <div class="mega-nav-item">
                        <a href="{{ route('department.show', $department['slug']) }}">{{ $department['name'] }}</a>
                        <div class="mega-menu" style="--department-accent: {{ $department['accent'] }}">
                            <div class="mega-menu__intro">
                                <img src="{{ $department['image'] }}" alt="{{ $department['name'] }} category" loading="lazy">
                                <p class="eyebrow">{{ $department['tagline'] }}</p>
                                <strong>{{ $department['headline'] }}</strong>
                                <span>{{ $department['description'] }}</span>
                                <small>{{ $department['offers'][0] ?? 'Marketplace deals available' }}</small>
                            </div>
                            <div class="mega-menu__columns">
                                <section>
                                    <h3>Featured Categories</h3>
                                    @foreach (array_slice($department['sidebar'], 0, 4) as $group => $items)
                                        <a href="{{ route('department.show', $department['slug']) }}?category={{ urlencode($group) }}">{{ $group }}</a>
                                    @endforeach
                                </section>
                                <section>
                                    <h3>Popular Products</h3>
                                    @forelse ($popularProducts as $product)
                                        <a href="{{ route('products.show', $product['slug']) }}">{{ $product['name'] }}</a>
                                    @empty
                                        <a href="{{ route('department.show', $department['slug']) }}">View department products</a>
                                    @endforelse
                                </section>
                                <section>
                                    <h3>Trending Brands</h3>
                                    @foreach (array_slice($department['brands'], 0, 5) as $brand)
                                        <a href="{{ route('department.show', $department['slug']) }}?brand={{ urlencode($brand) }}">{{ $brand }}</a>
                                    @endforeach
                                </section>
                                @foreach (array_slice($department['sidebar'], 0, 3) as $group => $items)
                                    <section>
                                        <h3>{{ $group }}</h3>
                                        @foreach ($items as $item)
                                            <a href="{{ route('department.show', $department['slug']) }}?category={{ urlencode($item) }}">{{ $item }}</a>
                                        @endforeach
                                    </section>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('search') }}" class="header-search">
                <label class="sr-only" for="site-search">Search</label>
                <input id="site-search" name="q" placeholder="Search products">
            </form>
            <nav class="header-actions" aria-label="Account navigation">
                @if ($storeProducts->isNotEmpty())
                    <form method="POST" action="{{ route('wishlist.toggle') }}" data-wishlist-form>
                        @csrf
                        <input type="hidden" name="slug" value="{{ $storeProducts->first()['slug'] }}">
                        <button type="submit" class="header-icon-button" aria-label="Wishlist" title="Wishlist">
                            <i class="fa-regular fa-heart" aria-hidden="true"></i>
                            <span class="action-badge" data-wishlist-count>{{ count(session('wishlist', [])) }}</span>
                        </button>
                    </form>
                @endif
                <a class="header-icon-button" href="{{ $accountHref }}" aria-label="{{ $accountLabel }}" title="{{ $accountLabel }}">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                </a>
                <a class="header-icon-button" href="{{ route('cart.empty') }}" aria-label="Shopping cart" title="Cart">
                    <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                    <span class="action-badge" data-cart-count>{{ count(session('cart', [])) }}</span>
                </a>
                @auth
                    @if ($authUser->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                        <a class="seller-header-link" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i><span>Admin Dashboard</span></a>
                    @endif
                @endauth
            </nav>
            <a class="mobile-header-action" href="{{ $accountHref }}" aria-label="{{ $accountLabel }}">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
            </a>
            <a class="mobile-header-action" href="{{ route('cart.empty') }}" aria-label="Open cart">
                <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                <span data-cart-count>{{ count(session('cart', [])) }}</span>
            </a>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <div class="toast" data-toast role="status" aria-live="polite">Added to Cart</div>

    <aside class="mini-cart" data-mini-cart aria-hidden="true">
        <div class="mini-cart__overlay" data-cart-close></div>
        <section class="mini-cart__panel" aria-label="Mini cart drawer">
            <header>
                <div>
                    <p class="eyebrow">Shopping Bag</p>
                    <h2>Added to Cart</h2>
                </div>
                <button type="button" data-cart-close aria-label="Close mini cart">Close</button>
            </header>
            <div class="mini-cart__items" data-mini-cart-items></div>
            <footer>
                <div class="mini-cart__subtotal">
                    <span>Subtotal</span>
                    <strong data-mini-cart-subtotal>&#8377;0</strong>
                </div>
                <button type="button" class="button button--secondary" data-cart-close>Continue Shopping</button>
                <a href="{{ route('checkout') }}" class="button button--primary">Checkout Now</a>
            </footer>
        </section>
    </aside>

    @php
        $whatsAppNumber = config('services.whatsapp.support_number');
        $whatsAppMessage = rawurlencode(config('services.whatsapp.support_message'));
    @endphp
    <a
        class="whatsapp-float"
        data-whatsapp-float
        href="https://wa.me/{{ $whatsAppNumber }}?text={{ $whatsAppMessage }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Need Help? Chat with us on WhatsApp"
    >
        <span>Need Help? Chat with us</span>
        <svg viewBox="0 0 32 32" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M16.02 3.2A12.67 12.67 0 0 0 5.1 22.26L3.2 29l6.9-1.82A12.68 12.68 0 1 0 16.02 3.2Zm0 2.2a10.48 10.48 0 0 1 8.93 15.95 10.47 10.47 0 0 1-13.87 3.74l-.47-.28-4.1 1.08 1.1-4-.31-.5A10.47 10.47 0 0 1 16.02 5.4Zm-4.16 5.5c-.25 0-.64.1-.97.46-.34.37-1.28 1.25-1.28 3.05 0 1.79 1.31 3.53 1.49 3.77.18.24 2.53 4.05 6.25 5.52 3.08 1.22 3.72.98 4.39.92.67-.06 2.16-.88 2.46-1.74.31-.86.31-1.6.22-1.75-.09-.15-.33-.24-.7-.43-.36-.18-2.16-1.06-2.49-1.18-.33-.13-.58-.18-.82.18-.24.37-.94 1.18-1.15 1.43-.21.24-.42.27-.79.09-.36-.18-1.54-.57-2.94-1.82-1.09-.97-1.82-2.17-2.03-2.54-.21-.36-.02-.56.16-.74.16-.16.36-.42.55-.64.18-.21.24-.36.36-.61.12-.24.06-.46-.03-.64-.09-.18-.82-1.98-1.12-2.71-.3-.71-.6-.61-.82-.62h-.74Z"/>
        </svg>
    </a>

    <x-cookie-consent-banner />

    <footer class="site-footer" data-surface="customer-footer">
        <section class="footer-newsletter" aria-label="Stay updated">
            <div class="site-shell footer-newsletter__inner">
                <div>
                    <p class="eyebrow">Stay Updated</p>
                    <h2>Get New Arrivals, Offers & Store Updates</h2>
                </div>
                <form>
                    <label class="sr-only" for="footer-newsletter-email">Email</label>
                    <input id="footer-newsletter-email" type="email" placeholder="Email">
                    <button class="button button--primary" type="submit">Subscribe</button>
                </form>
            </div>
        </section>
        <div class="site-shell store-footer">
            <section class="footer-brand-column">
                <x-brand.logo context="large" loading="lazy" />
                <p>Premium own-store ecommerce for curated products across India.</p>
                <div class="footer-socials" aria-label="Social links">
                    <a href="#facebook" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="#instagram" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                    <a href="#linkedin" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
                    <a href="#youtube" aria-label="YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
                </div>
            </section>
            <nav aria-label="Store department footer links">
                <h2><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Departments</h2>
                @foreach ($departments as $department)
                    <a href="{{ route('department.show', $department['slug']) }}">{{ $department['name'] }}</a>
                @endforeach
                <a href="{{ route('shop') }}?category=New+Arrivals">New Arrivals</a>
            </nav>
            <nav aria-label="Customer footer links">
                <h2><i class="fa-regular fa-user" aria-hidden="true"></i> Customer</h2>
                @guest
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                @elseif ($authUser->role === \App\Models\User::ROLE_CUSTOMER)
                    <a href="{{ route('account.show') }}">My Account</a>
                @elseif ($authUser->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                @endguest
                @auth
                    @if ($authUser->role === \App\Models\User::ROLE_CUSTOMER)
                        <a href="{{ route('orders.track') }}">Orders</a>
                    @endif
                @endauth
                <a href="{{ route('shop') }}?wishlist=1">Wishlist</a>
                <a href="{{ route('cart.empty') }}">Cart</a>
                @auth
                    @if ($authUser->role === \App\Models\User::ROLE_CUSTOMER)
                        <a href="{{ route('checkout') }}#wallet">Wallet</a>
                        <a href="{{ route('orders.track') }}">Track Order</a>
                    @endif
                @endauth
            </nav>
            <nav aria-label="Store footer links">
                <h2><i class="fa-solid fa-store" aria-hidden="true"></i> Sushako Store</h2>
                <a href="{{ route('shop') }}">All Products</a>
                @foreach (array_slice($departments, 0, 3) as $department)
                    <a href="{{ route('department.show', $department['slug']) }}">{{ $department['name'] }}</a>
                @endforeach
            </nav>
            <nav aria-label="Support footer links">
                <h2><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Support</h2>
                <a href="#contact">Contact Us</a>
                <a href="#help">Help Center</a>
                <a href="#faq">FAQ</a>
                <a href="{{ route('policies.cookie') }}">Cookie Policy</a>
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms & Conditions</a>
                <a href="#shipping">Shipping Policy</a>
                <a href="{{ route('policies.return-refund') }}">Return Policy</a>
                <a href="#cancellation">Cancellation Policy</a>
            </nav>
        </div>
        <div class="site-shell footer-trust-row" aria-label="Store trust badges">
            <span><img src="{{ asset('assets/payments/razorpay.svg') }}" alt="Razorpay"> Secure Payments</span>
            <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Fast Dispatch</span>
            <span><img src="{{ asset('assets/payments/upi.svg') }}" alt="UPI"> UPI Ready</span>
            <span><i class="fa-solid fa-flag" aria-hidden="true"></i> Nationwide Delivery</span>
            <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp Support</span>
            <span><i class="fa-solid fa-star" aria-hidden="true"></i> Sushako Quality</span>
        </div>
        <div class="site-shell footer-bottom">
            <div class="payment-icons" aria-label="Payment methods">
                @foreach ([
                    'razorpay' => 'Razorpay',
                    'upi' => 'UPI',
                    'visa' => 'Visa',
                    'mastercard' => 'Mastercard',
                    'rupay' => 'RuPay',
                    'net-banking' => 'Net Banking',
                ] as $file => $label)
                    <span><img src="{{ asset('assets/payments/'.$file.'.svg') }}" alt="{{ $label }}"></span>
                @endforeach
            </div>
            <p>&copy; {{ date('Y') }} Sushako Shopping. All Rights Reserved. <span>Made with love in India.</span></p>
        </div>
    </footer>
</body>
</html>
