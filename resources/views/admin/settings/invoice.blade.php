<x-layouts.admin title="Invoice Settings - Admin">
    <x-admin.shell eyebrow="Settings" title="Invoice numbering" subtitle="Manage invoice sequence and invoice text.">
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                @if (session('status')) <div class="status-banner">{{ session('status') }}</div> @endif
                @if ($errors->any()) <div class="status-banner status-banner--error">{{ $errors->first() }}</div> @endif
                <p class="eyebrow">Invoice Settings</p>
                <h1>Sequential invoice control</h1>
                <form class="admin-edit-form admin-settings-form" method="POST" action="{{ route('admin.settings.invoice.update') }}">
                    @csrf
                    @method('PUT')
                    <label>Invoice Prefix<input name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" required></label>
                    <label>Next Invoice Number<input name="next_invoice_number" type="number" min="{{ $settings->next_invoice_number }}" value="{{ old('next_invoice_number', $settings->next_invoice_number) }}" required></label>
                    <label>Invoice Footer<textarea name="invoice_footer">{{ old('invoice_footer', $settings->invoice_footer) }}</textarea></label>
                    <label>Terms & Conditions<textarea name="terms_conditions">{{ old('terms_conditions', $settings->terms_conditions) }}</textarea></label>
                    <label>Authorized Signatory Name<input name="authorized_signatory_name" value="{{ old('authorized_signatory_name', $settings->authorized_signatory_name) }}" required></label>
                    <label>Authorized Signatory Designation<input name="authorized_signatory_designation" value="{{ old('authorized_signatory_designation', $settings->authorized_signatory_designation) }}" required></label>
                    <button class="button button--primary" type="submit">Save Invoice Settings</button>
                </form>
            </section>
    </x-admin.shell>
</x-layouts.admin>
