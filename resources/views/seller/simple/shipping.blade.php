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
            <fieldset class="seller-field--wide">
                <legend>Working days</legend>
                <div class="seller-working-hours-grid">
                    @foreach (['mon' => 'Monday','tue' => 'Tuesday','wed' => 'Wednesday','thu' => 'Thursday','fri' => 'Friday','sat' => 'Saturday','sun' => 'Sunday'] as $key => $label)
                        <label class="seller-working-hours-day">
                            <span class="seller-working-hours-day__name">{{ $label }}</span>
                            <span class="seller-working-hours-day__toggle">
                                <input type="checkbox" name="working_days[]" value="{{ $key }}" @checked(in_array($key, old('working_days', $vendor->working_days ?? []), true))>
                                <span>{{ in_array($key, old('working_days', $vendor->working_days ?? []), true) ? 'Open' : 'Closed' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <label>Opening time<input type="time" name="opens_at" value="{{ old('opens_at', $vendor->opens_at) }}"></label>
            <label>Closing time<input type="time" name="closes_at" value="{{ old('closes_at', $vendor->closes_at) }}"></label>
            <label class="seller-checkbox"><input type="checkbox" name="manual_tracking_enabled" value="1" @checked(old('manual_tracking_enabled', $vendor->manual_tracking_enabled))> Enable manual tracking updates</label>
            <div class="seller-form-actions"><button type="submit">Save Shipping Details</button></div>
        </form>
    </section>
</x-layouts.seller>
