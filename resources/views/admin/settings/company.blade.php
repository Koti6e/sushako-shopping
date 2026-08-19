<x-layouts.admin title="Company Settings - Admin">
    <x-admin.shell eyebrow="Settings" title="Company profile" subtitle="Central business identity for invoices, emails and policies.">
            <section class="admin-dashboard-panel admin-settings-panel">
                @include('admin.settings.partials.nav')
                @if (session('status')) <div class="status-banner">{{ session('status') }}</div> @endif
                @if ($errors->any()) <div class="status-banner status-banner--error">{{ $errors->first() }}</div> @endif
                <p class="eyebrow">Company Settings</p>
                <h1>Central business identity</h1>
                <p class="lede">Used by invoices, emails, policies and future operational reports.</p>
                <form class="admin-edit-form admin-settings-form" method="POST" action="{{ route('admin.settings.company.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <label>Company Name<input name="company_name" value="{{ old('company_name', $settings->company_name) }}" required></label>
                    <label>Legal Business Name<input name="legal_business_name" value="{{ old('legal_business_name', $settings->legal_business_name) }}" required></label>
                    <x-ui.file-upload class="admin-field-wide" name="logo" label="Company logo" hint="JPG, PNG or WebP logo for invoices and emails." accept="image/jpeg,image/png,image/webp" />
                    <label>GSTIN<input name="gstin" value="{{ old('gstin', $settings->gstin) }}"></label>
                    <label>PAN <small>Optional</small><input name="pan" value="{{ old('pan', $settings->pan) }}"></label>
                    <label>CIN <small>Optional</small><input name="cin" value="{{ old('cin', $settings->cin) }}"></label>
                    <label>Address Line 1<input name="address_line_1" value="{{ old('address_line_1', $settings->address_line_1) }}" required></label>
                    <label>Address Line 2<input name="address_line_2" value="{{ old('address_line_2', $settings->address_line_2) }}"></label>
                    <label>City<input name="city" value="{{ old('city', $settings->city) }}" required></label>
                    <label>State<input name="state" value="{{ old('state', $settings->state) }}" required></label>
                    <label>Pincode<input name="pincode" value="{{ old('pincode', $settings->pincode) }}" required></label>
                    <label>Country<input name="country" value="{{ old('country', $settings->country) }}" required></label>
                    <label>Support Email<input name="support_email" type="email" value="{{ old('support_email', $settings->support_email) }}" required></label>
                    <label>Support Phone<input name="support_phone" value="{{ old('support_phone', $settings->support_phone) }}" required></label>
                    <label>Website<input name="website" type="url" value="{{ old('website', $settings->website) }}"></label>
                    <label>Currency<input name="currency" value="{{ old('currency', $settings->currency) }}" maxlength="3" required></label>
                    <label>Timezone<input name="timezone" value="{{ old('timezone', $settings->timezone) }}" required></label>
                    <label>Business Hours<input name="business_hours" value="{{ old('business_hours', $settings->business_hours) }}" required></label>
                    <button class="button button--primary" type="submit">Save Company Settings</button>
                </form>
            </section>
    </x-admin.shell>
</x-layouts.admin>
