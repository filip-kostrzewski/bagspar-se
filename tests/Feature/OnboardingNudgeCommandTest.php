<?php

namespace Tests\Feature;

use App\Mail\OnboardingNudge;
use App\Models\Project;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OnboardingNudgeCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->travelTo(Carbon::parse('2026-10-17 07:00:00', 'UTC'));
    }

    public function test_owner_is_nudged_but_a_member_is_not_when_the_workshop_is_six_days_old_without_projects(): void
    {
        $workshop = Workshop::factory()->create(['created_at' => now()->subDays(6)]);
        $owner = User::factory()->for($workshop)->create();
        $member = User::factory()->member()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertSent(OnboardingNudge::class, 1);
        Mail::assertSent(OnboardingNudge::class, fn (OnboardingNudge $mail): bool => $mail->hasTo($owner->email)
            && $mail->hasSubject('Kom igång med Bågspår: lägg in det första projektet'));
        Mail::assertNotSent(OnboardingNudge::class, fn (OnboardingNudge $mail): bool => $mail->hasTo($member->email));
        $this->assertNotNull($workshop->fresh()->onboarding_nudge_sent_at);
    }

    public function test_nothing_is_sent_when_the_workshop_already_has_a_project(): void
    {
        $workshop = Workshop::factory()->create(['created_at' => now()->subDays(6)]);
        User::factory()->for($workshop)->create();
        Project::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($workshop->fresh()->onboarding_nudge_sent_at);
    }

    public function test_nothing_is_sent_when_the_workshop_is_two_days_old(): void
    {
        $workshop = Workshop::factory()->create(['created_at' => now()->subDays(2)]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($workshop->fresh()->onboarding_nudge_sent_at);
    }

    public function test_nothing_is_sent_when_the_workshop_is_locked(): void
    {
        $workshop = Workshop::factory()->create([
            'created_at' => now()->subDays(40),
            'trial_ends_at' => now()->subDay(),
        ]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($workshop->fresh()->onboarding_nudge_sent_at);
    }

    public function test_nothing_is_sent_when_the_workshop_has_paid(): void
    {
        $workshop = Workshop::factory()->create([
            'created_at' => now()->subDays(40),
            'trial_ends_at' => now()->subDays(10),
            'paid_until' => now()->addMonth(),
        ]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_the_nudge_is_only_sent_once(): void
    {
        $workshop = Workshop::factory()->create(['created_at' => now()->subDays(6)]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();
        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertSent(OnboardingNudge::class, 1);
    }

    public function test_the_mail_contains_workshop_name_action_and_project_url(): void
    {
        $workshop = Workshop::factory()->create([
            'name' => 'Ekens Fönster',
            'created_at' => now()->subDays(6),
            'trial_ends_at' => now()->addDays(24),
        ]);
        User::factory()->for($workshop)->create();

        $this->artisan('bagspar:send-onboarding-nudges')->assertSuccessful();

        Mail::assertSent(OnboardingNudge::class, function (OnboardingNudge $mail): bool {
            $html = $mail->render();

            $this->assertStringContainsString('Ekens Fönster', $html);
            $this->assertStringContainsString('Första projektet tar fem minuter', $html);
            $this->assertStringContainsString('Skapa första projektet', $html);
            $this->assertStringContainsString(route('app.projects.create'), $html);
            $this->assertStringContainsString(route('guides.index'), $html);

            return true;
        });
    }
}
