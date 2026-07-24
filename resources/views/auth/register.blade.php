<x-layouts.customer title="Register - Sushako Shopping">
    <section class="auth-screen">
        <form class="auth-panel" method="POST" action="{{ route('register.store') }}">
            @csrf
            <x-brand.logo context="auth" loading="eager" />
            <p class="eyebrow">Customer Registration</p>
            <h1>Create your Sushako account</h1>
            @if ($errors->any())
                <div class="status-banner">{{ $errors->first() }}</div>
            @endif
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            <label for="phone">Mobile Number</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel">
            <label class="policy-agreement">
                <input name="phone_is_whatsapp" type="checkbox" value="1" @checked(old('phone_is_whatsapp'))>
                <span>This mobile number is also my WhatsApp number for order updates and support.</span>
            </label>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            <button type="submit" class="button button--primary">Create Account</button>
            <a href="{{ route('login') }}" class="button button--secondary">I Already Have An Account</a>
        </form>
    </section>
</x-layouts.customer>
