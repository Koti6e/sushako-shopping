<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_redirects_to_google(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->once()->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $socialite);

        $this->get(route('login.google'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_callback_creates_customer_and_requires_mobile_completion(): void
    {
        $this->mockGoogleUser('google-123', 'Google Customer', 'google-customer@example.test');

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('profile.complete'));

        $user = User::query()->where('email', 'google-customer@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNull($user->phone);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_customer_with_missing_mobile_cannot_access_protected_account_pages(): void
    {
        $customer = User::factory()->create([
            'phone' => null,
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($customer)->get(route('account.show'))->assertRedirect(route('profile.complete'));

        $this->actingAs($customer)->put(route('profile.complete.update'), [
            'phone' => '9876543210',
        ])->assertRedirect(route('account.show'));

        $this->assertSame('9876543210', $customer->refresh()->phone);
    }

    public function test_profile_completion_ignores_non_customer_intended_urls(): void
    {
        $customer = User::factory()->create([
            'phone' => null,
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($customer)
            ->withSession(['url.intended' => route('admin.dashboard')])
            ->put(route('profile.complete.update'), [
                'phone' => '9876543210',
            ])
            ->assertRedirect(route('account.show'));

        $this->assertSame('9876543210', $customer->refresh()->phone);
    }

    public function test_customer_registration_requires_mobile(): void
    {
        $this->post(route('register.store'), [
            'name' => 'No Phone Customer',
            'email' => 'missing-phone@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('phone');
    }

    public function test_profile_completion_requires_unique_mobile(): void
    {
        User::factory()->create(['phone' => '9876543210']);
        $customer = User::factory()->create(['phone' => null]);

        $this->actingAs($customer)->put(route('profile.complete.update'), [
            'phone' => '9876543210',
        ])->assertSessionHasErrors('phone');
    }

    public function test_google_callback_links_existing_customer(): void
    {
        $customer = User::factory()->create([
            'email' => 'existing@example.test',
            'phone' => '9876543211',
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'google_id' => null,
        ]);

        $this->mockGoogleUser('google-existing', 'Existing Customer', 'existing@example.test');

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('account.show'));

        $this->assertAuthenticatedAs($customer);
        $this->assertSame('google-existing', $customer->refresh()->google_id);
    }

    public function test_google_callback_rejects_super_admin_email(): void
    {
        User::factory()->create([
            'email' => 'superadmin@example.test',
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'password' => Hash::make('Password123!'),
        ]);

        $this->mockGoogleUser('google-admin', 'Super Admin', 'superadmin@example.test');

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_google_callback_handles_oauth_failure(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andThrow(new \RuntimeException('OAuth failed'));

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->once()->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $socialite);

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function mockGoogleUser(string $id, string $name, string $email): void
    {
        $googleUser = new SocialiteUser;
        $googleUser->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => 'https://example.test/avatar.jpg',
        ]);

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->once()->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $socialite);
    }
}
