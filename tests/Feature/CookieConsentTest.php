<?php

namespace Tests\Feature;

use App\Models\CookieConsent;
use App\Models\User;
use App\Services\CookieConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_cookie_banner_shows_until_essential_consent_is_accepted(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Essential Cookies')
            ->assertSee('Accept Essential Cookies')
            ->assertSee(route('policies.cookie'));

        $this->withCookie(CookieConsentService::COOKIE_NAME, CookieConsentService::COOKIE_VALUE)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Accept Essential Cookies');
    }

    public function test_accepting_essential_cookies_stores_cookie_and_database_consent(): void
    {
        $this->post(route('cookie-consent.essential.store'))
            ->assertRedirect()
            ->assertCookie(CookieConsentService::COOKIE_NAME, CookieConsentService::COOKIE_VALUE);

        $this->assertDatabaseHas('cookie_consents', [
            'consent_type' => CookieConsentService::CONSENT_TYPE,
            'accepted' => true,
            'user_id' => null,
        ]);

        $this->assertSame(1, CookieConsent::query()->count());
    }

    public function test_logged_in_customer_consent_is_associated_with_user(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'phone' => '9123456780',
        ]);

        $this->actingAs($user)
            ->post(route('cookie-consent.essential.store'))
            ->assertRedirect()
            ->assertCookie(CookieConsentService::COOKIE_NAME, CookieConsentService::COOKIE_VALUE);

        $this->assertDatabaseHas('cookie_consents', [
            'user_id' => $user->id,
            'consent_type' => CookieConsentService::CONSENT_TYPE,
            'accepted' => true,
        ]);
    }

    public function test_cookie_policy_explains_only_essential_cookies_are_used(): void
    {
        $this->get(route('policies.cookie'))
            ->assertOk()
            ->assertSee('Cookie Policy')
            ->assertSee('Authentication')
            ->assertSee('Shopping Cart')
            ->assertSee('CSRF Security')
            ->assertSee('No advertising, marketing or analytics cookies are used');
    }
}
