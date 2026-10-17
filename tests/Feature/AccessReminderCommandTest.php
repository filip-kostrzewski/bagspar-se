<?php

namespace Tests\Feature;

use App\Mail\AccessEndingReminder;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccessReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->travelTo(Carbon::parse('2026-10-17 07:00:00', 'UTC'));
    }

    public function test_owner_is_reminded_but_a_member_is_not_when_the_trial_ends_in_two_days(): void
    {
        $workshop = Workshop::factory()->create(['trial_ends_at' => now()->addDays(2)]);
        $owner = User::factory()->for($workshop)->create();
        $member = User::factory()->member()->for($workshop)->create();

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertSent(AccessEndingReminder::class, 1);
        Mail::assertSent(AccessEndingReminder::class, fn (AccessEndingReminder $mail): bool => $mail->hasTo($owner->email)
            && $mail->hasSubject('Provperioden för '.$workshop->name.' tar slut 19 okt 2026'));
        Mail::assertNotSent(AccessEndingReminder::class, fn (AccessEndingReminder $mail): bool => $mail->hasTo($member->email));
        $this->assertNotNull($workshop->fresh()->access_reminder_sent_at);
    }

    public function test_trial_mail_contains_workshop_name_date_action_and_billing_url(): void
    {
        $workshop = Workshop::factory()->create(['name' => 'Ekens Fönster', 'trial_ends_at' => now()->addDays(2)]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertSent(AccessEndingReminder::class, function (AccessEndingReminder $mail): bool {
            $html = $mail->render();

            $this->assertStringContainsString('Ekens Fönster', $html);
            $this->assertStringContainsString('19 okt 2026', $html);
            $this->assertStringContainsString('Provperioden tar snart slut', $html);
            $this->assertStringContainsString('Beställ abonnemang', $html);
            $this->assertStringContainsString('590 kr/mån exkl. moms', $html);
            $this->assertStringContainsString(route('app.billing.edit'), $html);

            return true;
        });
    }

    public function test_nothing_is_sent_when_the_trial_ends_in_ten_days(): void
    {
        $workshop = Workshop::factory()->create(['trial_ends_at' => now()->addDays(10)]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($workshop->fresh()->access_reminder_sent_at);
    }

    public function test_nothing_is_sent_when_the_workshop_is_already_locked(): void
    {
        $workshop = Workshop::factory()->create(['trial_ends_at' => now()->subDay()]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($workshop->fresh()->access_reminder_sent_at);
    }

    public function test_nothing_is_sent_for_an_ordered_workshop_whose_grace_period_ends_in_two_days(): void
    {
        $workshop = Workshop::factory()->create([
            'trial_ends_at' => now()->subDays(20),
            'subscription_plan' => Workshop::PLAN_MONTHLY,
            'subscription_ordered_at' => now()->subDays(28),
            'billing_email' => 'ekonomi@example.test',
        ]);
        User::factory()->for($workshop)->create();

        $this->assertSame(Workshop::ACCESS_ORDERED, $workshop->accessState());

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_the_reminder_is_only_sent_once(): void
    {
        $workshop = Workshop::factory()->create(['trial_ends_at' => now()->addDays(2)]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();
        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertSent(AccessEndingReminder::class, 1);
    }

    public function test_an_active_workshop_gets_the_paid_period_reminder(): void
    {
        $workshop = Workshop::factory()->create([
            'trial_ends_at' => now()->subDays(40),
            'paid_until' => now()->addDay(),
            'subscription_plan' => Workshop::PLAN_MONTHLY,
            'subscription_ordered_at' => now()->subDays(35),
            'billing_email' => 'ekonomi@example.test',
        ]);
        $owner = User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-access-reminders')->assertSuccessful();

        Mail::assertSent(AccessEndingReminder::class, function (AccessEndingReminder $mail) use ($owner, $workshop): bool {
            $this->assertTrue($mail->hasTo($owner->email));
            $this->assertTrue($mail->hasSubject('Betalperioden för '.$workshop->name.' tar slut 18 okt 2026'));

            $html = $mail->render();
            $this->assertStringContainsString('ekonomi@example.test', $html);
            $this->assertStringContainsString('Visa abonnemang', $html);

            return true;
        });
    }
}
