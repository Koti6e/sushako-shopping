<x-layouts.seller title="Settings">
    <x-seller.header title="Settings" subtitle="Policies, tax preference, working hours, holidays, and store visibility." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <form class="seller-form-card seller-form-card--sectioned" method="POST" action="{{ route('seller.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="seller-settings-section seller-settings-section--full">
                <div class="seller-settings-section__heading">
                    <h2>Legal Policies</h2>
                    <p>Keep the legal acknowledgements up to date and clearly visible.</p>
                </div>
                <div class="seller-policy-list" role="list">
                    @foreach ($policies as $key => $label)
                        @php $isAccepted = in_array($key, old('policies', $acceptedPolicies), true); @endphp
                        <label class="seller-policy-item" role="listitem">
                            <input type="checkbox" name="policies[]" value="{{ $key }}" @checked($isAccepted)>
                            <span class="seller-policy-item__body">
                                <strong>{{ $label }}</strong>
                                <small>{{ match ($key) {
                                    'seller_agreement' => 'Review the seller agreement before publishing your storefront.',
                                    'privacy_policy' => 'Confirm that customer data is handled securely and transparently.',
                                    'commission_policy' => 'Keep commission expectations aligned with the active plan.',
                                    'return_refund_policy' => 'Share clear return and refund guidance for buyers.',
                                    default => 'Policy acknowledgement',
                                } }}</small>
                            </span>
                            <span class="seller-policy-item__state">{{ $isAccepted ? 'Accepted' : 'Pending' }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="seller-settings-section seller-settings-section--split">
                <div class="seller-settings-section__heading">
                    <h2>Tax Settings</h2>
                    <p>Choose how taxes are presented and how the store appears to buyers.</p>
                </div>
                <label class="seller-form-field">
                    <span>Tax preference</span>
                    <select name="tax_preference" required>
                        <option value="">Select preference</option>
                        <option value="gst_included" @selected(old('tax_preference', $vendor->tax_preference) === 'gst_included')>GST Included</option>
                        <option value="gst_excluded" @selected(old('tax_preference', $vendor->tax_preference) === 'gst_excluded')>GST Excluded</option>
                    </select>
                </label>
                <label class="seller-form-field">
                    <span>Store visibility</span>
                    <select name="store_visibility" required>
                        <option value="draft" @selected(old('store_visibility', $vendor->store_visibility) === 'draft')>Draft</option>
                        <option value="published" @selected(old('store_visibility', $vendor->store_visibility) === 'published')>Published</option>
                        <option value="hidden" @selected(old('store_visibility', $vendor->store_visibility) === 'hidden')>Hidden</option>
                    </select>
                </label>
            </div>

            <div class="seller-settings-section seller-settings-section--full">
                <div class="seller-settings-section__heading">
                    <h2>Working Hours</h2>
                    <p>Choose which days are active and set the standard opening and closing time for those days.</p>
                </div>
                <div class="seller-working-hours-grid" role="list">
                    @foreach (['mon' => 'Monday','tue' => 'Tuesday','wed' => 'Wednesday','thu' => 'Thursday','fri' => 'Friday','sat' => 'Saturday','sun' => 'Sunday'] as $key => $label)
                        @php $isOpen = in_array($key, old('working_days', $vendor->working_days ?? []), true); @endphp
                        <label class="seller-working-hours-day" role="listitem">
                            <span class="seller-working-hours-day__name">{{ $label }}</span>
                            <span class="seller-working-hours-day__toggle">
                                <input type="checkbox" name="working_days[]" value="{{ $key }}" @checked($isOpen)>
                                <span>{{ $isOpen ? 'Open' : 'Closed' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="seller-working-hours-time-row">
                    <label class="seller-form-field">
                        <span>Opening time</span>
                        <input type="time" name="opens_at" value="{{ old('opens_at', $vendor->opens_at) }}">
                    </label>
                    <label class="seller-form-field">
                        <span>Closing time</span>
                        <input type="time" name="closes_at" value="{{ old('closes_at', $vendor->closes_at) }}">
                    </label>
                </div>
                <p class="seller-note">Closed days stay disabled in the visible list, while the shared opening and closing times continue to be saved with the existing seller settings flow.</p>
            </div>

            <div class="seller-settings-section seller-settings-section--split">
                <div class="seller-settings-section__heading">
                    <h2>Store Holidays</h2>
                    <p>Add occasional closures without disrupting the rest of the store setup.</p>
                </div>
                <label class="seller-form-field">
                    <span>Holiday date</span>
                    <input type="date" name="holiday_date" value="{{ old('holiday_date') }}">
                </label>
                <label class="seller-form-field">
                    <span>Holiday title</span>
                    <input name="holiday_title" value="{{ old('holiday_title') }}" placeholder="Festival, maintenance, personal closure">
                </label>
                <label class="seller-form-field seller-form-field--full">
                    <span>Holiday note</span>
                    <textarea name="holiday_note">{{ old('holiday_note') }}</textarea>
                </label>
                <div class="seller-form-field seller-form-field--full">
                    <span>Current holidays</span>
                    <div class="seller-list-card">
                        @forelse ($vendor->holidays as $holiday)
                            <div class="seller-row-link"><span>{{ $holiday->holiday_date->format('d M Y') }}</span><strong>{{ $holiday->title }}</strong></div>
                        @empty
                            <p class="seller-note">No holidays configured.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="seller-settings-section seller-settings-section--full">
                <div class="seller-settings-section__heading">
                    <h2>Operating Preferences</h2>
                    <p>Keep the storefront status and vacation message consistent for buyers.</p>
                </div>
                <label class="seller-checkbox seller-checkbox--stacked">
                    <input type="checkbox" name="vacation_auto_reactivate" value="1" @checked(old('vacation_auto_reactivate', $vendor->vacation_auto_reactivate))>
                    <span>Auto-reactivate store after vacation dates</span>
                </label>
                <label class="seller-form-field seller-form-field--full">
                    <span>Vacation message</span>
                    <textarea name="vacation_message">{{ old('vacation_message', $vendor->vacation_message) }}</textarea>
                </label>
            </div>

            <div class="seller-form-actions">
                <button type="submit">Save Settings</button>
            </div>
        </form>
    </section>
</x-layouts.seller>
