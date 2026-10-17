<?php

namespace Tests\Feature\Workshop;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('app.account.edit'))->assertRedirect(route('login'));
    }

    public function test_page_renders_for_owner_with_role_and_workshop_name(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('app.account.edit'))
            ->assertOk()
            ->assertSee('Mitt konto', false)
            ->assertSee('Ägare', false)
            ->assertSee($owner->workshop->name, false)
            ->assertSee('Verkstadens uppgifter ändras av ägaren under Inställningar.', false)
            ->assertSee('href="'.route('app.account.edit').'"', false)
            ->assertSee('data-password-toggle="profile_current_password"', false);
    }

    public function test_page_renders_for_member_with_role_and_the_account_link_in_the_menu(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($member)
            ->get(route('app.account.edit'))
            ->assertOk()
            ->assertSee('Medarbetare', false)
            ->assertDontSee('Ägare', false)
            ->assertSee('>Konto</a>', false);
    }

    public function test_update_with_wrong_current_password_fails(): void
    {
        $user = User::factory()->create(['name' => 'Anna', 'email' => 'anna@example.test']);

        $this->actingAs($user)
            ->from(route('app.account.edit'))
            ->patch(route('app.account.update'), [
                'name' => 'Annika',
                'email' => 'annika@example.test',
                'current_password' => 'wrong-password',
            ])
            ->assertRedirect(route('app.account.edit'))
            ->assertSessionHasErrorsIn('profile', ['current_password' => 'Fel lösenord.']);

        $user->refresh();
        $this->assertSame('Anna', $user->name);
        $this->assertSame('anna@example.test', $user->email);
    }

    public function test_changing_email_lowercases_it_clears_verification_and_sends_a_verification_mail(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'anna@example.test']);

        $this->actingAs($user)
            ->patch(route('app.account.update'), [
                'name' => '  Annika Berg ',
                'email' => '  Annika@Example.TEST ',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('app.account.edit'))
            ->assertSessionHas('status', 'Uppgifterna är sparade. Vi har skickat ett mejl till den nya adressen, bekräfta den för att behålla åtkomsten.');

        $user->refresh();
        $this->assertSame('annika@example.test', $user->email);
        $this->assertSame('Annika Berg', $user->name);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unchanged_email_keeps_verification_and_sends_no_mail(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'anna@example.test']);
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user)
            ->patch(route('app.account.update'), [
                'name' => 'Anna Nyström',
                'email' => 'Anna@Example.test',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('app.account.edit'))
            ->assertSessionHas('status', 'Uppgifterna är sparade.');

        $user->refresh();
        $this->assertSame('Anna Nyström', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($verifiedAt->equalTo($user->email_verified_at));
        Notification::assertNothingSent();
    }

    public function test_email_taken_by_another_user_is_rejected_in_swedish(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);
        $user = User::factory()->create(['email' => 'anna@example.test']);

        $this->actingAs($user)
            ->from(route('app.account.edit'))
            ->patch(route('app.account.update'), [
                'name' => 'Anna',
                'email' => 'TAKEN@example.test',
                'current_password' => 'password',
            ])
            ->assertSessionHasErrorsIn('profile', ['email' => 'E-postadressen är redan registrerad.']);

        $this->assertSame('anna@example.test', $user->fresh()->email);
    }

    public function test_profile_validation_messages_stay_swedish_when_application_locale_is_english(): void
    {
        $this->app->setLocale('en');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('app.account.edit'))
            ->patch(route('app.account.update'), [
                'name' => '',
                'email' => 'not-an-email',
                'current_password' => '',
            ])
            ->assertSessionHasErrorsIn('profile', [
                'name' => 'Ange ditt namn.',
                'email' => 'Ange en giltig e-postadress.',
                'current_password' => 'Ange ditt nuvarande lösenord.',
            ]);
    }

    public function test_password_change_with_wrong_current_password_fails(): void
    {
        $user = User::factory()->create();
        $hash = $user->password;

        $this->actingAs($user)
            ->from(route('app.account.edit'))
            ->patch(route('app.account.password'), [
                'current_password' => 'wrong-password',
                'password' => 'a-brand-new-password-1',
                'password_confirmation' => 'a-brand-new-password-1',
            ])
            ->assertRedirect(route('app.account.edit'))
            ->assertSessionHasErrorsIn('password', ['current_password' => 'Fel lösenord.']);

        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_password_change_updates_the_hash_and_the_new_password_works_for_login(): void
    {
        $user = User::factory()->create(['email' => 'anna@example.test']);

        $this->actingAs($user)
            ->patch(route('app.account.password'), [
                'current_password' => 'password',
                'password' => 'a-brand-new-password-1',
                'password_confirmation' => 'a-brand-new-password-1',
            ])
            ->assertRedirect(route('app.account.edit'))
            ->assertSessionHas('status', 'Lösenordet är bytt.');

        $this->assertTrue(Hash::check('a-brand-new-password-1', $user->fresh()->password));
        $this->assertFalse(Hash::check('password', $user->fresh()->password));

        $this->post(route('auth.logout'));
        $this->assertGuest();

        $this->post(route('auth.login.store'), [
            'email' => 'anna@example.test',
            'password' => 'a-brand-new-password-1',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_rules_are_enforced_in_swedish_even_with_english_locale(): void
    {
        $this->app->setLocale('en');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('app.account.edit'))
            ->patch(route('app.account.password'), [
                'current_password' => 'password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrorsIn('password', ['password' => 'Lösenordet måste innehålla minst 12 tecken.']);

        $this->from(route('app.account.edit'))
            ->patch(route('app.account.password'), [
                'current_password' => 'password',
                'password' => 'a-brand-new-password-1',
                'password_confirmation' => 'different-password-1',
            ])
            ->assertSessionHasErrorsIn('password', ['password' => 'Lösenorden stämmer inte överens.']);

        $this->from(route('app.account.edit'))
            ->patch(route('app.account.password'), [
                'current_password' => 'password',
                'password' => str_repeat('a', 73),
                'password_confirmation' => str_repeat('a', 73),
            ])
            ->assertSessionHasErrorsIn('password', ['password' => 'Lösenordet får vara högst 72 byte.']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
