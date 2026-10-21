<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RegistrationTurnstileTest extends TestCase
{
    use RefreshDatabase;

    private const TURNSTILE_ERROR = 'Vi kunde inte bekräfta att du inte är en robot. Försök igen.';

    public function test_registration_page_renders_turnstile_when_keys_are_configured(): void
    {
        $this->enableTurnstile();

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('class="cf-turnstile"', false)
            ->assertSee('data-sitekey="site-key-test"', false)
            ->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false);
    }

    #[TestWith([null, null])]
    #[TestWith(['site-key-test', null])]
    #[TestWith([null, 'secret-key-test'])]
    public function test_registration_skips_turnstile_when_either_key_is_missing(?string $siteKey, ?string $secret): void
    {
        Notification::fake();
        Http::preventStrayRequests();
        config([
            'services.turnstile.site_key' => $siteKey,
            'services.turnstile.secret' => $secret,
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('cf-turnstile', false);

        $this->post(route('auth.register.store'), $this->validRegistration())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'anna@example.test']);
        Http::assertNothingSent();
    }

    public function test_registration_without_a_turnstile_token_is_rejected_in_swedish(): void
    {
        Http::preventStrayRequests();
        $this->enableTurnstile();

        $this->from(route('register'))
            ->post(route('auth.register.store'), $this->validRegistration())
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['turnstile' => self::TURNSTILE_ERROR]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('workshops', 0);
        Http::assertNothingSent();

        $this->from(route('register'))
            ->followingRedirects()
            ->post(route('auth.register.store'), $this->validRegistration())
            ->assertOk()
            ->assertSee(self::TURNSTILE_ERROR, false)
            ->assertSee('id="turnstile-error"', false);
    }

    public function test_registration_with_a_rejected_turnstile_token_is_rejected(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);
        $this->enableTurnstile();

        $this->from(route('register'))
            ->post(route('auth.register.store'), $this->validRegistration(['cf-turnstile-response' => 'invalid-token']))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['turnstile' => self::TURNSTILE_ERROR]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_is_rejected_when_turnstile_is_unreachable(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::failedConnection(),
        ]);
        $this->enableTurnstile();

        $this->from(route('register'))
            ->post(route('auth.register.store'), $this->validRegistration(['cf-turnstile-response' => 'turnstile-token']))
            ->assertSessionHasErrors(['turnstile' => self::TURNSTILE_ERROR]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_with_a_verified_turnstile_token_creates_the_account(): void
    {
        Notification::fake();
        Http::preventStrayRequests();
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);
        $this->enableTurnstile();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('auth.register.store'), $this->validRegistration(['cf-turnstile-response' => 'turnstile-token']))
            ->assertRedirect(route('app.dashboard'))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs(User::query()->where('email', 'anna@example.test')->firstOrFail());
        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && ($data['secret'] ?? null) === 'secret-key-test'
                && ($data['response'] ?? null) === 'turnstile-token'
                && ($data['remoteip'] ?? null) === '203.0.113.10';
        });
    }

    private function enableTurnstile(): void
    {
        config([
            'services.turnstile.site_key' => 'site-key-test',
            'services.turnstile.secret' => 'secret-key-test',
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validRegistration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => 'anna@example.test',
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
            'terms' => '1',
        ], $overrides);
    }
}
