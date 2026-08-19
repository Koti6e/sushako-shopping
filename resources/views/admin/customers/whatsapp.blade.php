<x-layouts.admin title="WhatsApp Composer - Admin">
    <x-admin.shell eyebrow="Customer Management" title="WhatsApp Marketing" subtitle="Prepare one consent-based WhatsApp intent for {{ $customer->name }}. The admin must manually press Send in WhatsApp.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.show', $customer)" tone="ghost" icon="fa-solid fa-arrow-left">Customer</x-admin.action>
            <x-admin.action :href="route('admin.customers.communications.index')" tone="secondary" icon="fa-solid fa-clock-rotate-left">Communication Logs</x-admin.action>
        </x-slot:actions>
        <section class="customer-admin">
            @if ($errors->any())
                <div class="status-banner status-banner--error">{{ $errors->first() }}</div>
            @endif

            <div class="customer-profile-grid">
                <section class="customer-panel customer-panel--span-5">
                    <div class="customer-panel__head"><h2>Customer Consent</h2></div>
                    <dl class="customer-detail-list">
                        <div><dt>Customer</dt><dd>{{ $customer->name }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ $normalPhone ? substr($customer->phone, 0, 2).'******'.substr($customer->phone, -2) : 'Invalid number' }}</dd></div>
                        <div><dt>Normalized</dt><dd>{{ $normalPhone ? 'Valid for wa.me' : 'Unavailable' }}</dd></div>
                        <div><dt>Marketing consent</dt><dd>{{ $customer->whatsapp_marketing_consent ? 'Available' : 'Marketing Consent Not Available' }}</dd></div>
                        <div><dt>Source</dt><dd>{{ $customer->whatsapp_marketing_consent_source ?: 'Not recorded' }}</dd></div>
                    </dl>
                </section>

                <section class="customer-panel customer-panel--span-7">
                    <div class="customer-panel__head"><h2>Message Composer</h2><span class="customer-badge customer-badge--neutral">Manual Send Only</span></div>
                    <form class="customer-stack-form customer-stack-form--composer" method="POST" action="{{ route('admin.customers.whatsapp.prepare', $customer) }}" data-whatsapp-composer>
                        @csrf
                        <label>Purpose
                            <select name="purpose" required>
                                @foreach ($purposes as $value => $label)
                                    <option value="{{ $value }}" @disabled(! $customer->whatsapp_marketing_consent) @selected(request('purpose') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        @if ($latestOrder)
                            <input type="hidden" name="order_id" value="{{ $latestOrder->id }}">
                        @endif
                        @if ($latestCart)
                            <input type="hidden" name="customer_cart_id" value="{{ $latestCart->id }}">
                        @endif
                        <label>Message
                            <textarea name="message" rows="10" maxlength="1200" required data-message-counter>{{ old('message', $defaultMessage) }}</textarea>
                        </label>
                        <p class="customer-counter"><span data-message-count>0</span>/1200 characters</p>
                        <x-admin.action type="submit" tone="primary" icon="fa-solid fa-check">Prepare WhatsApp Intent</x-admin.action>
                    </form>
                </section>

                @if ($prepared && $intentUrl)
                    <section class="customer-panel customer-panel--span-12">
                        <div class="customer-panel__head"><h2>Prepared Intent</h2><span class="customer-badge customer-badge--active">Prepared</span></div>
                        <p class="customer-empty-copy">Review the message below. Opening WhatsApp only opens the official click-to-chat screen; it does not send the message.</p>
                        <pre class="customer-message-preview">{{ $prepared->message_content }}</pre>
                        <div class="customer-card-actions">
                            <form method="POST" action="{{ route('admin.customer-communications.open-whatsapp', $prepared) }}">@csrf<x-admin.action type="submit" tone="primary" icon="fa-brands fa-whatsapp">Open WhatsApp</x-admin.action></form>
                            <form method="POST" action="{{ route('admin.customer-communications.mark-sent', $prepared) }}">@csrf<x-admin.action type="submit" tone="secondary" icon="fa-solid fa-check-double">Mark Sent by Admin</x-admin.action></form>
                        </div>
                    </section>
                @endif
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
