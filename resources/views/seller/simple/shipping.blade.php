<x-layouts.seller title="Shipping">
    <x-seller.header title="Shipping" subtitle="Update pickup, return, and delivery settings used before publishing your store." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <form class="seller-form-card seller-form-card--sectioned" method="POST" action="{{ route('seller.shipping.update') }}">
            @csrf
            @method('PUT')
            <h2>Pickup and Return Details</h2>
            <label>Address line 1<input name="address_line_1" value="{{ old('address_line_1', $vendor->address_line_1) }}" required></label>
            <label>Address line 2<input name="address_line_2" value="{{ old('address_line_2', $vendor->address_line_2) }}"></label>
            <label>City<input name="city" value="{{ old('city', $vendor->city) }}" required></label>
            <label>District<input name="district" value="{{ old('district', $vendor->district) }}"></label>
            <label>State<input name="state" value="{{ old('state', $vendor->state) }}" required></label>
            <label>Pincode<input name="postal_code" value="{{ old('postal_code', $vendor->postal_code) }}" required></label>
            <label class="seller-field--wide">Pickup address<textarea name="pickup_address" required>{{ old('pickup_address', $vendor->pickup_address ?: $vendor->address_line_1) }}</textarea></label>
            <label class="seller-field--wide">Return address<textarea name="return_address" required>{{ old('return_address', $vendor->return_address ?: $vendor->address_line_1) }}</textarea></label>

            <h2>Delivery Settings</h2>
            <label>Shipping commitment<select name="shipping_commitment" required>@foreach (['ships_same_day' => 'Ships same day', 'ships_within_1_business_day' => 'Ships within 1 business day', 'ships_within_2_business_days' => 'Ships within 2 business days'] as $value => $label)<option value="{{ $value }}" @selected(old('shipping_commitment', $vendor->shipping_commitment) === $value)>{{ $label }}</option>@endforeach</select></label>
            <label>Preferred courier<input name="preferred_courier_name" value="{{ old('preferred_courier_name', $vendor->preferred_courier_name) }}"></label>
            <label>Flat shipping charge<input type="number" step="0.01" name="flat_shipping_charge" value="{{ old('flat_shipping_charge', $vendor->flat_shipping_charge) }}"></label>
            <label>Free shipping threshold<input type="number" step="0.01" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', $vendor->free_shipping_threshold) }}"></label>
            <label>Delivery radius<input type="number" name="delivery_radius" value="{{ old('delivery_radius', $vendor->delivery_radius) }}"></label>
            @php
                $selectedWorkingDays = old('working_days', $vendor->working_days ?? []);
                $openingTime = old('opens_at', $vendor->opens_at);
                $closingTime = old('closes_at', $vendor->closes_at);
                $formatTime = fn ($time) => filled($time) ? \Illuminate\Support\Carbon::createFromFormat('H:i', substr((string) $time, 0, 5))->format('h:i A') : null;
                $hoursLabel = $formatTime($openingTime) && $formatTime($closingTime)
                    ? $formatTime($openingTime).' -> '.$formatTime($closingTime)
                    : 'Set opening and closing time';
            @endphp
            <fieldset class="seller-field--wide seller-working-hours-panel">
                <legend>Working days</legend>
                <p>Choose the days your store accepts dispatch work. Open days use the business hours below.</p>
                <div class="seller-working-hours-grid" data-working-hours-grid data-hours-label="{{ $hoursLabel }}">
                    @foreach (['mon' => 'Monday','tue' => 'Tuesday','wed' => 'Wednesday','thu' => 'Thursday','fri' => 'Friday','sat' => 'Saturday','sun' => 'Sunday'] as $key => $label)
                        @php $isOpen = in_array($key, $selectedWorkingDays, true); @endphp
                        <label class="seller-working-hours-day" data-working-day>
                            <span class="seller-working-hours-day__name">{{ $label }}</span>
                            <span class="seller-working-hours-day__toggle">
                                <input type="checkbox" name="working_days[]" value="{{ $key }}" @checked($isOpen) data-working-day-toggle>
                                <span data-working-day-state>{{ $isOpen ? 'Open' : 'Closed' }}</span>
                            </span>
                            <span @class(['seller-working-hours-day__hours', 'is-closed' => ! $isOpen]) data-working-day-hours>{{ $isOpen ? $hoursLabel : 'Closed' }}</span>
                        </label>
                    @endforeach
                </div>
                @error('working_days')<span class="seller-field-error">{{ $message }}</span>@enderror
                @error('working_days.*')<span class="seller-field-error">{{ $message }}</span>@enderror
            </fieldset>
            <div class="seller-working-hours-time-row seller-field--wide">
                <label>Opening time<input type="time" name="opens_at" value="{{ $openingTime }}">@error('opens_at')<span class="seller-field-error">{{ $message }}</span>@enderror</label>
                <span class="seller-working-hours-arrow">to</span>
                <label>Closing time<input type="time" name="closes_at" value="{{ $closingTime }}">@error('closes_at')<span class="seller-field-error">{{ $message }}</span>@enderror</label>
            </div>
            <label class="seller-checkbox"><input type="checkbox" name="manual_tracking_enabled" value="1" @checked(old('manual_tracking_enabled', $vendor->manual_tracking_enabled))> Enable manual tracking updates</label>
            <div class="seller-form-actions"><button type="submit">Save Shipping Details</button></div>
        </form>
    </section>
    <script>
        (() => {
            const grid = document.querySelector('[data-working-hours-grid]');
            if (!grid) return;

            const refresh = (row) => {
                const checkbox = row.querySelector('[data-working-day-toggle]');
                const state = row.querySelector('[data-working-day-state]');
                const hours = row.querySelector('[data-working-day-hours]');
                const open = checkbox?.checked;
                if (state) state.textContent = open ? 'Open' : 'Closed';
                if (hours) {
                    hours.textContent = open ? (grid.dataset.hoursLabel || 'Set opening and closing time') : 'Closed';
                    hours.classList.toggle('is-closed', !open);
                }
            };

            grid.querySelectorAll('[data-working-day]').forEach((row) => {
                row.querySelector('[data-working-day-toggle]')?.addEventListener('change', () => refresh(row));
                refresh(row);
            });
        })();
    </script>
</x-layouts.seller>
