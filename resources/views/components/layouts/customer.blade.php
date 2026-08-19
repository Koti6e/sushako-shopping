@props(['title' => 'Sushako Shopping'])
@php
    $authUser = auth()->user();
    $accountHref = route('orders.track');
    $accountLabel = 'Track Order';
    $adminAccessHref = route('admin.login');

    if ($authUser?->role === \App\Models\User::ROLE_CUSTOMER) {
        $accountHref = route('account.show');
        $accountLabel = $authUser->name ?: 'My Account';
    } elseif ($authUser?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN)) {
        $accountHref = route('admin.dashboard');
        $accountLabel = 'Admin Dashboard';
        $adminAccessHref = route('admin.dashboard');
    } elseif ($authUser?->role === \App\Models\User::ROLE_SELLER) {
        $accountHref = route('seller.dashboard');
        $accountLabel = 'Seller Dashboard';
    }

    $departments = collect(\App\Support\ProductCatalog::departments());
    $storeProducts = collect(\App\Support\ProductCatalog::products());
    $navigationDepartments = $departments;
    $cartCount = collect(session('cart', []))->sum('quantity');
    $wishlistCount = count(session('wishlist', []));
    $popularSearches = $storeProducts->pluck('name')->take(5)->values();
    $sellerSearches = \App\Models\Vendor::query()
        ->where('store_status', \App\Models\Vendor::STORE_LIVE)
        ->where('store_visibility', \App\Models\Vendor::VISIBILITY_PUBLISHED)
        ->orderBy('store_display_name')
        ->limit(5)
        ->get(['store_display_name', 'business_name', 'slug']);
    $deliveryLocation = session('delivery_location');
    $deliveryLabel = data_get($deliveryLocation, 'label', 'Set location');
    $logoutRoute = $authUser?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN)
        ? route('admin.logout')
        : ($authUser?->role === \App\Models\User::ROLE_SELLER ? route('seller.logout') : route('logout'));
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
    <meta property="og:image" content="{{ asset('assets/brand/sushako-shopping-official-full.png') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/brand/sushako-shopping-official-favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/brand/sushako-shopping-official-favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/sushako-shopping-official-apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="preload" as="image" href="{{ asset('assets/brand/sushako-shopping-official-horizontal.webp') }}" type="image/webp">
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
            <img class="page-loader__logo" src="{{ asset('assets/brand/sushako-shopping-official-horizontal.webp') }}" alt="Sushako Shopping" width="760" height="220" decoding="async">
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
            <div class="site-header__left">
                <x-brand.logo loading="eager" />
                <nav class="desktop-nav desktop-nav--mega" aria-label="Primary navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('shop') }}">Products</a>
                    @if ($navigationDepartments->isNotEmpty())
                        <details class="site-nav-menu">
                            <summary>Categories <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
                            <div>
                                @foreach ($navigationDepartments->take(8) as $department)
                                    <a href="{{ route('department.show', $department['slug']) }}">{{ $department['name'] }}</a>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </nav>
            </div>
            <form method="GET" action="{{ route('search') }}" class="header-search header-search--enterprise" data-enterprise-search data-suggestions-url="{{ route('search.suggestions') }}">
                <label class="sr-only" for="site-search">Search</label>
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="site-search" name="q" value="{{ request('q') }}" placeholder="Search products, categories and stores" autocomplete="off" data-search-placeholder data-search-input>
                <button type="button" class="header-search__clear" data-search-clear aria-label="Clear search"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                <div class="header-search__panel" data-search-panel hidden>
                    <div class="header-search__state" data-search-state>Type at least 2 characters to search products, categories and stores.</div>
                    <div data-search-results></div>
                    <a class="header-search__view-all" href="{{ route('search') }}" data-search-view-all hidden>View All Results</a>
                </div>
            </form>
            <div class="site-header__right">
                <details class="delivery-location-selector" data-location-selector>
                    <summary>
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span><small>Delivering to</small><strong>{{ $deliveryLabel }}</strong></span>
                    </summary>
                    <div class="delivery-location-selector__panel">
                        <strong>Choose delivery location</strong>
                        <p>Allow location to discover nearby stores and products deliverable to your area. You can also enter it manually.</p>
                        <button type="button" data-location-gps><i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Use Current Location</button>
                        <form method="POST" action="{{ route('location.store') }}" data-location-manual>
                            @csrf
                            <input type="hidden" name="source" value="manual">
                            <label>Search City or Area<input name="area" value="{{ data_get($deliveryLocation, 'area') }}" placeholder="Chengalpattu"></label>
                            <label>Enter Pincode<input name="pincode" value="{{ data_get($deliveryLocation, 'pincode') }}" inputmode="numeric" maxlength="12" placeholder="603002"></label>
                            <button type="submit">Save Location</button>
                        </form>
                        @if ($deliveryLocation)
                            <form method="POST" action="{{ route('location.clear') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delivery-location-selector__clear">Clear Location</button>
                            </form>
                        @endif
                    </div>
                </details>
                <a class="header-track-link" href="{{ route('orders.track') }}">
                    <i class="fa-solid fa-route" aria-hidden="true"></i>
                    <span>Track Order</span>
                </a>
                <nav class="header-actions" aria-label="Account navigation">
                @if ($storeProducts->isNotEmpty())
                    <form method="POST" action="{{ route('wishlist.toggle') }}" data-wishlist-form>
                        @csrf
                        <input type="hidden" name="slug" value="{{ $storeProducts->first()['slug'] }}">
                        <button type="submit" class="header-icon-button" aria-label="Wishlist" title="Wishlist">
                            <i class="fa-regular fa-heart" aria-hidden="true"></i>
                            <span class="action-badge" data-wishlist-count>{{ $wishlistCount }}</span>
                        </button>
                    </form>
                @endif
                <details class="account-dropdown" data-account-dropdown>
                    <summary class="header-icon-button" aria-label="{{ $accountLabel }}" title="{{ $accountLabel }}">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                    </summary>
                    <div class="account-dropdown__panel">
                        @guest
                            <a href="{{ route('orders.track') }}"><i class="fa-solid fa-route" aria-hidden="true"></i> Track Order</a>
                            <a href="{{ route('shop') }}?wishlist=1"><i class="fa-regular fa-heart" aria-hidden="true"></i> Wishlist</a>
                            <a href="{{ route('seller.login') }}"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Become Seller</a>
                            <a href="#help"><i class="fa-regular fa-circle-question" aria-hidden="true"></i> Help</a>
                        @else
                            <a href="{{ $accountHref }}"><i class="fa-regular fa-user" aria-hidden="true"></i> {{ $authUser->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) ? 'Dashboard' : 'My Account' }}</a>
                            <a href="{{ route('orders.track') }}"><i class="fa-solid fa-box" aria-hidden="true"></i> Orders</a>
                            <a href="{{ route('shop') }}?wishlist=1"><i class="fa-regular fa-heart" aria-hidden="true"></i> Wishlist</a>
                            @if ($authUser->role === \App\Models\User::ROLE_CUSTOMER)
                                <a href="{{ route('account.show') }}#addresses"><i class="fa-regular fa-address-book" aria-hidden="true"></i> Saved Addresses</a>
                            @endif
                            <a href="#notifications"><i class="fa-regular fa-bell" aria-hidden="true"></i> Notifications</a>
                            <a href="{{ $accountHref }}#settings"><i class="fa-solid fa-gear" aria-hidden="true"></i> Settings</a>
                            <form method="POST" action="{{ $logoutRoute }}">
                                @csrf
                                <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Logout</button>
                            </form>
                        @endguest
                    </div>
                </details>
                <a class="header-icon-button" href="{{ route('cart.empty') }}" aria-label="Shopping cart" title="Cart">
                    <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                    <span class="action-badge" data-cart-count>{{ $cartCount }}</span>
                </a>
                @auth
                    @if ($authUser->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                        <a class="seller-header-link" href="{{ $adminAccessHref }}"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i><span>Admin Dashboard</span></a>
                    @endif
                @endauth
                </nav>
                <a class="header-track-link seller-header-link--storefront" href="{{ route('seller.login') }}">
                    <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    <span>Become a Seller</span>
                </a>
            </div>
            <a class="mobile-header-action mobile-header-action--account" href="{{ $accountHref }}" aria-label="{{ $accountLabel }}">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
                <span class="mobile-header-action__text">Account</span>
            </a>
            <a class="mobile-header-action mobile-header-action--orders" href="{{ route('orders.track') }}" aria-label="Track order">
                <i class="fa-solid fa-route" aria-hidden="true"></i>
                <span class="mobile-header-action__text">Track Order</span>
            </a>
            <a class="mobile-header-action mobile-header-action--seller" href="{{ route('seller.login') }}" aria-label="Become a seller">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                <span class="mobile-header-action__text">Become Seller</span>
            </a>
            <a class="mobile-header-action mobile-header-action--cart" href="{{ route('cart.empty') }}" aria-label="Open cart">
                <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                <span class="mobile-header-action__badge" data-cart-count>{{ $cartCount }}</span>
            </a>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <x-mobile-bottom-nav />

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

    <script>
        (() => {
            const gpsButton = document.querySelector('[data-location-gps]');
            gpsButton?.addEventListener('click', async () => {
                if (!navigator.geolocation) {
                    alert('Location is not supported by this browser. Please enter your city or pincode manually.');
                    return;
                }

                const ok = confirm('Allow location to discover nearby stores and products deliverable to your area. You can also enter your location manually.');
                if (!ok) return;

                gpsButton.disabled = true;
                gpsButton.textContent = 'Detecting...';

                navigator.geolocation.getCurrentPosition(async (position) => {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    await fetch(@json(route('location.store')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            source: 'gps',
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                        }),
                    });
                    window.location.reload();
                }, () => {
                    gpsButton.disabled = false;
                    gpsButton.textContent = 'Use Current Location';
                    alert('We could not access your location. Please enter your city or pincode manually.');
                }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 });
            });
        })();
    </script>

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
                    <a href="{{ route('orders.track') }}">Track Order</a>
                @elseif ($authUser->role === \App\Models\User::ROLE_CUSTOMER)
                    <a href="{{ route('account.show') }}">My Account</a>
                @elseif ($authUser->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                    <a href="{{ $adminAccessHref }}">Admin Dashboard</a>
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
                @foreach ($departments->take(3) as $department)
                    <a href="{{ route('department.show', $department['slug']) }}">{{ $department['name'] }}</a>
                @endforeach
            </nav>
            <nav aria-label="Support footer links">
                <h2><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Support</h2>
                <a href="#contact">Contact Us</a>
                <a href="#help">Help Center</a>
                <a href="#faq">FAQ</a>
                <a href="{{ route('policies.cookie') }}">Cookie Policy</a>
                <a href="{{ $adminAccessHref }}">Admin Login</a>
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
