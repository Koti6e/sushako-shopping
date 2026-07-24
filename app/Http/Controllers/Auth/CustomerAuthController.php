<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'phone_is_whatsapp' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'phone_is_whatsapp' => $request->boolean('phone_is_whatsapp'),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('account.show'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || $user->role !== User::ROLE_CUSTOMER || $user->status !== User::STATUS_ACTIVE || ! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => 'These credentials do not match an active customer account.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('account.show'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function account(): View
    {
        $user = request()->user();
        $addresses = $user->addresses()->latest('is_default')->latest()->get();
        $hasDefaultAddress = $addresses->contains(fn ($address): bool => (bool) $address->is_default);
        $avatarUrl = $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : $user->google_avatar;
        $completionItems = collect([
            'name' => filled($user->name),
            'email' => filled($user->email),
            'mobile' => filled($user->phone),
            'whatsapp' => (bool) $user->phone_is_whatsapp,
            'profile_photo' => filled($avatarUrl),
            'saved_address' => $hasDefaultAddress,
        ]);

        return view('account.show', [
            'user' => $user,
            'addresses' => $addresses,
            'avatarUrl' => $avatarUrl,
            'profileCompletion' => (int) round(($completionItems->filter()->count() / $completionItems->count()) * 100),
            'profileChecklist' => $completionItems,
            'wishlistCount' => count(request()->session()->get('wishlist', [])),
            'orders' => Order::query()
                ->with('items')
                ->where('user_id', $user->id)
                ->whereIn('status', ['placed', 'packing', 'shipped', 'delivered'])
                ->latest()
                ->get(),
        ]);
    }

    public function completeProfile(): View
    {
        return view('profile.complete', [
            'user' => request()->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->routeIs('profile.complete.update')
            ? $request->validate([
                'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
                'phone_is_whatsapp' => ['nullable', 'boolean'],
            ])
            : $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
                'phone_is_whatsapp' => ['nullable', 'boolean'],
                'avatar' => ['nullable', 'image', 'max:2048'],
            ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store('customer-avatars', 'public');
        }

        $user->forceFill(array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'],
            'phone_is_whatsapp' => $request->boolean('phone_is_whatsapp'),
            'avatar_path' => $data['avatar_path'] ?? null,
        ], fn ($value) => $value !== null))->save();

        if ($request->routeIs('profile.complete.update')) {
            $request->session()->forget('url.intended');

            return redirect()->route('account.show')->with('profile_status', 'Mobile number saved.');
        }

        return back()->with('profile_status', 'Profile updated.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validateAddress($request);

        if ($request->boolean('is_default') || ! $user->addresses()->exists()) {
            $user->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $user->addresses()->create($data);

        return back()->with('address_status', 'Address saved.');
    }

    public function setDefaultAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->forceFill(['is_default' => true])->save();

        return back()->with('address_status', 'Default address updated.');
    }

    public function deleteAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault && $nextAddress = $request->user()->addresses()->latest()->first()) {
            $nextAddress->forceFill(['is_default' => true])->save();
        }

        return back()->with('address_status', 'Address removed.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $key = 'password-update|'.$request->user()->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            abort(429, "Too many password change attempts. Please try again in {$seconds} seconds.");
        }

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            RateLimiter::hit($key);

            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        RateLimiter::clear($key);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        Auth::logoutOtherDevices($data['password']);

        return back()->with('password_status', 'Password updated.');
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        abort(429, "Too many login attempts. Please try again in {$seconds} seconds.");
    }

    private function throttleKey(Request $request): string
    {
        return strtolower((string) $request->input('email')).'|'.$request->ip();
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:180'],
            'address_line_2' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
            'landmark' => ['nullable', 'string', 'max:180'],
            'delivery_location_url' => ['nullable', 'url', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}
