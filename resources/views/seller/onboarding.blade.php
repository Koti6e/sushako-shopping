@php
    $settings = $vendor->delivery_settings ?? [];
    $gstRegistered = old('gst_registered', ($vendor->gst_status === 'registered' || filled($vendor->gstin)) ? 'yes' : 'no');
    $pickupSame = (bool) old('pickup_same_as_business', data_get($settings, 'use_business_address', true));
    $returnsAccepted = old('returns_accepted', $vendor->returns_accepted ? 'yes' : 'no');
    $sellerPhoneValue = old('phone', preg_replace('/\D+/', '', $vendor->phone ?: auth()->user()->phone ?: ''));
@endphp

<x-layouts.seller title="Business Setup" :minimal="true">
    <section class="seller-onboarding-production seller-simple-onboarding" data-simple-onboarding>
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <header class="seller-onboarding-hero">
            <div>
                <p class="eyebrow">Sushako Seller OS</p>
                <h1>Your shop is almost ready.</h1>
                <p>Tell us a few details and start selling with Sushako.</p>
            </div>
            <span class="seller-autosave-status">One final step</span>
        </header>

        <form class="seller-onboarding-card" method="POST" action="{{ route('seller.onboarding.store') }}" enctype="multipart/form-data" data-onboarding-form novalidate>
            @csrf

            <section class="seller-ob-section">
                <div class="seller-ob-section__heading">
                    <h2>Business Details</h2>
                    <p>Use the name and mobile number customers can recognise.</p>
                </div>

                <div class="seller-ob-grid">
                    <label class="seller-ob-field seller-field--wide">Business Name
                        <input name="business_name" value="{{ old('business_name', $vendor->business_name) }}" minlength="3" maxlength="100" required autocomplete="organization">
                        @error('business_name')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field seller-field--wide">Business Address
                        <textarea name="business_address" rows="3" required>{{ old('business_address', $vendor->address_line_1) }}</textarea>
                        @error('business_address')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Pincode
                        <input name="postal_code" inputmode="numeric" maxlength="6" pattern="\d{6}" value="{{ old('postal_code', $vendor->postal_code) }}" required data-digits-only>
                        @error('postal_code')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Phone Number
                        <span class="seller-phone-field"><b>+91</b><input name="phone" inputmode="numeric" maxlength="10" pattern="[6-9]\d{9}" value="{{ $sellerPhoneValue }}" required data-digits-only></span>
                        @error('phone')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">City
                        <input name="city" value="{{ old('city', $vendor->city) }}" autocomplete="address-level2">
                        @error('city')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">State
                        <select name="state">
                            @foreach ($indianStates as $stateName)
                                <option value="{{ $stateName }}" @selected(old('state', $vendor->state ?: 'Tamil Nadu') === $stateName)>{{ $stateName }}</option>
                            @endforeach
                        </select>
                        @error('state')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field seller-field--wide">Business Logo <span>(Optional)</span>
                        <input type="file" name="business_logo" accept="image/*" data-logo-input>
                        <small data-logo-preview>{{ $vendor->business_logo_path ? 'Current logo saved' : 'PNG, JPG, or WEBP up to 2 MB' }}</small>
                        @error('business_logo')<b>{{ $message }}</b>@enderror
                    </label>
                </div>
            </section>

            <section class="seller-ob-section">
                <div class="seller-ob-section__heading">
                    <h2>Tax Details</h2>
                    <p>Choose the option that matches your business today.</p>
                </div>

                <div class="seller-segmented" role="radiogroup" aria-label="GST registration">
                    <label @class(['is-selected' => $gstRegistered === 'yes']) data-gst-option>
                        <input type="radio" name="gst_registered" value="yes" @checked($gstRegistered === 'yes')>
                        <span>Yes, I have GST</span>
                    </label>
                    <label @class(['is-selected' => $gstRegistered === 'no']) data-gst-option>
                        <input type="radio" name="gst_registered" value="no" @checked($gstRegistered === 'no')>
                        <span>No GST</span>
                    </label>
                </div>

                <div class="seller-ob-grid">
                    <label class="seller-ob-field seller-field--wide" data-gstin-field>GSTIN
                        <input name="gstin" value="{{ old('gstin', $vendor->gstin) }}" maxlength="15" autocomplete="off">
                        @error('gstin')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field seller-field--wide" data-pan-field>PAN Number
                        <input name="pan_number" value="{{ old('pan_number', $vendor->pan_number) }}" maxlength="10" autocomplete="off">
                        @error('pan_number')<b>{{ $message }}</b>@enderror
                    </label>
                </div>
            </section>

            <section class="seller-ob-section">
                <div class="seller-ob-section__heading">
                    <h2>Pickup Address</h2>
                    <p>Delivery distance is calculated from where orders are picked up.</p>
                </div>

                <label class="seller-toggle-row">
                    <input type="hidden" name="pickup_same_as_business" value="0">
                    <input type="checkbox" name="pickup_same_as_business" value="1" @checked($pickupSame) data-pickup-toggle>
                    <span>Pickup address is same as business address</span>
                </label>

                <div class="seller-ob-grid" data-pickup-fields @if ($pickupSame) hidden @endif>
                    <label class="seller-ob-field seller-field--wide">Pickup Address
                        <textarea name="pickup_address" rows="3">{{ old('pickup_address', $vendor->pickup_address_line_1) }}</textarea>
                        @error('pickup_address')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Pickup Pincode
                        <input name="pickup_postal_code" inputmode="numeric" maxlength="6" pattern="\d{6}" value="{{ old('pickup_postal_code', $vendor->pickup_postal_code) }}" data-digits-only data-pincode="pickup">
                        @error('pickup_postal_code')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Pickup City
                        <input name="pickup_city" value="{{ old('pickup_city', $vendor->pickup_city) }}">
                        @error('pickup_city')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Pickup State
                        <select name="pickup_state">
                            @foreach ($indianStates as $stateName)
                                <option value="{{ $stateName }}" @selected(old('pickup_state', $vendor->pickup_state ?: 'Tamil Nadu') === $stateName)>{{ $stateName }}</option>
                            @endforeach
                        </select>
                        @error('pickup_state')<b>{{ $message }}</b>@enderror
                    </label>
                </div>
            </section>

            <section class="seller-ob-section">
                <div class="seller-ob-section__heading">
                    <h2>Delivery Setup</h2>
                    <p>Keep it simple. Customers will see this clearly before checkout.</p>
                </div>

                <div class="seller-ob-grid">
                    <label class="seller-ob-field">Delivery Radius
                        <span class="seller-money-field"><input type="number" min="1" max="500" step="1" name="delivery_radius" value="{{ old('delivery_radius', $vendor->delivery_radius ?: 10) }}" required inputmode="numeric" data-delivery-preview-input><b>km</b></span>
                        @error('delivery_radius')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Delivery Charge
                        <span class="seller-money-field"><b>INR ₹</b><input type="number" min="0" step="0.01" name="flat_shipping_charge" value="{{ old('flat_shipping_charge', $vendor->flat_shipping_charge ?? 40) }}" required inputmode="decimal" data-delivery-preview-input></span>
                        @error('flat_shipping_charge')<b>{{ $message }}</b>@enderror
                    </label>

                    <label class="seller-ob-field">Free Shipping Above
                        <span class="seller-money-field"><b>INR ₹</b><input type="number" min="0" step="0.01" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', $vendor->free_shipping_threshold ?? 999) }}" inputmode="decimal" data-delivery-preview-input></span>
                        @error('free_shipping_threshold')<b>{{ $message }}</b>@enderror
                    </label>
                </div>

                <p class="seller-delivery-preview" data-delivery-preview>₹40 delivery within 10 km · FREE above ₹999</p>
            </section>

            <section class="seller-ob-section">
                <div class="seller-ob-section__heading">
                    <h2>Return Policy</h2>
                    <p>No returns still keeps customers protected for wrong, damaged, missing, counterfeit, or undelivered orders.</p>
                </div>

                <div class="seller-segmented" role="radiogroup" aria-label="Return policy">
                    <label @class(['is-selected' => $returnsAccepted === 'yes']) data-return-option>
                        <input type="radio" name="returns_accepted" value="yes" @checked($returnsAccepted === 'yes')>
                        <span>Yes, accept returns</span>
                    </label>
                    <label @class(['is-selected' => $returnsAccepted === 'no']) data-return-option>
                        <input type="radio" name="returns_accepted" value="no" @checked($returnsAccepted === 'no')>
                        <span>No Returns</span>
                    </label>
                </div>

                <label class="seller-ob-field seller-field--wide" data-return-window-field>Returns accepted within
                    <select name="return_window_days">
                        @foreach ([1, 3, 5, 7] as $days)
                            <option value="{{ $days }}" @selected((int) old('return_window_days', $vendor->return_window_days ?: 3) === $days)>{{ $days }} {{ str('day')->plural($days) }} from delivery</option>
                        @endforeach
                    </select>
                    @error('return_window_days')<b>{{ $message }}</b>@enderror
                </label>
            </section>

            <footer class="seller-onboarding-footer">
                <a href="{{ route('home') }}">Back to Shop</a>
                <button type="submit" data-primary-submit>Let's Go to My Shop →</button>
            </footer>
        </form>
    </section>

    <script>
        (() => {
            const root = document.querySelector('[data-simple-onboarding]');
            if (!root) return;

            const syncTaxFields = () => {
                const value = root.querySelector('input[name="gst_registered"]:checked')?.value || 'no';
                root.querySelectorAll('[data-gst-option]').forEach((label) => {
                    label.classList.toggle('is-selected', label.querySelector('input')?.value === value);
                });
                const gstin = root.querySelector('[data-gstin-field]');
                const pan = root.querySelector('[data-pan-field]');
                if (gstin) gstin.hidden = value !== 'yes';
                if (pan) pan.hidden = value !== 'no';
            };
            const syncPickup = () => {
                const same = root.querySelector('[data-pickup-toggle]')?.checked ?? true;
                const fields = root.querySelector('[data-pickup-fields]');
                if (fields) fields.hidden = same;
            };
            const syncReturns = () => {
                const value = root.querySelector('input[name="returns_accepted"]:checked')?.value || 'no';
                root.querySelectorAll('[data-return-option]').forEach((label) => {
                    label.classList.toggle('is-selected', label.querySelector('input')?.value === value);
                });
                const field = root.querySelector('[data-return-window-field]');
                if (field) field.hidden = value !== 'yes';
            };
            const syncDeliveryPreview = () => {
                const radius = root.querySelector('input[name="delivery_radius"]')?.value || '10';
                const charge = root.querySelector('input[name="flat_shipping_charge"]')?.value || '0';
                const free = root.querySelector('input[name="free_shipping_threshold"]')?.value || '';
                const preview = root.querySelector('[data-delivery-preview]');
                if (preview) preview.textContent = `₹${Number(charge || 0).toLocaleString('en-IN')} delivery within ${radius} km${free !== '' ? ` · FREE above ₹${Number(free).toLocaleString('en-IN')}` : ''}`;
            };
            const pincodeMap = {
                '600001': ['Chennai', 'Tamil Nadu'],
                '600042': ['Chennai', 'Tamil Nadu'],
                '110001': ['New Delhi', 'Delhi'],
                '400001': ['Mumbai', 'Maharashtra'],
                '560001': ['Bengaluru', 'Karnataka'],
                '700001': ['Kolkata', 'West Bengal']
            };
            const fillCityState = (input) => {
                const match = pincodeMap[input.value];
                if (!match) return;
                const prefix = input.dataset.pincode === 'pickup' ? 'pickup_' : '';
                const city = root.querySelector(`[name="${prefix}city"]`);
                const state = root.querySelector(`[name="${prefix}state"]`);
                if (city && !city.value) city.value = match[0];
                if (state) state.value = match[1];
            };

            root.querySelectorAll('input[name="gst_registered"]').forEach((input) => input.addEventListener('change', syncTaxFields));
            root.querySelector('[data-pickup-toggle]')?.addEventListener('change', syncPickup);
            root.querySelectorAll('input[name="returns_accepted"]').forEach((input) => input.addEventListener('change', syncReturns));
            root.querySelectorAll('[data-delivery-preview-input]').forEach((input) => input.addEventListener('input', syncDeliveryPreview));
            root.querySelectorAll('[data-digits-only]').forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D+/g, '').slice(0, input.maxLength || 10);
                    if (input.value.length === Number(input.maxLength || 0)) fillCityState(input);
                });
            });
            root.querySelector('[data-logo-input]')?.addEventListener('change', (event) => {
                const label = root.querySelector('[data-logo-preview]');
                if (label) label.textContent = event.target.files?.[0]?.name || 'PNG, JPG, or WEBP up to 2 MB';
            });
            root.querySelector('[data-onboarding-form]')?.addEventListener('submit', () => {
                const button = root.querySelector('[data-primary-submit]');
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Setting up your shop...';
                }
            });
            syncTaxFields();
            syncPickup();
            syncReturns();
            syncDeliveryPreview();
        })();
    </script>
</x-layouts.seller>
