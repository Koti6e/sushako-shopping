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
        --ob-info: #0e6b8a;
        --ob-info-bg: #e8f4fa;
        --ob-warning: #b45f06;
        --ob-warning-bg: #fff3e0;
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
        width: min(960px, 100%);
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
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .seller-ob-heading .step-badge {
        font-size: 11px;
        font-weight: 800;
        background: var(--ob-accent-soft);
        color: var(--ob-accent);
        padding: 2px 12px;
        border-radius: 99px;
        letter-spacing: .04em;
    }

    .seller-ob-heading p {
        margin: 0;
        color: var(--ob-muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .seller-ob-heading .seller-tip {
        margin-top: 10px;
        padding: 12px 16px;
        background: var(--ob-info-bg);
        border-left: 4px solid var(--ob-info);
        border-radius: 8px;
        font-size: 13px;
        color: var(--ob-info);
        font-weight: 500;
        line-height: 1.5;
    }

    .seller-ob-heading .seller-tip strong {
        color: var(--ob-text);
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

    .seller-ob-field .field-hint {
        color: var(--ob-muted);
        font-weight: 500;
        line-height: 1.45;
        font-size: 12px;
    }

    .seller-ob-field .field-hint--success {
        color: var(--ob-success);
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
        background: #f8fafc;
        border-right: 1px solid #e5e7eb;
        min-height: 48px;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .seller-phone-field input,
    .seller-money-field input {
        border: 0;
        border-radius: 0;
        min-width: 0;
        box-shadow: none !important;
        padding: 11px 13px;
        flex: 1;
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
        border: none;
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
        flex-shrink: 0;
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

    .seller-segmented .segmented-desc {
        font-weight: 400;
        font-size: 12px;
        color: var(--ob-muted);
        margin-top: 2px;
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
        width: min(960px, 100%);
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

    .address-type-selector {
        display: flex;
        gap: 12px;
        margin-bottom: 4px;
        flex-wrap: wrap;
    }

    .address-type-selector label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border: 1.5px solid var(--ob-border);
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        transition: all .2s ease;
        background: white;
    }

    .address-type-selector label:hover {
        border-color: var(--ob-accent);
    }

    .address-type-selector label.is-selected {
        border-color: var(--ob-accent);
        background: var(--ob-accent-soft);
    }

    .address-type-selector input {
        display: none;
    }

    .address-type-selector .addr-icon {
        font-size: 18px;
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

        .address-type-selector label {
            flex: 1;
            min-width: 100px;
            justify-content: center;
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

            {{-- ============================================================ --}}
            {{-- 1. BUSINESS DETAILS                                           --}}
            {{-- ============================================================ --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>
                        Business Details
                        <span class="step-badge">Step 1 of 7</span>
                    </h2>
                    <p>Use the details customers should recognise when shopping from your store.</p>
                    <div class="seller-tip">
                        💡 <strong>Pro Tip:</strong> Use your legal business name as it appears on your GST/PAN documents. This helps with verification and builds customer trust.
                    </div>
                </div>

                <div class="seller-ob-grid">

                    {{-- Business Name --}}
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
                            placeholder="e.g., Sushako Organic Foods"
                        >

                        <span class="field-hint">
                            This appears on invoices, delivery labels, and customer communications.
                        </span>

                        @error('business_name')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Address Type Selector --}}
                    <div class="seller-ob-field seller-ob-field--wide">
                        <span>Address Type <span class="required-star">*</span></span>

                        <div class="address-type-selector" data-address-type-selector>
                            <label class="is-selected">
                                <input type="radio" name="address_type" value="residential" checked>
                                <span class="addr-icon">🏠</span>
                                Residential
                            </label>
                            <label>
                                <input type="radio" name="address_type" value="commercial">
                                <span class="addr-icon">🏢</span>
                                Commercial
                            </label>
                            <label>
                                <input type="radio" name="address_type" value="industrial">
                                <span class="addr-icon">🏭</span>
                                Industrial
                            </label>
                            <label>
                                <input type="radio" name="address_type" value="warehouse">
                                <span class="addr-icon">📦</span>
                                Warehouse
                            </label>
                        </div>

                        <span class="field-hint">
                            Your address type helps us optimise delivery routing and logistics.
                        </span>

                        @error('address_type')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Address Line 1 --}}
                    <label class="seller-ob-field seller-ob-field--wide">
                        <span>
                            Address Line 1 <span class="required-star">*</span>
                        </span>

                        <input
                            name="address_line_1"
                            value="{{ old('address_line_1', $vendor->address_line_1) }}"
                            required
                            autocomplete="address-line1"
                            placeholder="e.g., 123, Main Road, Anna Nagar"
                        >

                        <span class="field-hint">
                            Street address, building number, and area.
                        </span>

                        @error('address_line_1')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Address Line 2 --}}
                    <label class="seller-ob-field seller-ob-field--wide">
                        <span>Address Line 2 <small>(Optional)</small></span>

                        <input
                            name="address_line_2"
                            value="{{ old('address_line_2', $vendor->address_line_2) }}"
                            autocomplete="address-line2"
                            placeholder="e.g., Landmark: Near Metro Station, Floor/Suite"
                        >

                        <span class="field-hint">
                            Useful for locating your shop — landmark, building name, floor number, etc.
                        </span>

                        @error('address_line_2')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- City --}}
                    <label class="seller-ob-field">
                        <span>
                            City <span class="required-star">*</span>
                        </span>

                        <input
                            name="city"
                            value="{{ old('city', $vendor->city) }}"
                            required
                            autocomplete="address-level2"
                            placeholder="e.g., Chennai, Bengaluru"
                        >

                        @error('city')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- State --}}
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

                    {{-- Pincode --}}
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

                        <span class="field-hint">
                            6-digit Indian pincode.
                        </span>

                        @error('postal_code')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Phone --}}
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

                        <span class="field-hint">
                            10-digit Indian mobile number. Used for order updates and delivery coordination.
                        </span>

                        @error('phone')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Email (Optional but recommended) --}}
                    <label class="seller-ob-field">
                        <span>Business Email <small>(Optional)</small></span>

                        <input
                            name="business_email"
                            type="email"
                            value="{{ old('business_email', $vendor->email ?? auth()->user()->email) }}"
                            autocomplete="email"
                            placeholder="shop@yourbusiness.com"
                        >

                        <span class="field-hint">
                            Recommended for professional communication and invoices.
                        </span>

                        @error('business_email')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- Business Logo --}}
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

                            <span class="field-hint">
                                PNG, JPG or WEBP. Recommended size: 400×400px. Max 2MB.
                            </span>
                        </div>

                        @error('business_logo')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 2. TAX DETAILS                                                --}}
            {{-- ============================================================ --}}
            <section class="seller-ob-section">
                <div class="seller-ob-heading">
                    <h2>
                        Tax Details
                        <span class="step-badge">Step 2 of 7</span>
                    </h2>
                    <p>Choose the option that currently applies to your business.</p>
                    <div class="seller-tip">
                        💡 <strong>Product Architect's Note:</strong> If you're a new business, you can start without GST and add it later. We'll help you display tax-inclusive/exclusive pricing based on your selection.
                    </div>
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
                        <span class="segmented-desc">I'm a new or small business</span>
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
                        <span class="segmented-desc">I'm GST registered</span>
                    </label>
                </div>

                <div class="seller-ob-grid">

                    {{-- GSTIN --}}
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

                        <span class="field-hint">
                            15-character GSTIN format: 2-digit state code + 10-digit PAN + 1 entity code + 1 check digit + "Z" + 1 check digit.
                        </span>

                        @error('gstin')
                            <span class="seller-ob-error">{{ $message }}</span>
                        @enderror
                    </label>

                    {{-- PAN --}}
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

                        <span class