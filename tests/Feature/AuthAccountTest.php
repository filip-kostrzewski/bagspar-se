<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_company_account_and_starts_a_session(): void
    {
        Notification::fake();

        $response = $this->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => '  ANNA@EXAMPLE.TEST ',
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
        ]);

        $user = User::query()->where('email', 'anna@example.test')->firstOrFail();

        $response->assertRedirect(route('app.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Fönsterverkstan', $user->workshop->name);
        $this->assertTrue($user->workshop->trial_ends_at->between(now()->addMonths(3)->subMinute(), now()->addMonths(3)->addMinute()));
        $this->assertTrue(Hash::check('correct-horse-5', $user->password));
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_short_and_overlong_byte_passwords(): void
    {
        $this->from(route('register'))->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => 'anna@example.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $longPassword = str_repeat('å', 37);
        $this->from(route('register'))->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => 'anna@example.test',
            'password' => $longPassword,
            'password_confirmation' => $longPassword,
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('workshops', 0);
    }

    public function test_authentication_email_fields_reject_array_input_without_server_errors(): void
    {
        $this->from(route('register'))->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => ['anna@example.test'],
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
        ])->assertSessionHasErrors('email');

        $this->from(route('login'))->post(route('auth.login.store'), [
            'email' => ['anna@example.test'],
            'password' => 'correct-horse-5',
        ])->assertSessionHasErrors('email');

        $this->from(route('auth.password.request'))->post(route('auth.password.email'), [
            'email' => ['anna@example.test'],
        ])->assertSessionHasErrors('email');

        $this->get(route('auth.password.reset', ['token' => 'invalid']).'?email%5B%5D=anna%40example.test')
            ->assertOk();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('workshops', 0);
    }

    public function test_login_normalizes_email_and_throttles_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'anna@example.test',
            'password' => Hash::make('correct-horse-5'),
        ]);

        $this->post(route('auth.login.store'), [
            'email' => ' ANNA@EXAMPLE.TEST ',
            'password' => 'correct-horse-5',
        ])->assertRedirect(route('app.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->post(route('auth.logout'));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))->post(route('auth.login.store'), [
                'email' => 'anna@example.test',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->from(route('login'))->post(route('auth.login.store'), [
            'email' => 'anna@example.test',
            'password' => 'correct-horse-5',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_clears_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('auth.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_password_reset_response_does_not_reveal_whether_an_email_exists(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'owner@example.test']);

        $knownResponse = $this->from(route('auth.password.request'))->post(route('auth.password.email'), [
            'email' => ' OWNER@EXAMPLE.TEST ',
        ]);
        $knownStatus = $knownResponse->getSession()->get('status');

        $unknownResponse = $this->from(route('auth.password.request'))->post(route('auth.password.email'), [
            'email' => 'missing@example.test',
        ]);

        $knownResponse->assertRedirect(route('auth.password.request'));
        $unknownResponse->assertRedirect(route('auth.password.request'));
        $unknownResponse->assertSessionHas('status', $knownStatus);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_a_valid_password_reset_token_updates_the_password(): void
    {
        $user = User::factory()->create();
        $originalRememberToken = $user->remember_token;
        $token = Password::createToken($user);

        $this->post(route('auth.password.update'), [
            'token' => $token,
            'email' => strtoupper($user->email),
            'password' => 'a-new-secure-pass-8',
            'password_confirmation' => 'a-new-secure-pass-8',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('a-new-secure-pass-8', $user->password));
        $this->assertNotSame($originalRememberToken, $user->remember_token);
    }

    public function test_an_existing_session_is_rejected_after_a_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.test',
            'password' => Hash::make('original-secure-pass-4'),
        ]);

        $this->post(route('auth.login.store'), [
            'email' => $user->email,
            'password' => 'original-secure-pass-4',
        ])->assertRedirect(route('app.dashboard'));
        $this->get(route('app.dashboard'))->assertOk();
        $oldSession = $this->app['session']->all();

        $token = Password::createToken($user);
        $this->actingAsGuest()->flushSession()
            ->post(route('auth.password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'a-new-secure-pass-8',
                'password_confirmation' => 'a-new-secure-pass-8',
            ])->assertRedirect(route('login'));

        $this->actingAsGuest()->withSession($oldSession)
            ->get(route('app.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertTrue(Hash::check('a-new-secure-pass-8', $user->fresh()->password));
    }

    public function test_signed_email_verification_marks_the_authenticated_user_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($verificationUrl)
            ->assertRedirect(route('app.dashboard'))
            ->assertSessionHas('status', 'E-postadressen har verifierats.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_local_workshop_access_does_not_wait_for_email_delivery(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('app.dashboard'))->assertOk();
    }

    public function test_production_workshop_access_waits_for_signed_email_verification(): void
    {
        $user = User::factory()->unverified()->create();
        $this->app['env'] = 'production';

        $this->actingAs($user)->get(route('app.dashboard'))->assertRedirect(route('verification.notice'));
    }
}
