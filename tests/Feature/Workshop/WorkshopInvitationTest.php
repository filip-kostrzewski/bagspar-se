<?php

namespace Tests\Feature\Workshop;

use App\Mail\WorkshopInvitationMail;
use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use App\Models\WorkshopInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkshopInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'postmark',
            'services.postmark.key' => 'server-token-test',
        ]);
    }

    public function test_owner_can_invite_a_member_by_email_and_the_invitation_can_be_accepted(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['name' => 'Anna Andersson']);
        $owner->workshop->update(['name' => 'Ekens Fönster <verkstad>']);

        $this->actingAs($owner)
            ->post(route('app.invitations.store'), ['email' => '  MEDE@EXAMPLE.TEST '])
            ->assertRedirect()
            ->assertSessionHas('status', 'Inbjudan har skickats.');

        $invitation = WorkshopInvitation::query()->firstOrFail();
        $this->assertSame('mede@example.test', $invitation->email);
        $this->assertNull($invitation->accepted_at);

        $url = null;
        Mail::assertSent(WorkshopInvitationMail::class, function (WorkshopInvitationMail $mail) use (&$url): bool {
            $url = $mail->url;
            $html = $mail->render();

            $this->assertStringContainsString('Ekens Fönster &lt;verkstad&gt;', $html);
            $this->assertStringNotContainsString('Ekens Fönster <verkstad>', $html);

            return $mail->hasTo('mede@example.test')
                && $mail->hasSubject('Inbjudan till Ekens Fönster <verkstad> i Bågspår');
        });
        $this->assertIsString($url);

        $this->post(route('auth.logout'));

        $this->get($url)
            ->assertOk()
            ->assertSee('Ekens Fönster <verkstad>')
            ->assertSee('mede@example.test', false);

        $this->post($url, [
            'name' => 'Maja Medarbetare',
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
            'terms' => '1',
        ])->assertRedirect(route('app.dashboard'));

        $member = User::query()->where('email', 'mede@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($member);
        $this->assertTrue($member->isMember());
        $this->assertSame($owner->workshop_id, $member->workshop_id);
        $this->assertNotNull($member->email_verified_at);
        $this->assertNotNull($member->terms_accepted_at);
        $this->assertNotNull($invitation->fresh()?->accepted_at);

        $this->get(route('app.settings.edit'))->assertForbidden();
        $this->patch(route('app.settings.update'), [
            'name' => 'Annat namn',
        ])->assertForbidden();
        $this->assertSame('Ekens Fönster <verkstad>', $owner->workshop->fresh()?->name);
    }

    public function test_accepting_an_invitation_requires_the_terms_and_a_long_password(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('app.invitations.store'), [
            'email' => 'mede@example.test',
        ])->assertRedirect();
        $url = $this->sentInvitationUrl();
        $this->post(route('auth.logout'));

        $this->from($url)->post($url, [
            'name' => 'Maja Medarbetare',
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
        ])->assertSessionHasErrors(['terms' => 'Godkänn villkoren för att acceptera inbjudan.']);

        $this->from($url)->post($url, [
            'name' => 'Maja Medarbetare',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => '1',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_a_member_cannot_invite_or_remove_access_and_cannot_delete_a_project(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $otherOwner = User::factory()->create();

        $this->assertTrue($owner->can('delete', $project));
        $this->assertFalse($member->can('delete', $project));
        $this->assertFalse($otherOwner->can('delete', $project));
        $this->assertTrue($member->can('update', $project));

        $this->actingAs($member)
            ->post(route('app.invitations.store'), ['email' => 'ny@example.test'])
            ->assertForbidden();

        $this->actingAs($member)
            ->delete(route('app.members.destroy', $owner))
            ->assertForbidden();

        $this->assertDatabaseCount('workshop_invitations', 0);
        $this->assertModelExists($owner);
    }

    public function test_owner_can_cancel_an_invitation_and_remove_a_member_without_erasing_history(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['name' => 'Anna Andersson']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)->post(route('app.invitations.store'), [
            'email' => 'mede@example.test',
        ])->assertRedirect();
        $url = $this->sentInvitationUrl();
        $this->post(route('auth.logout'));
        $this->post($url, [
            'name' => 'Maja Medarbetare',
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
            'terms' => '1',
        ])->assertRedirect(route('app.dashboard'));

        $member = User::query()->where('email', 'mede@example.test')->firstOrFail();
        $this->post(route('app.sashes.store', $project), [
            'code' => 'B-24',
            'room' => 'Kök',
            'notes' => 'Behåll originalglaset.',
        ])->assertRedirect();

        $sash = WindowSash::query()->where('code', 'B-24')->firstOrFail();
        $this->get(route('app.sashes.show', [$project, $sash]))
            ->assertOk()
            ->assertSee('Maja Medarbetare', false)
            ->assertSee('Mottagen', false);

        $this->post(route('auth.logout'));
        $this->actingAs($owner)->delete(route('app.members.destroy', $member))
            ->assertRedirect()
            ->assertSessionHas('status', 'Medarbetarens åtkomst har tagits bort.');

        $this->assertModelMissing($member);
        $this->assertDatabaseHas('sash_events', [
            'sash_id' => $sash->getKey(),
            'user_name' => 'Maja Medarbetare',
            'kind' => 'stage',
        ]);

        $this->actingAs($owner)->post(route('app.invitations.store'), [
            'email' => 'annan@example.test',
        ])->assertRedirect();
        $invitation = WorkshopInvitation::query()->where('email', 'annan@example.test')->firstOrFail();

        $this->actingAs($owner)->delete(route('app.invitations.destroy', $invitation))
            ->assertRedirect()
            ->assertSessionHas('status', 'Inbjudan har tagits bort.');
        $this->assertModelMissing($invitation);

        $this->actingAs(User::factory()->create())
            ->delete(route('app.invitations.destroy', $invitation->getKey()))
            ->assertNotFound();
    }

    public function test_expired_and_unknown_invitation_links_are_not_found(): void
    {
        $owner = User::factory()->create();
        $invitation = WorkshopInvitation::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'invited_by_user_id' => $owner->getKey(),
            'expires_at' => now()->subMinute(),
        ]);
        $token = WorkshopInvitation::freshToken();
        $invitation->update(['token_hash' => WorkshopInvitation::hashToken($token)]);

        $this->get(route('invitations.accept', ['token' => $token]))->assertNotFound();
        $this->get(route('invitations.accept', ['token' => WorkshopInvitation::freshToken()]))->assertNotFound();
    }

    public function test_an_existing_email_cannot_be_invited_and_unconfigured_mail_is_reported(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        User::factory()->create(['email' => 'finns@example.test']);

        $this->actingAs($owner)
            ->from(route('app.settings.edit'))
            ->post(route('app.invitations.store'), ['email' => 'finns@example.test'])
            ->assertRedirect(route('app.settings.edit'))
            ->assertSessionHasErrors(['email' => 'E-postadressen är redan registrerad.']);

        config(['mail.default' => 'log']);

        $this->post(route('app.invitations.store'), ['email' => 'ny@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Inbjudan sparades, men mejlet kunde inte skickas.');

        Mail::assertNothingSent();
        $this->assertDatabaseHas('workshop_invitations', ['email' => 'ny@example.test']);
    }

    public function test_an_open_invitation_shows_the_swedish_expiry(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-10-17 10:00:00', 'UTC'));
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('app.invitations.store'), ['email' => 'mede@example.test'])
            ->assertRedirect();

        $this->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('Gäller till 24 okt 2026 12:00', false)
            ->assertDontSee('Gäller till 2026-10-24', false)
            ->assertDontSee('kl. 12:00', false);
    }

    private function sentInvitationUrl(): string
    {
        $url = null;
        Mail::assertSent(WorkshopInvitationMail::class, function (WorkshopInvitationMail $mail) use (&$url): bool {
            $url = $mail->url;

            return true;
        });
        $this->assertIsString($url);

        return $url;
    }
}
