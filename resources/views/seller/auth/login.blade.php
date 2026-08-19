@php
    $mode = $mode ?? 'login';
    $isLogin = $mode === 'login';
    $isRegister = $mode === 'register';
    $isForgot = $mode === 'forgot';
    $isReset = $mode === 'reset';
    $title = match ($mode) {
        'register' => 'Create Seller Account',
        'forgot' => 'Seller Password Help',
        'reset' => 'Reset Seller Password',
        default => 'Seller Login',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sign in to the Sushako Seller Hub to manage products, orders, plans, and settlements.">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/brand/sushako-shopping-official-favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/brand/sushako-shopping-official-favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/sushako-shopping-official-apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="preload" as="image" href="{{ asset('assets/brand/sushako-shopping-official-horizontal.webp') }}" type="image/webp">
    <link rel="preload" as="image" href="{{ asset('assets/auth/seller-commerce-suite.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <title>{{ $title }} - Sushako Shopping</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="seller-auth-body">
    <main class="seller-auth-page">
        <section class="seller-auth-shell" aria-label="{{ $title }}">
            <div class="seller-auth-story">
                <x-brand.sushako-shopping-logo class="seller-auth-brand" variant="full" theme="light" size="lg" loading="eager" />

                <div class="seller-auth-copy">
                    <p class="seller-auth-eyebrow">Sushako Seller Platform</p>
                    <h1>PARTNER. GROW.<br>SUCCEED.</h1>
                    <p>Welcome to the Sushako Seller Hub.<br>Your command center for digital commerce.</p>
                </div>

                <img class="seller-auth-illustration" src="{{ asset('assets/auth/seller-commerce-suite.svg') }}" alt="Seller analytics dashboard, storefront, and growth chart illustration" width="720" height="520">

                <div class="seller-auth-proof" aria-label="Seller platform highlights">
                    <span><i class="fa-solid fa-store" aria-hidden="true"></i> Storefront</span>
                    <span><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Growth</span>
                    <span><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i> Orders</span>
                </div>
            </div>

            <div class="seller-auth-panel">
                <form
                    class="seller-auth-card"
                    method="POST"
                    action="{{ $isRegister ? route('seller.register.store') : ($isForgot ? route('seller.password.email') : ($isReset ? route('seller.password.update') : route('seller.login.store'))) }}"
                    data-auth-submit
                    novalidate
                >
                    @csrf
                    @if ($isReset)
                        <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    @endif

                    <header class="seller-auth-card__header">
                        <p class="seller-auth-eyebrow">{{ $isRegister ? 'Seller Registration' : ($isForgot || $isReset ? 'Seller Password' : 'Secure Access') }}</p>
                        <h2>{{ $title }}</h2>
                        <p>
                            @if ($isRegister)
                                Start with a secure seller account, then complete onboarding in the Seller Hub.
                            @elseif ($isForgot)
                                Enter your seller email and we will send reset instructions if the account is active.
                            @elseif ($isReset)
                                Create a new password for your seller account.
                            @else
                                Sign in to manage products, orders, settlements, and store readiness.
                            @endif
                        </p>
                    </header>

                    @if (session('status'))
                        <div class="seller-auth-alert seller-auth-alert--success" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="seller-auth-alert seller-auth-alert--error" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if ($isRegister)
                        <label class="seller-auth-field" for="seller-name">
                            <span>Full name</span>
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input id="seller-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Your name">
                        </label>
                    @endif

                    <label class="seller-auth-field" for="seller-email">
                        <span>Email</span>
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        <input id="seller-email" type="email" name="email" value="{{ old('email', $email ?? '') }}" required autocomplete="email" placeholder="seller@example.com">
                    </label>

                    @if (! $isForgot)
                        <label class="seller-auth-field" for="seller-password">
                            <span>Password</span>
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="seller-password" type="password" name="password" required autocomplete="{{ $isLogin ? 'current-password' : 'new-password' }}" placeholder="Enter password">
                            <button class="seller-auth-password-toggle" type="button" data-password-toggle aria-label="Show password" aria-controls="seller-password">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </label>
                    @endif

                    @if ($isRegister || $isReset)
                        <label class="seller-auth-field" for="seller-password-confirmation">
                            <span>Confirm password</span>
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            <input id="seller-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password">
                        </label>
                    @endif

                    @if ($isLogin)
                        <div class="seller-auth-row">
                            <label class="seller-auth-check">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                <span>Remember me</span>
                            </label>
                            <a href="{{ route('seller.password.request') }}">Forgot Password?</a>
                        </div>
                    @endif

                    @if ($isRegister)
                        <label class="seller-auth-check seller-auth-check--terms">
                            <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
                            <span>I agree to create a seller account for Sushako Shopping.</span>
                        </label>
                    @endif

                    <button class="seller-auth-primary" type="submit" data-auth-primary>
                        <span>{{ $isRegister ? 'Create Seller Account' : ($isForgot ? 'Send Reset Link' : ($isReset ? 'Reset Password' : 'Login')) }}</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>

                    @if (! $isForgot && ! $isReset)
                        <div class="seller-auth-divider"><span>or</span></div>
                        <x-google-auth-button :href="route('seller.auth.google')" />
                    @endif

                    <footer class="seller-auth-card__footer">
                        @if ($isLogin)
                            <span>New to Sushako?</span>
                            <a href="{{ route('seller.register') }}">Create Seller Account</a>
                        @elseif ($isRegister)
                            <span>Already selling?</span>
                            <a href="{{ route('seller.login') }}">Login to Seller Hub</a>
                        @else
                            <span>Remember your password?</span>
                            <a href="{{ route('seller.login') }}">Back to Seller Login</a>
                        @endif
                    </footer>

                    <a class="seller-auth-back" href="{{ route('home') }}">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        Back to Sushako Shopping
                    </a>
                </form>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.getAttribute('aria-controls'));
                const icon = button.querySelector('i');
                const show = input?.type === 'password';
                if (!input) return;
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                icon?.classList.toggle('fa-eye', !show);
                icon?.classList.toggle('fa-eye-slash', show);
            });
        });

        document.querySelectorAll('[data-auth-submit]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('[data-auth-primary]');
                if (!button) return;
                button.disabled = true;
                button.classList.add('is-loading');
                button.querySelector('span').textContent = 'Please wait';
            });
        });

    </script>
</body>
</html>
