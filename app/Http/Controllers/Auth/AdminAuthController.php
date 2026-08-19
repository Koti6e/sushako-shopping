<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function entry(): RedirectResponse
    {
        if (Auth::user()?->hasRole(User::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::user()?->hasRole(User::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! $user->hasRole(User::ROLE_SUPER_ADMIN) || $user->status !== User::STATUS_ACTIVE || ! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => 'These credentials do not match the active super admin account.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
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
        return 'admin|'.strtolower((string) $request->input('email')).'|'.$request->ip();
    }
}
