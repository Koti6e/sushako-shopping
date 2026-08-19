<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SellerAccountService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SellerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('seller.auth.login', ['mode' => 'login']);
    }

    public function showRegister(): View
    {
        return view('seller.auth.login', ['mode' => 'register']);
    }

    public function showForgotPassword(): View
    {
        return view('seller.auth.login', ['mode' => 'forgot']);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('seller.auth.login', [
            'mode' => 'reset',
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirectUrl($this->googleRedirectUri())
            ->redirect();
    }

    public function login(Request $request, SellerAccountService $sellerAccounts): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || $user->role !== User::ROLE_SELLER || $user->status !== User::STATUS_ACTIVE || ! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => $user && $user->role === User::ROLE_SELLER && $user->status !== User::STATUS_ACTIVE
                    ? 'This seller account is not active. Please contact Sushako support.'
                    : 'These credentials do not match a seller account.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        $vendor = $sellerAccounts->ensureVendor($user);

        return redirect()->intended($sellerAccounts->redirectFor($vendor));
    }

    public function register(Request $request, SellerAccountService $sellerAccounts): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $vendor = $sellerAccounts->ensureVendor($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($sellerAccounts->redirectFor($vendor));
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', User::ROLE_SELLER)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        if ($user) {
            ResetPassword::createUrlUsing(fn (User $notifiable, string $token): string => route('seller.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]));

            Password::sendResetLink($data);
        }

        return back()->with('status', 'If an active seller account exists for this email, reset instructions will be sent.');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', User::ROLE_SELLER)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'We could not reset a password for this seller account.',
            ]);
        }

        $status = Password::reset($data, function (User $user, string $password): void {
            if ($user->role !== User::ROLE_SELLER || $user->status !== User::STATUS_ACTIVE) {
                return;
            }

            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return redirect()->route('seller.login')->with('status', 'Your seller password has been reset. You can sign in now.');
    }

    public function callback(SellerAccountService $sellerAccounts): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->googleRedirectUri())
                ->stateless()
                ->user();
        } catch (Throwable) {
            return redirect()->route('seller.login')->withErrors([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        if (! $googleUser->getEmail()) {
            return redirect()->route('seller.login')->withErrors([
                'email' => 'Google did not return an email address for this account.',
            ]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user && $user->role !== User::ROLE_SELLER) {
            return redirect()->route('seller.login')->withErrors([
                'email' => 'This email is already used for another Sushako account. Use a dedicated seller email.',
            ]);
        }

        if ($user && $user->status !== User::STATUS_ACTIVE) {
            return redirect()->route('seller.login')->withErrors([
                'email' => 'This seller account is not active. Please contact Sushako support.',
            ]);
        }

        $user ??= new User([
            'email' => $googleUser->getEmail(),
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_ACTIVE,
            'password' => Hash::make(Str::random(40)),
        ]);

        $user->forceFill([
            'name' => $googleUser->getName() ?: $user->name ?: Str::before($googleUser->getEmail(), '@'),
            'role' => User::ROLE_SELLER,
            'status' => $user->status ?: User::STATUS_ACTIVE,
            'google_id' => $googleUser->getId(),
            'google_avatar' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?: now(),
            'last_login_at' => now(),
        ])->save();

        $vendor = $sellerAccounts->ensureVendor($user);

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended($sellerAccounts->redirectFor($vendor));
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('seller.login');
    }

    private function googleRedirectUri(): string
    {
        return rtrim((string) config('app.url'), '/').'/seller/auth/google/callback';
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        abort(429, 'Too many seller login attempts. Please try again in '.RateLimiter::availableIn($this->throttleKey($request)).' seconds.');
    }

    private function throttleKey(Request $request): string
    {
        return 'seller|'.strtolower((string) $request->input('email')).'|'.$request->ip();
    }
}
