<?php

namespace Tests\Feature;

use App\Mail\PilotLeadNotification;
use App\Models\PilotLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class PilotLeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_interest_submission_is_saved_and_notifies_the_configured_recipient(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), $this->validSubmission());

        $response->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true);

        $lead = PilotLead::query()->where('email', 'verkstad@example.test')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Båghantverket', $lead->company);
        $this->assertNull($lead->message);
        $this->assertNull($lead->consented_at);

        $thankYou = $this->get(route('home'));
        $thankYou->assertOk();
        $thankYou->assertSeeInOrder([
            'Tack, verkstaden är anmäld.',
            route('demo'),
        ], false);

        Mail::assertSent(PilotLeadNotification::class, function (PilotLeadNotification $mail): bool {
            return $mail->hasTo('pilot-team@example.test')
                && $mail->lead->company === 'Båghantverket';
        });
    }

    public function test_missing_required_fields_return_swedish_errors_without_saving_or_sending_mail(): void
    {
        Mail::fake();

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), []);

        $response->assertRedirectBackWithErrors(['company', 'email'])
            ->assertInvalid([
                'company' => 'Fyll i företag eller verkstad.',
                'email' => 'Fyll i din e-postadress.',
            ]);

        $this->assertDatabaseCount('pilot_leads', 0);
        Mail::assertNothingSent();
    }

    public function test_invalid_email_and_overlong_company_are_rejected_without_saving(): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        $submission['email'] = 'not-an-email';
        $submission['company'] = str_repeat('a', 161);

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), $submission);

        $response->assertInvalid([
            'email' => 'Ange en giltig e-postadress.',
            'company' => 'Företagsnamnet får vara högst 160 tecken.',
        ]);

        $this->assertDatabaseCount('pilot_leads', 0);
        Mail::assertNothingSent();
    }

    public function test_filled_honeypot_is_rejected_without_saving_or_sending_mail(): void
    {
        Mail::fake();

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), [
                ...$this->validSubmission(),
                'website' => 'https://spam.example.test',
            ]);

        $response->assertInvalid(['website' => 'Formuläret kunde inte skickas.']);

        $this->assertDatabaseCount('pilot_leads', 0);
        Mail::assertNothingSent();
    }

    public function test_requests_from_one_ip_are_limited_to_five_per_minute(): void
    {
        Mail::fake();
        $serverVariables = ['REMOTE_ADDR' => '192.0.2.18'];

        for ($submissionNumber = 0; $submissionNumber < 5; $submissionNumber++) {
            $this->withServerVariables($serverVariables)
                ->post(route('pilot-leads.store'), $this->validSubmission())
                ->assertRedirect(route('home').'#form-result');
        }

        $this->withServerVariables($serverVariables)
            ->post(route('pilot-leads.store'), $this->validSubmission())
            ->assertTooManyRequests();

        $this->assertDatabaseCount('pilot_leads', 5);
        Mail::assertSent(PilotLeadNotification::class, 5);
    }

    public function test_mail_failure_does_not_undo_the_saved_lead_or_hide_confirmation(): void
    {
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);
        Mail::shouldReceive('to')
            ->once()
            ->with('pilot-team@example.test')
            ->andThrow(new RuntimeException('Mail transport is unavailable.'));

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), $this->validSubmission());

        $response->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true);

        $lead = PilotLead::query()->where('email', 'verkstad@example.test')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Båghantverket', $lead->company);
    }

    public function test_company_name_is_escaped_in_the_notification_mail(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);

        $this->post(route('pilot-leads.store'), [
            'company' => 'Båge <script>alert(1)</script>',
            'email' => 'verkstad@example.test',
        ]);

        Mail::assertSent(PilotLeadNotification::class, function (PilotLeadNotification $mail): bool {
            $html = $mail->render();

            return str_contains($html, 'Båge &lt;script&gt;alert(1)&lt;/script&gt;')
                && ! str_contains($html, '<script>alert(1)</script>');
        });
    }

    /**
     * @return array<string, string>
     */
    private function validSubmission(): array
    {
        return [
            'company' => 'Båghantverket',
            'email' => 'verkstad@example.test',
            'message' => 'Ska inte sparas',
        ];
    }
}
