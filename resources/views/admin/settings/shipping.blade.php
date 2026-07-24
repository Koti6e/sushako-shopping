<x-layouts.admin title="Shipping Settings - Admin">
    <div class="admin-shell">
        @include('admin.settings.partials.sidebar')
        <main class="admin-main">
            <header class="admin-topbar admin-topbar--premium"><div><span>Settings</span><strong>Shipping rules</strong></div></header>
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                @if (session('status')) <div class="status-banner">{{ session('status') }}</div> @endif
                @if ($errors->any()) <div class="status-banner status-banner--error">{{ $errors->first() }}</div> @endif
                <p class="eyebrow">Shipping Settings</p>
                <h1>Delivery charge communication</h1>
                <form class="admin-edit-form admin-settings-form" method="POST" action="{{ route('admin.settings.shipping.update') }}">
                    @csrf
                    @method('PUT')
                    <label class="admin-check-row"><input name="free_shipping_enabled" type="checkbox" value="1" @checked($settings->free_shipping_enabled)> Enable Free Shipping</label>
                    <label>Free Shipping Threshold<input name="free_shipping_threshold" type="number" min="0" value="{{ old('free_shipping_threshold', $settings->free_shipping_threshold) }}" required></label>
                    <label>Shipping Policy Text<textarea name="shipping_policy_text" required>{{ old('shipping_policy_text', $settings->shipping_policy_text) }}</textarea></label>
                    <label class="admin-check-row"><input name="weight_based_shipping_enabled" type="checkbox" value="1" @checked($settings->weight_based_shipping_enabled)> Weight Based Shipping</label>
                    <label class="admin-check-row"><input name="courier_integration_enabled" type="checkbox" value="1" @checked($settings->courier_integration_enabled)> Courier Integration</label>
                    <label class="admin-check-row"><input name="zone_based_shipping_enabled" type="checkbox" value="1" @checked($settings->zone_based_shipping_enabled)> Zone Based Shipping</label>
                    <button class="button button--primary" type="submit">Save Shipping Settings</button>
                </form>
            </section>
        </main>
    </div>
</x-layouts.admin>
