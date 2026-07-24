<x-layouts.customer title="Complete Profile - Sushako Shopping">
    <section class="auth-screen">
        <form class="auth-panel" method="POST" action="{{ route('profile.complete.update') }}">
            @csrf
            @method('PUT')
            <x-brand.logo context="auth" loading="eager" />
            <p class="eyebrow">Complete Profile</p>
            <h1>Add your mobile number</h1>
            @if ($errors->any())
                <div class="status-banner">{{ $errors->first() }}</div>
            @endif
            <label for="phone">Mobile number</label>
            <div class="phone-field">
                <span>+91</span>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" inputmode="numeric" pattern="[6-9][0-9]{9}" placeholder="9876543210" required autocomplete="tel">
            </div>
            <label class="policy-agreement">
                <input name="phone_is_whatsapp" type="checkbox" value="1" @checked(old('phone_is_whatsapp', $user->phone_is_whatsapp))>
                <span>This mobile number is also my WhatsApp number for order updates and support.</span>
            </label>
            <button type="submit" class="button button--primary">Save Profile</button>
        </form>
    </section>
</x-layouts.customer>
