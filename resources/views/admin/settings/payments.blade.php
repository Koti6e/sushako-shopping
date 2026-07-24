<x-layouts.admin title="Payment Settings - Admin">
    <div class="admin-shell">
        @include('admin.settings.partials.sidebar')
        <main class="admin-main">
            <header class="admin-topbar admin-topbar--premium"><div><span>Settings</span><strong>Payment configuration</strong></div></header>
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                @if (session('status')) <div class="status-banner">{{ session('status') }}</div> @endif
                @if ($errors->any()) <div class="status-banner status-banner--error">{{ $errors->first() }}</div> @endif
                <p class="eyebrow">Payment Settings</p>
                <h1>Enable channels, store placeholders</h1>
                <p class="lede">This phase stores configuration only. Gateway processing remains a later phase.</p>
                <form class="admin-edit-form admin-settings-form" method="POST" action="{{ route('admin.settings.payments.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="admin-settings-table">
                        @foreach ($providers as $provider)
                            <section class="admin-settings-card">
                                <h2>{{ $provider->label }}</h2>
                                <label class="admin-check-row"><input name="providers[{{ $provider->provider }}][enabled]" type="checkbox" value="1" @checked($provider->enabled)> Enabled</label>
                                <label>Environment
                                    <select name="providers[{{ $provider->provider }}][environment]" required>
                                        <option value="sandbox" @selected($provider->environment === 'sandbox')>Sandbox</option>
                                        <option value="production" @selected($provider->environment === 'production')>Production</option>
                                    </select>
                                </label>
                                <label>Key Placeholder<input name="providers[{{ $provider->provider }}][key_placeholder]" value="{{ $provider->key_placeholder }}"></label>
                                <label>Webhook URL Placeholder<input name="providers[{{ $provider->provider }}][webhook_url_placeholder]" value="{{ $provider->webhook_url_placeholder }}"></label>
                            </section>
                        @endforeach
                    </div>
                    <button class="button button--primary" type="submit">Save Payment Settings</button>
                </form>
            </section>
        </main>
    </div>
</x-layouts.admin>
