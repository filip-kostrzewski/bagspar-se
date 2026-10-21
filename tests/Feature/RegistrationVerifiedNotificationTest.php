<?php

namespace Tests\Feature;

use App\Mail\RegistrationVerifiedNotification;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

class RegistrationVerifiedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private const LEAD_ADDRESS = 'team@example.test';

    protected function setUp(): void
    {
        parent::setUp();

        config(['bagspar.lead_notification_email' => self::LEAD_ADDRESS]);
    }

    public function test_registering_sends_nothing_until_the_owner_confirms_the_email(): void
    {
        Mail::fake();
        Notification::fake();
        $this->travelTo(Carbon::parse('2026-10-21 14:05:00', 'Europe/Stockholm'));

        $this->post(route('auth.register.store'), [
            'name' => 'Anna Andersson',
            'company' => 'Fönsterverkstan',
            'email' => 'anna@example.test',
            'password' => 'correct-horse-5',
            'password_confirmation' => 'correct-horse-5',
            'terms' => '1',
        ])->assertRedirect();

        Mail::assertNothingSent();

        $owner = User::query()->where('email', 'anna@example.test')->firstOrFail();
        $verificationUrl = null;
        Notification::assertSentTo($owner, VerifyEmail::class, function (VerifyEmail $notification) use ($owner, &$verificationUrl): bool {
            $verificationUrl = $notification->toMail($owner)->viewData['url'];

            return true;
        });

        $this->travel(10)->minutes();
        $this->get($verificationUrl)->assertRedirect(route('app.dashboard'));

        Mail::assertSent(RegistrationVerifiedNotification::class, 1);
        Mail::assertSent(RegistrationVerifiedNotification::class, function (RegistrationVerifiedNotification $mail) use ($owner): bool {
            return $mail->hasTo(self::LEAD_ADDRESS)
                && $mail->owner->is($owner)
                && $mail->workshop->is($owner->workshop);
        });
        $this->assertNotNull($owner->workshop->fresh()->registration_notified_at);
    }

    public function test_the_mail_lists_the_workshop_owner_and_stockholm_registration_time(): void
    {
        config(['bagspar.admin_token' => 'admin-secret']);
        $workshop = Workshop::factory()->create(['name' => 'Fönsterverkstan']);
        $owner = User::factory()->create([
            'workshop_id' => $workshop->getKey(),
            'name' => 'Anna Andersson',
            'email' => 'anna@example.test',
            'created_at' => Carbon::parse('2026-10-21 12:05:00', 'UTC'),
        ]);

        $mail = new RegistrationVerifiedNotification($workshop, $owner);

        $mail->assertHasSubject('Nytt verkstadskonto: Fönsterverkstan');
        $mail->assertSeeInText('Verkstad: Fönsterverkstan');
        $mail->assertSeeInText('Namn: Anna Andersson');
        $mail->assertSeeInText('E-post: anna@example.test');
        $mail->assertSeeInText('Registrerad: 2026-10-21 14:05');
        $mail->assertSeeInText(route('admin.workshops'));
        $mail->assertSeeInHtml('Fönsterverkstan');
        $mail->assertSeeInHtml('mailto:anna@example.test', false);
        $mail->assertSeeInHtml(route('admin.workshops'), false);
    }

    public function test_the_mail_omits_the_admin_link_when_the_admin_page_is_disabled(): void
    {
        config(['bagspar.admin_token' => null]);
        $owner = User::factory()->create();

        $mail = new RegistrationVerifiedNotification($owner->workshop, $owner);

        $mail->assertDontSeeInText(route('admin.workshops'));
        $mail->assertDontSeeInHtml(route('admin.workshops'), false);
    }

    public function test_no_mail_is_sent_when_the_lead_address_is_empty(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => '']);
        $owner = User::factory()->unverified()->create();

        $this->actingAs($owner)->get($this->verificationUrlFor($owner))
            ->assertRedirect(route('app.dashboard'));

        $this->assertNotNull($owner->fresh()->email_verified_at);
        Mail::assertNothingSent();
        $this->assertNull($owner->workshop->fresh()->registration_notified_at);
    }

    public function test_verifying_again_after_an_email_change_sends_no_second_mail(): void
    {
        Mail::fake();
        $owner = User::factory()->unverified()->create();

        $this->actingAs($owner)->get($this->verificationUrlFor($owner));

        $owner->refresh()->forceFill(['email' => 'new-address@example.test', 'email_verified_at' => null])->save();
        $this->actingAs($owner)->get($this->verificationUrlFor($owner))
            ->assertRedirect(route('app.dashboard'));

        $this->assertNotNull($owner->fresh()->email_verified_at);
        Mail::assertSent(RegistrationVerifiedNotification::class, 1);
    }

    public function test_a_member_confirming_the_email_sends_no_mail(): void
    {
        Mail::fake();
        $member = User::factory()->member()->unverified()->create();

        $this->actingAs($member)->get($this->verificationUrlFor($member))
            ->assertRedirect(route('app.dashboard'));

        $this->assertNotNull($member->fresh()->email_verified_at);
        Mail::assertNothingSent();
    }

    public function test_a_mail_failure_is_logged_and_does_not_block_verification(): void
    {
        Log::spy();
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new RuntimeException('Mail transport is unavailable.'));
        $owner = User::factory()->unverified()->create();

        $this->actingAs($owner)->get($this->verificationUrlFor($owner))
            ->assertRedirect(route('app.dashboard'))
            ->assertSessionHas('status', 'E-postadressen är verifierad.');

        $this->assertNotNull($owner->fresh()->email_verified_at);
        Log::shouldHaveReceived('warning')
            ->with('Registration notification could not be sent.', [
                'user_id' => $owner->getKey(),
                'exception' => RuntimeException::class,
            ])
            ->once();
    }

    private function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);
    }
}
