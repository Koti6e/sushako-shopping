<x-layouts.admin title="Admin Login - Sushako Shopping">
    <section class="admin-auth-screen admin-auth-screen--operations">
        <div class="auth-portal auth-portal--admin">
            <aside class="auth-visual auth-visual--admin">
                <x-brand.sushako-shopping-logo href="{{ route('admin.login') }}" variant="full" theme="dark" size="lg" loading="eager" />
                <div>
                    <p class="eyebrow">Operations Control</p>
                    <h1>Super admin command center for live store movement.</h1>
                    <p class="lede">Monitor orders, inventory, customers and manual fulfillment from one protected workspace.</p>
                </div>
                <div class="admin-ops-signal-grid">
                    <span><strong>Orders</strong> Live queue</span>
                    <span><strong>Inventory</strong> Action ready</span>
                    <span><strong>Support</strong> WhatsApp intent</span>
                    <span><strong>Shipping</strong> Manual flow</span>
                </div>
            </aside>

            <form class="admin-login-panel admin-login-panel--elevated" method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <x-brand.sushako-shopping-logo href="{{ route('admin.login') }}" variant="full" theme="light" size="lg" loading="eager" />
                <p class="eyebrow">Super Admin Login</p>
                <h1>Manage Sushako Shopping</h1>
                <p class="auth-panel-copy">Restricted access for store operations, orders and fulfillment control.</p>
                @if ($errors->any())
                    <div class="status-banner">{{ $errors->first() }}</div>
                @endif
                <label for="admin-email">Email</label>
                <input id="admin-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                <label for="admin-password">Password</label>
                <input id="admin-password" name="password" type="password" required autocomplete="current-password">
                <label class="policy-agreement policy-agreement--compact">
                    <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    <span>Keep me signed in on this device.</span>
                </label>
                <button type="submit" class="button button--primary">Sign In</button>
            </form>
        </div>
    </section>
</x-layouts.admin>
