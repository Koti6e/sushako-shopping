<x-layouts.customer title="Login - Sushako Shopping">
    <section class="auth-screen auth-screen--customer">
        <div class="auth-portal">
            <aside class="auth-visual auth-visual--customer">
                <x-brand.logo context="auth" loading="eager" />
                <div>
                    <p class="eyebrow">Customer Lounge</p>
                    <h1>Step back into your Sushako shopping suite.</h1>
                    <p class="lede">Saved addresses, order history, invoices and premium checkout are ready when you sign in.</p>
                </div>
                <div class="auth-visual-points">
                    <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Saved delivery addresses</span>
                    <span><i class="fa-solid fa-receipt" aria-hidden="true"></i> Order invoices</span>
                    <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp support</span>
                </div>
            </aside>

            <form class="auth-panel auth-panel--elevated" method="POST" action="{{ route('login.store') }}">
                @csrf
                <x-brand.logo context="auth" loading="eager" />
                <p class="eyebrow">Customer Login</p>
                <h1>Continue to Sushako Shopping</h1>
                <p class="auth-panel-copy">Use email login or continue securely with Google.</p>
                @if ($errors->any())
                    <div class="status-banner">{{ $errors->first() }}</div>
                @endif
                <x-google-auth-button :href="route('login.google')" />
                <div class="auth-divider"><span>or sign in with email</span></div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
                <label class="policy-agreement policy-agreement--compact">
                    <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    <span>Keep me signed in on this device.</span>
                </label>
                <button type="submit" class="button button--primary">Sign In</button>
                <a href="{{ route('register') }}" class="button button--secondary">Create Customer Account</a>
            </form>
        </div>
    </section>
</x-layouts.customer>
