@php
    $settings = $vendor->delivery_settings ?? [];

    $gstRegistered = old(
        'gst_registered',
        ($vendor->gst_status === 'registered' || filled($vendor->gstin)) ? 'yes' : 'no'
    );

    $pickupSame = (bool) old(
        'pickup_same_as_business',
        data_get($settings, 'use_business_address', true)
    );

    $returnsAccepted = old(
        'returns_accepted',
        $vendor->returns_accepted ? 'yes' : 'no'
    );

    $sellerPhoneValue = old(
        'phone',
        preg_replace('/\D+/', '', $vendor->phone ?: auth()->user()->phone ?: '')
    );
@endphp

<x-layouts.seller title="Set up your shop" :minimal="true">

<style>
    :root {
        --ob-bg: #f7f7f8;
        --ob-card: #ffffff;
        --ob-text: #14161a;
        --ob-muted: #677084;
        --ob-border: #e5e7eb;
        --ob-soft: #f8fafc;
        --ob-primary: #111827;
        --ob-accent: #b88a32;
        --ob-accent-soft: #fff8e8;
        --ob-danger: #c62828;
        --ob-success: #087443;
        --ob-radius: 18px;
    }

    .seller-ob-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 10% 0%, rgba(184, 138, 50, .09), transparent 28rem),
            var(--ob-bg);
        padding: 32px 18px 64px;
        color: var(--ob-text);
    }

    .seller-ob-shell {
        width: min(920px, 100%);
        margin: 0 auto;
        animation: sellerObEnter .45s ease both;
    }

    .seller-ob-brand {
        display: flex;
        justify-content: center;
        margin-bottom: 22px;
    }

    .seller-ob-brand img {
        max-width: 190px;
        max-height: 52px;
        object-fit: contain;
    }

    .seller-ob-hero {
        text-align: center;
        margin-bottom: 26px;
    }

    .seller-ob-eyebrow {
        margin: 0 0 7px;
        color: var(--ob-accent);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .seller-ob-hero h1 {
        margin: 0;
        font-size: clamp(28px, 4vw, 42px);
        line-height: 1.12;
        letter-spacing: -.035em;
    }

    .seller-ob-hero > p:last-child {
        color: var(--ob-muted);
        margin: 10px auto 0;
        max-width: 630px;
        line-height: 1.6;
    }

    .seller-ob-card {
        background: var(--ob-card);
        border: 1px solid var(--ob-border);
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
        overflow: hidden;
    }

    .seller-ob-required-note {
        padding: 14px 24px;
        background: #fafafa;
        border-bottom: 1px solid var(--ob-border);
        color: var(--ob-muted);
        font-size: 13px;
    }

    .required-star {
        color: var(--ob-danger);
        font-weight: 900;
    }

    .seller-ob-section {
        padding: 28px;
        border-bottom: 1px solid var(--ob-border);
        animation: sellerObSection .45s ease both;
    }

    .seller-ob-section:last-of-type {
        border-bottom: 0;
    }

    .seller-ob-heading {
        margin-bottom: 20px;
    }

    .seller-ob-heading h2 {
        margin: 0 0 5px;
        font-size: 19px;
        letter-spacing: -.015em;
    }

    .seller-ob-heading p {
        margin: 0;
        color: var(--ob-muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .seller-ob-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .seller-ob-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
        font-size: 13px;
        font-weight: 750;
        color: #293247;
    }

    .seller-ob-field--wide {
        grid-column: 1 / -1;
    }

    .seller-ob-field input,
    .seller-ob-field select,
    .seller-ob-field textarea {
        width: 100%;
        box-sizing: border-box;
        min-height: 48px;
        border: 1px solid #d7dce4;
        border-radius: 11px;
        background: #fff;
        color: var(--ob-text);
        padding: 11px 13px;
        font: inherit;
        font-weight: 500;
        outline: none;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            transform .18s ease;
    }

    .seller-ob-field textarea {
        min-height: 96px;
        resize: vertical;
    }

    .seller-ob-field input:focus,
    .seller-ob-field select:focus,
    .seller-ob-field textarea:focus {
        border-color: var(--ob-accent);
        box-shadow: 0 0 0 4px rgba(184, 138, 50, .13);
    }

    .seller-ob-field small {
        color: var(--ob-muted);
        font-weight: 500;
        line-height: 1.45;
    }

    .seller-ob-error {
        color: var(--ob-danger);
        font-size: 12px;
        font-weight: 700;
    }

    .seller-phone-field,
    .seller-money-field {
        display: flex;
        align-items: center;
        min-height: 48px;
        border: 1px solid #d7dce4;
        border-radius: 11px;
        overflow: hidden;
        background: #fff;
    }

    .seller-phone-field > b,
    .seller-money-field > b {
        padding: 0 13px;
        color: #475569;
        white-space: nowrap;
        font-size: 13px;
    }

    .seller-phone-field input,
    .seller-money-field input {
        border: 0;
        border-radius: 0;
        min-width: 0;
        box-shadow: none !important;
    }

    .seller-logo-upload {
        border: 1.5px dashed #cfd5de;
        border-radius: 14px;
        background: var(--ob-soft);
        padding: 18px;
    }

    .seller-logo-upload__inner {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .seller-logo-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 17px;
        border-radius: 10px;
        background: var(--ob-primary);
        color: white;
        cursor: pointer;
        font-size: 13px;
        font-weight: 800;
        transition: transform .18s ease, opacity .18s ease;
    }

    .seller-logo-button:hover {
        transform: translateY(-1px);
        opacity: .92;
    }

    .seller-logo-upload input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .seller-logo-preview-box {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .seller-logo-preview-box img {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid var(--ob-border);
        background: white;
    }

    .seller-logo-file-name {
        color: var(--ob-muted);
        font-size: 13px;
        word-break: break-word;
    }

    .seller-choice-title {
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .seller-segmented {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 18px;
    }

    .seller-segmented label {
        position: relative;
        cursor: pointer;
        border: 1px solid var(--ob-border);
        border-radius: 12px;
        padding: 14px 16px;
        background: #fff;
        transition:
            border-color .2s ease,
            background .2s ease,
            transform .2s ease,
            box-shadow .2s ease;
    }

    .seller-segmented label:hover {
        transform: translateY(-1px);
    }

    .seller-segmented label.is-selected {
        border-color: var(--ob-accent);
        background: var(--ob-accent-soft);
        box-shadow: 0 0 0 3px rgba(184, 138, 50, .09);
    }

    .seller-segmented input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .seller-segmented span {
        display: block;
        font-weight: 800;
        font-size: 14px;
    }

    .seller-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid var(--ob-border);
        border-radius: 13px;
        padding: 15px 16px;
        background: var(--ob-soft);
        cursor: pointer;
        margin-bottom: 18px;
    }

    .seller-toggle-copy strong {
        display: block;
        font-size: 14px;
    }

    .seller-toggle-copy small {
        display: block;
        color: var(--ob-muted);
        margin-top: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    .seller-switch {
        position: relative;
        width: 46px;
        height: 26px;
        flex: 0 0 46px;
    }

    .seller-switch input {
        position: absolute;
        opacity: 0;
    }

    .seller-switch-track {
        position: absolute;
        inset: 0;
        border-radius: 99px;
        background: #cbd2da;
        transition: background .2s ease;
    }

    .seller-switch-track::after {
        content: "";
        position: absolute;
        width: 20px;
        height: 20px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: white;
        box-shadow: 0 1px 4px rgba(0,0,0,.18);
        transition: transform .2s ease;
    }

    .seller-switch input:checked + .seller-switch-track {
        background: var(--ob-success);
    }

    .seller-switch input:checked + .seller-switch-track::after {
        transform: translateX(20px);
    }

    .seller-conditional {
        animation: sellerObReveal .24s ease both;
    }

    .seller-delivery-preview {
        margin: 18px 0 0;
        padding: 14px 16px;
        border-radius: 12px;
        background: #eefbf4;
        border: 1px solid #ccebd9;
        color: #16633b;
        font-size: 13px;
        font-weight: 750;
    }

    .seller-commercial-card {
        display: grid;
        gap: 0;
        border: 1px solid #eadcb9;
        background: #fffaf0;
        border-radius: 15px;
        overflow: hidden;
    }

    .seller-commercial-row {
        display: grid;
        grid-template-columns: 185px minmax(0, 1fr);
        gap: 15px;
        padding: 16px;
        border-bottom: 1px solid #eee1c5;
    }

    .seller-commercial-row:last-child {
        border-bottom: 0;
    }

    .seller-commercial-row strong {
        font-size: 13px;
    }

    .seller-commercial-row span {
        color: #5e6471;
        font-size: 13px;
        line-height: 1.55;
    }

    .seller-commercial-highlight {
        color: #8b5f10 !important;
        font-weight: 800;
    }

    .seller-policy-box {
        padding: 18px;
        border-radius: 14px;
        border: 1px solid var(--ob-border);
        background: var(--ob-soft);
    }

    .seller-policy-box h3 {
        margin: 0 0 9px;
        font-size: 15px;
    }

    .seller-policy-box p {
        margin: 0;
        color: var(--ob-muted);
        line-height: 1.55;
        font-size: 13px;
    }

    .seller-policy-details {
        margin-top: 14px;
        display: grid;
        gap: 8px;
    }

    .seller-policy-details details {
        background: white;
        border: 1px solid var(--ob-border);
        border-radius: 10px;
        padding: 11px 13px;
    }

    .seller-policy-details summary {
        cursor: pointer;
        font-size: 13px;
        font-weight: 800;
    }

    .seller-policy-details details p {
        padding-top: 8px;
    }

    .seller-agreement {
        margin-top: 18px;
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 16px;
        border: 1px solid #d8dde5;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
    }

    .seller-agreement input {
        width: 19px;
        height: 19px;
        margin-top: 1px;
        accent-color: var(--ob-primary);
        flex: 0 0 auto;
    }

    .seller-agreement span {
        font-size: 13px;
        line-height: 1.6;
        color: #394150;
    }

    .seller-ob-footer {
        padding: 24px 28px 28px;
        background: #fafafa;
    }

    .seller-before-continue {
        padding: 15px 17px;
        border-radius: 12px;
        background: white;
        border: 1px solid var(--ob-border);
        margin-bottom: 18px;
    }

    .seller-before-continue strong {
        display: block;
        margin-bottom: 7px;
        font-size: 14px;
    }

    .seller-before-continue ul {
        margin: 0;
        padding-left: 19px;
        color: var(--ob-muted);
        font-size: 12.5px;
        line-height: 1.7;
    }

    .seller-ob-footer-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .seller-ob-back {
        color: #596273;
        text-decoration: none;
        font-size: 13px;
        font-weight: 750;
    }

    .seller-ob-submit {
        border: 0;
        min-height: 50px;
        padding: 0 24px;
        border-radius: 12px;
        background: var(--ob-primary);
        color: white;
        font-size: 14px;
        font-weight: 850;
        cursor: pointer;
        transition:
            transform .18s ease,
            opacity .18s ease,
            box-shadow .18s ease;
    }

    .seller-ob-submit:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, .15);
    }

    .seller-ob-submit:disabled {
        opacity: .42;
        cursor: not-allowed;
    }

    .seller-ob-alert {
        width: min(920px, 100%);
        box-sizing: border-box;
        margin: 0 auto 15px;
        border-radius: 12px;
        padding: 13px 16px;
        font-size: 13px;
        font-weight: 700;
    }

    .seller-ob-alert--success {
        background: #edf9f2;
        color: #08633a;
        border: 1px solid #caead7;
    }

    .seller-ob-alert--error {
        background: #fff1f1;
        color: #a62323;
        border: 1px solid #f0cccc;
    }

    [hidden] {
        display: none !important;
    }

    @keyframes sellerObEnter {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes sellerObSection {
        from { opacity: 0; transform: translateY(7px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes sellerObReveal {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 700px) {
        .seller-ob-page {
            padding: 20px 12px 42px;
        }

        .seller-ob-card {
            border-radius: 17px;
        }

        .seller-ob-section {
            padding: 22px 17px;
        }

        .seller-ob-required-note {
            padding-left: 17px;
            padding-right: 17px;
        }

        .seller-ob-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .seller-ob-field--wide {
            grid-column: auto;
        }

        .seller-segmented {
            grid-template-columns: 1fr;
        }

        .seller-commercial-row {
            grid-template-columns: 1fr;
            gap: 5px;
        }

        .seller-ob-footer {
            padding: 20px 17px;
        }

        .seller-ob-footer-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .seller-ob-submit {
            width: 100%;
        }

        .seller-ob-back {
            text-align: center;
            padding: 8px;
        }

        .seller-toggle-row {
            align-items: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="seller-ob-page" data-seller-onboarding>

    <div class="seller-ob-shell">

        <div class="seller-ob-brand">
            <x-brand.sushako-shopping-logo />
        </div>

        @if (session('status'))
            <div class="seller-ob-alert seller-ob-alert--success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="seller-ob-alert seller-ob-alert--error">
                Please check the highlighted information and try again.
            </div>
        @endif

        <header class="seller-ob-hero">
            <p class="seller-ob-eyebrow">Sushako Seller OS</p>
            <h1>Set up your shop</h1>
            <p>
                Tell us about your business, delivery preferences and policies.
                We'll prepare your Seller OS and activate your Free Starter Plan.
            </p>
        </header>

        <form
            class="seller-ob-card"
            method="POST"
            action="{{ route('seller.onboarding.store') }}"
            enctype="multipart/form-data"
            data-onboarding-form
        >
            @csrf

            <div class="seller-ob-required-note">
                <span class="required-star">*</span> Required fields
            </div>

            {{-- BUSINESS DETAILS --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>1. Business Details</h2>
                    <p>Use the details customers should recognise when shopping from your store.</p>
                </div>

                <div class="seller-ob-grid">

                    <label class="seller-ob-field seller-ob-field--wide">
                        <span>
                            Business Name <span class="required-star">*</span>
                        </span>

                        <input
                            name="business_name"
                            value="{{ old('business_name', $vendor->business_name) }}"
                            minlength="3"
                            maxlength="100"
                            required
                            autocomplete="organization"
                            placeholder="Enter your business name"
                        >

                        @error('business_name')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field seller-ob-field--wide">
                        <span>
                            Business Address <span class="required-star">*</span>
                        </span>

                        <textarea
                            name="business_address"
                            rows="3"
                            required
                            autocomplete="street-address"
                            placeholder="Enter your complete business address"
                        >{{ old('business_address', $vendor->address_line_1) }}</textarea>

                        @error('business_address')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            Pincode <span class="required-star">*</span>
                        </span>

                        <input
                            name="postal_code"
                            inputmode="numeric"
                            maxlength="6"
                            pattern="\d{6}"
                            value="{{ old('postal_code', $vendor->postal_code) }}"
                            required
                            data-digits-only
                            data-pincode="business"
                            placeholder="603001"
                        >

                        @error('postal_code')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            Phone Number <span class="required-star">*</span>
                        </span>

                        <span class="seller-phone-field">
                            <b>+91</b>

                            <input
                                name="phone"
                                inputmode="numeric"
                                maxlength="10"
                                pattern="[6-9]\d{9}"
                                value="{{ $sellerPhoneValue }}"
                                required
                                data-digits-only
                                placeholder="9876543210"
                            >
                        </span>

                        @error('phone')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            City <span class="required-star">*</span>
                        </span>

                        <input
                            name="city"
                            value="{{ old('city', $vendor->city) }}"
                            required
                            autocomplete="address-level2"
                            placeholder="Chengalpattu"
                        >

                        @error('city')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            State <span class="required-star">*</span>
                        </span>

                        <select name="state" required>
                            @foreach ($indianStates as $stateName)
                                <option
                                    value="{{ $stateName }}"
                                    @selected(old('state', $vendor->state ?: 'Tamil Nadu') === $stateName)
                                >
                                    {{ $stateName }}
                                </option>
                            @endforeach
                        </select>

                        @error('state')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="seller-ob-field seller-ob-field--wide">
                        <span>Business Logo <small>(Optional)</small></span>

                        <div class="seller-logo-upload">
                            <div class="seller-logo-upload__inner">

                                <label class="seller-logo-button">
                                    Browse Logo
                                    <input
                                        type="file"
                                        name="business_logo"
                                        accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"
                                        data-logo-input
                                    >
                                </label>

                                <div class="seller-logo-preview-box">
                                    <img
                                        data-logo-image
                                        src="{{ $vendor->business_logo_path ? asset('storage/'.$vendor->business_logo_path) : asset('assets/brand/sushako-shopping-official-icon.png') }}"
                                        alt="Business logo preview"
                                    >

                                    <span
                                        class="seller-logo-file-name"
                                        data-logo-file-name
                                    >
                                        {{ $vendor->business_logo_path ? 'Current logo saved' : 'No file selected' }}
                                    </span>
                                </div>
                            </div>

                            <small>
                                PNG, JPG or WEBP. Maximum size according to platform upload rules.
                            </small>
                        </div>

                        @error('business_logo')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- TAX --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>2. Tax Details</h2>
                    <p>Choose the option that currently applies to your business.</p>
                </div>

                <div class="seller-choice-title">
                    Do you have GST registration?
                    <span class="required-star">*</span>
                </div>

                <div
                    class="seller-segmented"
                    role="radiogroup"
                    aria-label="GST registration"
                >
                    <label
                        @class(['is-selected' => $gstRegistered === 'no'])
                        data-gst-option
                    >
                        <input
                            type="radio"
                            name="gst_registered"
                            value="no"
                            required
                            @checked($gstRegistered === 'no')
                        >
                        <span>No, I don't have GST</span>
                    </label>

                    <label
                        @class(['is-selected' => $gstRegistered === 'yes'])
                        data-gst-option
                    >
                        <input
                            type="radio"
                            name="gst_registered"
                            value="yes"
                            required
                            @checked($gstRegistered === 'yes')
                        >
                        <span>Yes, I have GST</span>
                    </label>
                </div>

                <div class="seller-ob-grid">

                    <label
                        class="seller-ob-field seller-ob-field--wide seller-conditional"
                        data-gstin-field
                    >
                        <span>
                            GST Number <span class="required-star">*</span>
                        </span>

                        <input
                            name="gstin"
                            value="{{ old('gstin', $vendor->gstin) }}"
                            maxlength="15"
                            autocomplete="off"
                            placeholder="22AAAAA0000A1Z5"
                            style="text-transform:uppercase"
                        >

                        <small>Enter your 15-character GSTIN.</small>

                        @error('gstin')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label
                        class="seller-ob-field seller-ob-field--wide seller-conditional"
                        data-pan-field
                    >
                        <span>
                            PAN Number <span class="required-star">*</span>
                        </span>

                        <input
                            name="pan_number"
                            value="{{ old('pan_number', $vendor->pan_number) }}"
                            maxlength="10"
                            autocomplete="off"
                            placeholder="ABCDE1234F"
                            style="text-transform:uppercase"
                        >

                        <small>PAN is required when GST registration is not available.</small>

                        @error('pan_number')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                </div>
            </section>

            {{-- PICKUP --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>3. Pickup Address</h2>
                    <p>This is where customer delivery distance will be calculated from.</p>
                </div>

                <label class="seller-toggle-row">
                    <span class="seller-toggle-copy">
                        <strong>Same as Business Address</strong>
                        <small>Use your business location as the order pickup location.</small>
                    </span>

                    <span class="seller-switch">
                        <input
                            type="hidden"
                            name="pickup_same_as_business"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="pickup_same_as_business"
                            value="1"
                            @checked($pickupSame)
                            data-pickup-toggle
                        >

                        <span class="seller-switch-track"></span>
                    </span>
                </label>

                <div
                    class="seller-ob-grid seller-conditional"
                    data-pickup-fields
                    @if ($pickupSame) hidden @endif
                >

                    <label class="seller-ob-field seller-ob-field--wide">
                        <span>
                            Pickup Address <span class="required-star">*</span>
                        </span>

                        <textarea
                            name="pickup_address"
                            rows="3"
                            placeholder="Enter pickup address"
                        >{{ old('pickup_address', $vendor->pickup_address_line_1) }}</textarea>

                        @error('pickup_address')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            Pickup Pincode <span class="required-star">*</span>
                        </span>

                        <input
                            name="pickup_postal_code"
                            inputmode="numeric"
                            maxlength="6"
                            pattern="\d{6}"
                            value="{{ old('pickup_postal_code', $vendor->pickup_postal_code) }}"
                            data-digits-only
                            data-pincode="pickup"
                            placeholder="603001"
                        >

                        @error('pickup_postal_code')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            Pickup City <span class="required-star">*</span>
                        </span>

                        <input
                            name="pickup_city"
                            value="{{ old('pickup_city', $vendor->pickup_city) }}"
                            placeholder="Chengalpattu"
                        >

                        @error('pickup_city')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            Pickup State <span class="required-star">*</span>
                        </span>

                        <select name="pickup_state">
                            @foreach ($indianStates as $stateName)
                                <option
                                    value="{{ $stateName }}"
                                    @selected(old('pickup_state', $vendor->pickup_state ?: 'Tamil Nadu') === $stateName)
                                >
                                    {{ $stateName }}
                                </option>
                            @endforeach
                        </select>

                        @error('pickup_state')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                </div>
            </section>

            {{-- DELIVERY --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>4. Delivery Setup</h2>
                    <p>Configure the area you serve and how customers are charged for delivery.</p>
                </div>

                <div class="seller-ob-grid">

                    <label class="seller-ob-field">
                        <span>
                            Delivery Radius <span class="required-star">*</span>
                        </span>

                        <span class="seller-money-field">
                            <input
                                type="number"
                                min="1"
                                max="500"
                                step="1"
                                name="delivery_radius"
                                value="{{ old('delivery_radius', $vendor->delivery_radius ?: 10) }}"
                                required
                                inputmode="numeric"
                                data-delivery-preview-input
                            >
                            <b>km</b>
                        </span>

                        @error('delivery_radius')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field">
                        <span>
                            Delivery Charge <span class="required-star">*</span>
                        </span>

                        <span class="seller-money-field">
                            <b>₹</b>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name="flat_shipping_charge"
                                value="{{ old('flat_shipping_charge', $vendor->flat_shipping_charge ?? 40) }}"
                                required
                                inputmode="decimal"
                                data-delivery-preview-input
                            >
                        </span>

                        @error('flat_shipping_charge')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="seller-ob-field seller-ob-field--wide">
                        <span>Free Shipping Above</span>

                        <span class="seller-money-field">
                            <b>₹</b>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name="free_shipping_threshold"
                                value="{{ old('free_shipping_threshold', $vendor->free_shipping_threshold ?? 999) }}"
                                inputmode="decimal"
                                data-delivery-preview-input
                            >
                        </span>

                        @error('free_shipping_threshold')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                </div>

                <div class="seller-delivery-preview" data-delivery-preview>
                    ₹40 delivery within 10 km · FREE above ₹999
                </div>
            </section>

            {{-- RETURN POLICY --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>5. Return Policy</h2>
                    <p>
                        Choose whether your shop normally accepts returns.
                        Customer protection for wrong, damaged, missing, counterfeit or undelivered orders still applies.
                    </p>
                </div>

                <div class="seller-choice-title">
                    Do you accept customer returns?
                    <span class="required-star">*</span>
                </div>

                <div
                    class="seller-segmented"
                    role="radiogroup"
                    aria-label="Return policy"
                >
                    <label
                        @class(['is-selected' => $returnsAccepted === 'yes'])
                        data-return-option
                    >
                        <input
                            type="radio"
                            name="returns_accepted"
                            value="yes"
                            required
                            @checked($returnsAccepted === 'yes')
                        >
                        <span>Returns Allowed</span>
                    </label>

                    <label
                        @class(['is-selected' => $returnsAccepted === 'no'])
                        data-return-option
                    >
                        <input
                            type="radio"
                            name="returns_accepted"
                            value="no"
                            required
                            @checked($returnsAccepted === 'no')
                        >
                        <span>No Returns</span>
                    </label>
                </div>

                <label
                    class="seller-ob-field seller-ob-field--wide seller-conditional"
                    data-return-window-field
                >
                    <span>
                        Return Window <span class="required-star">*</span>
                    </span>

                    <select name="return_window_days">
                        @foreach ([1, 3, 5, 7] as $days)
                            <option
                                value="{{ $days }}"
                                @selected((int) old('return_window_days', $vendor->return_window_days ?: 3) === $days)
                            >
                                {{ $days }} {{ str('day')->plural($days) }} from delivery
                            </option>
                        @endforeach
                    </select>

                    @error('return_window_days')
                        <span class="seller-ob-error">{{ $message }}</span>
                    @enderror
                </label>
            </section>

            {{-- COMMERCIAL TERMS --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>6. Seller Terms & Charges</h2>
                    <p>Please understand these rules before activating your shop.</p>
                </div>

                <div class="seller-commercial-card">

                    <div class="seller-commercial-row">
                        <strong>Free Starter Plan</strong>
                        <span>
                            Your shop starts on the Free Plan with
                            <span class="seller-commercial-highlight">Sushako branding</span>.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Sushako Commission</strong>
                        <span>
                            <span class="seller-commercial-highlight">
                                ₹1 commission per applicable product/order item
                            </span>
                            under the Free Plan according to Sushako's current commission rules.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Online Payments</strong>
                        <span>
                            Razorpay/payment gateway charges are
                            <span class="seller-commercial-highlight">
                                separate from Sushako commission
                            </span>
                            and apply to eligible online transactions.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Refund Charges</strong>
                        <span>
                            Where payment gateway charges are non-refundable, applicable charges may be deducted from the refundable amount and will be disclosed according to the platform refund flow.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Settlements</strong>
                        <span>
                            Eligible seller settlements are normally processed within
                            <span class="seller-commercial-highlight">T+3 to T+5 days</span>,
                            subject to successful order completion, valid payout details and no active refund/dispute.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Bank Details</strong>
                        <span>
                            You may start listing products before adding bank details, but valid bank details are required before payout.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Order Acceptance</strong>
                        <span>
                            Orders should be accepted within
                            <span class="seller-commercial-highlight">24 hours</span>.
                            After 24 hours, the customer may choose to continue waiting or request a refund according to platform rules.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Delivery</strong>
                        <span>
                            Sellers are responsible for fulfilling orders under the Sushako self-shipping model using the delivery settings configured for their store.
                        </span>
                    </div>

                    <div class="seller-commercial-row">
                        <strong>Cancellation</strong>
                        <span>
                            Normal cancellation is permitted only until the order is shipped, subject to applicable Sushako order and customer-protection rules.
                        </span>
                    </div>

                </div>
            </section>

            {{-- AGREEMENT --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>7. Agreement</h2>
                    <p>Your shop can be activated after you acknowledge the platform rules.</p>
                </div>

                <div class="seller-policy-box">
                    <h3>Sushako Seller Policies</h3>

                    <p>
                        These policies cover seller commissions, online payment charges,
                        settlements, order acceptance, refunds, returns, delivery responsibilities
                        and platform/customer-protection requirements.
                    </p>

                    <div class="seller-policy-details">

                        <details>
                            <summary>Commission & Payment Policy</summary>
                            <p>
                                Free Plan sellers are subject to the current ₹1 Sushako commission rule.
                                Razorpay/payment gateway charges are separate from Sushako commission.
                            </p>
                        </details>

                        <details>
                            <summary>Settlement Policy</summary>
                            <p>
                                Eligible settlements are normally processed within T+3 to T+5 days
                                after the applicable order requirements are satisfied.
                            </p>
                        </details>

                        <details>
                            <summary>Order Acceptance & Refund Policy</summary>
                            <p>
                                Sellers should accept orders within 24 hours. If the seller does not
                                accept within that period, the customer may continue waiting or request
                                a refund according to the platform workflow.
                            </p>
                        </details>

                        <details>
                            <summary>Return & Customer Protection Policy</summary>
                            <p>
                                The selected store return policy applies to normal returns.
                                Platform/customer-protection rules may still apply to wrong, damaged,
                                missing, counterfeit or undelivered orders.
                            </p>
                        </details>

                    </div>

                    <label class="seller-agreement">
                        <input
                            type="checkbox"
                            name="seller_terms_accepted"
                            value="1"
                            required
                            data-terms-checkbox
                        >

                        <span>
                            I have read and agree to the Sushako Seller Terms,
                            commission structure, payment gateway charges,
                            settlement policy, 24-hour order acceptance rule,
                            return/refund rules and applicable platform policies.
                            <span class="required-star">*</span>
                        </span>
                    </label>

                    @error('seller_terms_accepted')
                        <span class="seller-ob-error">{{ $message }}</span>
                    @enderror
                </div>
            </section>

            {{-- FINAL --}}
            <footer class="seller-ob-footer">

                <div class="seller-before-continue">
                    <strong>Before you continue</strong>

                    <ul>
                        <li>Your Free Starter Plan will be activated.</li>
                        <li>Free Plan Sushako commission rules apply.</li>
                        <li>Razorpay charges are separate for online payments.</li>
                        <li>Eligible settlements normally follow T+3–T+5 processing rules.</li>
                        <li>Bank details are required before settlement payout.</li>
                        <li>Orders should be accepted within 24 hours.</li>
                        <li>Your selected return policy will apply to your store where appropriate.</li>
                    </ul>
                </div>

                <div class="seller-ob-footer-actions">

                    <a
                        class="seller-ob-back"
                        href="{{ route('home') }}"
                    >
                        ← Back to Shop
                    </a>

                    <button
                        class="seller-ob-submit"
                        type="submit"
                        data-primary-submit
                        disabled
                    >
                        Complete Setup & Create My Shop →
                    </button>

                </div>
            </footer>

        </form>
    </div>
</div>

<script>
(() => {
    const root = document.querySelector('[data-seller-onboarding]');
    if (!root) return;

    const form = root.querySelector('[data-onboarding-form]');
    const submitButton = root.querySelector('[data-primary-submit]');
    const termsCheckbox = root.querySelector('[data-terms-checkbox]');

    const syncTaxFields = () => {
        const value =
            root.querySelector('input[name="gst_registered"]:checked')?.value || 'no';

        root.querySelectorAll('[data-gst-option]').forEach((label) => {
            label.classList.toggle(
                'is-selected',
                label.querySelector('input')?.value === value
            );
        });

        const gstField = root.querySelector('[data-gstin-field]');
        const panField = root.querySelector('[data-pan-field]');

        const gstInput = gstField?.querySelector('input');
        const panInput = panField?.querySelector('input');

        if (gstField) {
            gstField.hidden = value !== 'yes';
        }

        if (panField) {
            panField.hidden = value !== 'no';
        }

        if (gstInput) {
            gstInput.required = value === 'yes';
        }

        if (panInput) {
            panInput.required = value === 'no';
        }
    };

    const syncPickup = () => {
        const same =
            root.querySelector('[data-pickup-toggle]')?.checked ?? true;

        const fields = root.querySelector('[data-pickup-fields]');
        if (!fields) return;

        fields.hidden = same;

        fields.querySelectorAll('input, textarea, select').forEach((field) => {
            field.required = !same;
        });
    };

    const syncReturns = () => {
        const value =
            root.querySelector('input[name="returns_accepted"]:checked')?.value || 'no';

        root.querySelectorAll('[data-return-option]').forEach((label) => {
            label.classList.toggle(
                'is-selected',
                label.querySelector('input')?.value === value
            );
        });

        const windowField = root.querySelector('[data-return-window-field]');
        const select = windowField?.querySelector('select');

        if (windowField) {
            windowField.hidden = value !== 'yes';
        }

        if (select) {
            select.required = value === 'yes';
        }
    };

    const syncDeliveryPreview = () => {
        const radius =
            root.querySelector('input[name="delivery_radius"]')?.value || '0';

        const charge =
            root.querySelector('input[name="flat_shipping_charge"]')?.value || '0';

        const free =
            root.querySelector('input[name="free_shipping_threshold"]')?.value || '';

        const preview = root.querySelector('[data-delivery-preview]');
        if (!preview) return;

        const chargeText =
            Number(charge || 0).toLocaleString('en-IN', {
                maximumFractionDigits: 2
            });

        let text = `₹${chargeText} delivery within ${radius} km`;

        if (free !== '') {
            text += ` · FREE above ₹${Number(free).toLocaleString('en-IN')}`;
        }

        preview.textContent = text;
    };

    const syncTerms = () => {
        if (!submitButton || !termsCheckbox) return;

        submitButton.disabled = !termsCheckbox.checked;
    };

    const syncLogoPreview = (event) => {
        const file = event.target.files?.[0];

        const filename = root.querySelector('[data-logo-file-name]');
        const image = root.querySelector('[data-logo-image]');

        if (!file) {
            if (filename) filename.textContent = 'No file selected';
            return;
        }

        if (filename) {
            filename.textContent = file.name;
        }

        if (image && file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = (loadEvent) => {
                image.src = loadEvent.target.result;
            };

            reader.readAsDataURL(file);
        }
    };

    root.querySelectorAll('input[name="gst_registered"]').forEach((input) => {
        input.addEventListener('change', syncTaxFields);
    });

    root.querySelectorAll('input[name="returns_accepted"]').forEach((input) => {
        input.addEventListener('change', syncReturns);
    });

    root.querySelector('[data-pickup-toggle]')
        ?.addEventListener('change', syncPickup);

    root.querySelectorAll('[data-delivery-preview-input]').forEach((input) => {
        input.addEventListener('input', syncDeliveryPreview);
    });

    root.querySelector('[data-logo-input]')
        ?.addEventListener('change', syncLogoPreview);

    termsCheckbox?.addEventListener('change', syncTerms);

    root.querySelectorAll('[data-digits-only]').forEach((input) => {
        input.addEventListener('input', () => {
            const max = Number(input.getAttribute('maxlength') || 10);

            input.value = input.value
                .replace(/\D+/g, '')
                .slice(0, max);
        });
    });

    root.querySelectorAll('input[name="gstin"], input[name="pan_number"]')
        .forEach((input) => {
            input.addEventListener('input', () => {
                input.value = input.value.toUpperCase();
            });
        });

    form?.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            return;
        }

        if (!termsCheckbox?.checked) {
            event.preventDefault();
            termsCheckbox?.focus();
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Creating your shop...';
        }
    });

    syncTaxFields();
    syncPickup();
    syncReturns();
    syncDeliveryPreview();
    syncTerms();
})();
</script>

</x-layouts.seller>