<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Services\SellerAccountService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SellerAuthScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_login_screen_uses_dedicated_auth_links(): void
    {
        $this->get(route('seller.login'))
            ->assertOk()
            ->assertSee('Seller Login')
            ->assertSee('Continue with Google')
            ->assertSee('Secured by Sushako')
            ->assertSee('google-auth-button')
            ->assertSee(route('seller.register'))
            ->assertSee(route('seller.password.request'))
            ->assertDontSee(route('register'));
    }

    public function test_seller_registration_creates_seller_and_redirects_to_onboarding(): void
    {
        $this->post(route('seller.register.store'), [
            'name' => 'Seller Founder',
            'email' => 'seller-founder@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('seller.onboarding'));

        $user = User::query()->where('email', 'seller-founder@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_SELLER, $user->role);
        $this->assertDatabaseHas('vendors', [
            'user_id' => $user->id,
            'onboarding_status' => 'draft',
        ]);
    }

    public function test_inactive_seller_cannot_login_with_password(): void
    {
        User::factory()->create([
            'email' => 'blocked-seller@example.test',
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_INACTIVE,
            'password' => Hash::make('Password123!'),
        ]);

        $this->post(route('seller.login.store'), [
            'email' => 'blocked-seller@example.test',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_seller_password_link_only_notifies_active_seller_accounts(): void
    {
        Notification::fake();

        $seller = User::factory()->create([
            'email' => 'reset-seller@example.test',
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customer = User::factory()->create([
            'email' => 'reset-customer@example.test',
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->post(route('seller.password.email'), ['email' => $seller->email])
            ->assertSessionHas('status');
        $this->post(route('seller.password.email'), ['email' => $customer->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($seller, ResetPassword::class);
        Notification::assertNotSentTo($customer, ResetPassword::class);
    }

    public function test_google_callback_creates_new_seller_without_duplicate_email(): void
    {
        $this->mockSellerGoogleUser('google-seller-new', 'Google Seller', 'google-seller@example.test');

        $this->get(route('seller.auth.google.callback'))
            ->assertRedirect(route('seller.onboarding'));

        $user = User::query()->where('email', 'google-seller@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_SELLER, $user->role);
        $this->assertSame('google-seller-new', $user->google_id);
        $this->assertSame(1, User::query()->where('email', 'google-seller@example.test')->count());
    }

    public function test_google_callback_links_existing_seller_and_uses_onboarding_redirect_rules(): void
    {
        $seller = User::factory()->create([
            'email' => 'existing-seller@example.test',
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_ACTIVE,
            'google_id' => null,
        ]);

        app(SellerAccountService::class)->ensureVendor($seller)->update([
            'business_name' => 'Existing Seller',
            'onboarding_status' => 'complete',
            'current_plan' => Vendor::PLAN_FREE,
            'selected_plan' => Vendor::PLAN_FREE,
            'selected_plan_slug' => Vendor::PLAN_FREE,
            'plan_status' => Vendor::PLAN_ACTIVE,
            'payment_status' => Vendor::PAYMENT_NOT_REQUIRED,
            'dashboard_access_enabled' => true,
        ]);

        $this->mockSellerGoogleUser('google-existing-seller', 'Existing Seller', 'existing-seller@example.test');

        $this->get(route('seller.auth.google.callback'))
            ->assertRedirect(route('seller.dashboard'));

        $this->assertAuthenticatedAs($seller);
        $this->assertSame('google-existing-seller', $seller->refresh()->google_id);
        $this->assertSame(1, User::query()->where('email', 'existing-seller@example.test')->count());
    }

    public function test_google_callback_rejects_inactive_seller(): void
    {
        User::factory()->create([
            'email' => 'inactive-google-seller@example.test',
            'role' => User::ROLE_SELLER,
            'status' => User::STATUS_SUSPENDED,
            'google_id' => null,
        ]);

        $this->mockSellerGoogleUser('google-inactive-seller', 'Inactive Seller', 'inactive-google-seller@example.test');

        $this->get(route('seller.auth.google.callback'))
            ->assertRedirect(route('seller.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'inactive-google-seller@example.test',
            'status' => User::STATUS_SUSPENDED,
            'google_id' => null,
        ]);
    }

    private function mockSellerGoogleUser(string $id, string $name, string $email): void
    {
        $googleUser = new SocialiteUser;
        $googleUser->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => 'https://example.test/avatar.jpg',
        ]);

        $provider = Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('redirectUrl')->once()->andReturnSelf();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->once()->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $socialite);
    }
}
