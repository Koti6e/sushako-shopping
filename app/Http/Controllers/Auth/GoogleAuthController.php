<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        if (! $googleUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google did not return an email address for this account.',
            ]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user && $user->role !== User::ROLE_CUSTOMER) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is available for customer accounts only.',
            ]);
        }

        $user ??= new User([
            'email' => $googleUser->getEmail(),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'password' => Hash::make(Str::password(32)),
        ]);

        if ($user->status !== User::STATUS_ACTIVE) {
            return redirect()->route('login')->withErrors([
                'email' => 'This account is not active. Please contact Sushako support.',
            ]);
        }

        $user->forceFill([
            'name' => $googleUser->getName() ?: $user->name ?: Str::before($googleUser->getEmail(), '@'),
            'google_id' => $googleUser->getId(),
            'google_avatar' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?: now(),
            'last_login_at' => now(),
        ])->save();

        Auth::login($user);
        request()->session()->regenerate();

        if (! $user->phone) {
            return redirect()->route('profile.complete');
        }

        return redirect()->intended(route('account.show'));
    }
}
