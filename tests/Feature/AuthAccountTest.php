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

    public function test_login_page_omits_the_verification_notice_on_a_direct_visit(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Din e-post är verifierad.', false)
            ->assertDontSee('auth-notice', false);
    }

    public function test_registration_page_says_payment_waits_for_a_chosen_subscription(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Ingen betalning dras förrän du själv väljer att aktivera abonnemanget.', false)
            ->assertDontSee('Betalning kan börja först när den aktiveras manuellt', false);
    }

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

    public function test_auth_form_validation_errors_stay_swedish_when_application_locale_is_english(): void
    {
        $this->app->setLocale('en');

        $this->from(route('register'))->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => 'anna@example.test',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors(['password' => 'Ange ett lösenord.']);

        $this->from(route('register'))->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => ['anna@example.test'],
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
        ])->assertSessionHasErrors(['email' => 'Ange en giltig e-postadress.']);

        $this->from(route('login'))->post(route('auth.login.store'), [
            'email' => ['anna@example.test'],
            'password' => ['correct-horse-5'],
        ])->assertSessionHasErrors([
            'email' => 'Ange en giltig e-postadress.',
            'password' => 'Ange ditt lösenord som text.',
        ]);

        $this->from(route('auth.password.request'))->post(route('auth.password.email'), [
            'email' => ['anna@example.test'],
        ])->assertSessionHasErrors(['email' => 'Ange en giltig e-postadress.']);

        $this->from(route('auth.password.reset', ['token' => 'test-token']))
            ->post(route('auth.password.update'), [
                'token' => 'test-token',
                'email' => 'anna@example.test',
                'password' => '',
                'password_confirmation' => '',
            ])->assertSessionHasErrors(['password' => 'Ange ett nytt lösenord.']);

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

    public function test_auth_notification_emails_render_swedish_fallback_and_footer_with_english_app_locale(): void
    {
        $user = User::factory()->create(['name' => 'Anna Andersson']);
        $this->app->setLocale('en');
        config(['bagspar.contact_email' => 'kontakt@example.test']);

        $messages = [
            (new VerifyEmail)->toMail($user),
            (new ResetPassword('test-reset-token'))->toMail($user),
        ];

        foreach ($messages as $message) {
            $this->assertSame([['kontakt@example.test', 'Bågspår']], $message->replyTo);

            $html = (string) $message->render();
            $text = view($message->view[1], $message->viewData)->render();

            $this->assertStringContainsString('<html lang="sv">', $html);
            $this->assertStringContainsString('Om knappen inte fungerar kan du kopiera länken nedan', $html);
            $this->assertStringContainsString('Med vänliga hälsningar,', $html);
            $this->assertStringContainsString('Alla rättigheter förbehållna.', $html);
            $this->assertStringContainsString('Om knappen inte fungerar kan du kopiera länken nedan', $text);
            $this->assertStringContainsString($message->viewData['url'], $text);
            $this->assertStringContainsString('Alla rättigheter förbehållna.', $text);
            $this->assertStringNotContainsString('Regards,', $html);
            $this->assertStringNotContainsString('All rights reserved.', $html);
            $this->assertStringNotContainsString("If you're having trouble clicking", $html);
        }
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

        $this->get($verificationUrl)
            ->assertRedirect(route('app.dashboard'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_guest_signed_verification_marks_the_email_and_returns_to_the_link_after_login(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'owner@example.test',
            'password' => Hash::make('correct-secure-pass-8'),
        ]);
        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($verificationUrl)
            ->assertRedirect(route('login'))
            ->assertSessionHas('email_verified_notice', 'Din e-post är verifierad. Logga in med samma e-postadress och lösenord som när kontot skapades.')
            ->assertSessionHas('url.intended', $verificationUrl);

        $this->assertGuest();
        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->get($verificationUrl)
            ->assertRedirect(route('login'))
            ->assertSessionHas('email_verified_notice', 'Din e-post är verifierad. Logga in med samma e-postadress och lösenord som när kontot skapades.');

        $this->get(route('login'))
            ->assertSee('Din e-post är verifierad. Logga in med samma e-postadress och lösenord som när kontot skapades.', false)
            ->assertSee('auth-notice', false);

        $this->post(route('auth.login.store'), [
            'email' => $user->email,
            'password' => 'correct-secure-pass-8',
        ])->assertRedirect($verificationUrl);

        $this->get($verificationUrl)->assertRedirect(route('app.dashboard'));
        $this->get(route('app.dashboard'))->assertOk();
    }

    public function test_verification_link_for_another_account_shows_resend_recovery_for_the_current_user(): void
    {
        Notification::fake();
        $linkOwner = User::factory()->unverified()->create(['email' => 'link-owner@example.test']);
        $currentUser = User::factory()->unverified()->create(['email' => 'current-user@example.test']);
        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $linkOwner->getKey(),
            'hash' => sha1($linkOwner->getEmailForVerification()),
        ]);

        $this->actingAs($currentUser)->get($verificationUrl)
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('verification_issue', 'Den här länken kan inte verifiera kontot du är inloggad på. En ny verifieringslänk kan skickas till adressen nedan.');

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee($currentUser->email)
            ->assertSee('Den här länken kan inte verifiera kontot du är inloggad på.')
            ->assertSee('Logga ut och byt konto')
            ->assertDontSee($linkOwner->email);

        $this->post(route('verification.send'))->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($currentUser, VerifyEmail::class);
        Notification::assertNotSentTo($linkOwner, VerifyEmail::class);
        $this->assertNull($currentUser->fresh()->email_verified_at);
        $this->assertNull($linkOwner->fresh()->email_verified_at);
    }

    public function test_verification_link_for_another_account_offers_dashboard_when_current_user_is_verified(): void
    {
        Notification::fake();
        $linkOwner = User::factory()->unverified()->create(['email' => 'link-owner@example.test']);
        $currentUser = User::factory()->create(['email' => 'current-user@example.test']);
        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $linkOwner->getKey(),
            'hash' => sha1($linkOwner->getEmailForVerification()),
        ]);

        $this->actingAs($currentUser)->get($verificationUrl)
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('verification_issue', 'Den här länken kan inte verifiera kontot du är inloggad på. E-postadressen för det kontot är redan bekräftad.');

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee($currentUser->email)
            ->assertSee('Du är inloggad som')
            ->assertSee('Till min verkstad')
            ->assertDontSee('Skicka bekräftelselänk igen')
            ->assertDontSee('Följ länken i e-postmeddelandet')
            ->assertDontSee($linkOwner->email);

        $this->post(route('verification.send'))->assertRedirect(route('app.dashboard'));

        Notification::assertNotSentTo($currentUser, VerifyEmail::class);
        Notification::assertNotSentTo($linkOwner, VerifyEmail::class);
        $this->assertNull($linkOwner->fresh()->email_verified_at);
    }

    public function test_expired_and_tampered_verification_links_show_a_forbidden_page_without_verifying(): void
    {
        $user = User::factory()->unverified()->create();
        $routeParameters = [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ];
        $expiredUrl = URL::temporarySignedRoute('verification.verify', now()->subMinute(), $routeParameters);
        $validUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), $routeParameters);
        $tamperedUrl = $validUrl.'&tampered=1';

        foreach ([$expiredUrl, $tamperedUrl] as $verificationUrl) {
            $this->actingAsGuest()->get($verificationUrl)
                ->assertForbidden()
                ->assertSee('Länken är ogiltig eller har gått ut')
                ->assertSee(route('login'));

            $this->actingAs($user)->get($verificationUrl)
                ->assertForbidden()
                ->assertSee('Länken är ogiltig eller har gått ut')
                ->assertSee(route('verification.notice'));
        }

        $this->assertNull($user->fresh()->email_verified_at);
        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee('Skicka bekräftelselänk igen');
    }

    public function test_signed_verification_with_the_wrong_hash_returns_a_forbidden_page_without_verifying(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->getKey(),
            'hash' => sha1('other@example.test'),
        ]);

        $this->get($verificationUrl)
            ->assertForbidden()
            ->assertSee('Länken är ogiltig eller har gått ut');

        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_json_verification_failures_remain_forbidden(): void
    {
        $linkOwner = User::factory()->unverified()->create();
        $currentUser = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $linkOwner->getKey(),
            'hash' => sha1($linkOwner->getEmailForVerification()),
        ]);

        $this->actingAs($currentUser)->getJson($verificationUrl)->assertForbidden();
        $this->getJson($verificationUrl.'&tampered=1')->assertForbidden();
        $this->actingAsGuest()->getJson($verificationUrl.'&tampered=1')->assertForbidden();

        $this->assertNull($currentUser->fresh()->email_verified_at);
        $this->assertNull($linkOwner->fresh()->email_verified_at);
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
