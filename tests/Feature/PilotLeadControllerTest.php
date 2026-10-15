<?php

namespace Tests\Feature;

use App\Mail\PilotLeadConfirmation;
use App\Mail\PilotLeadNotification;
use App\Models\PilotLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class PilotLeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_interest_submission_is_saved_and_notifies_the_configured_recipient(): void
    {
        Mail::fake();
        $this->configureDeliverableMail();
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), [
                ...$this->validSubmission(),
                'email' => 'Verkstad@Example.Test',
            ]);

        $response->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true)
            ->assertSessionHas('pilot_lead_confirmation_sent', true);

        $lead = PilotLead::query()->where('email', 'verkstad@example.test')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Båghantverket', $lead->company);
        $this->assertSame('verkstad@example.test', $lead->email);
        $this->assertNull($lead->message);
        $this->assertNotNull($lead->consented_at);

        $thankYou = $this->get(route('home'));
        $thankYou->assertOk();
        $thankYou->assertSeeInOrder([
            'Pilotplatsen är reserverad.',
            'Du får en bekräftelse via e-post.',
            'inom några vardagar',
            route('demo'),
        ], false);
        $thankYou->assertSee('class="form-result"', false);
        $thankYou->assertDontSee('form-result--info', false);

        Mail::assertSent(PilotLeadNotification::class, function (PilotLeadNotification $mail): bool {
            return $mail->hasTo('pilot-team@example.test')
                && $mail->lead->company === 'Båghantverket';
        });
        Mail::assertSent(PilotLeadConfirmation::class, function (PilotLeadConfirmation $mail): bool {
            $mail->assertSeeInHtml('inom några vardagar', false);
            $mail->assertSeeInHtml('eget konto ingår i piloten', false);
            $mail->assertSeeInText('inom några vardagar');
            $mail->assertDontSeeInHtml('prototyp');

            return $mail->hasTo('verkstad@example.test')
                && $mail->hasReplyTo((string) config('bagspar.contact_email'))
                && $mail->envelope()->subject === 'Vi har tagit emot anmälan till Bågspår';
        });
    }

    public function test_the_same_email_does_not_create_another_lead(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);
        PilotLead::factory()->create([
            'company' => 'Båghantverket',
            'email' => 'verkstad@example.test',
            'message' => null,
        ]);

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), [
                'company' => 'Annat namn',
                'email' => 'VERKSTAD@example.test',
            ]);

        $response->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_duplicate', true);

        $this->assertDatabaseCount('pilot_leads', 1);
        $this->assertDatabaseHas('pilot_leads', [
            'company' => 'Båghantverket',
            'email' => 'verkstad@example.test',
        ]);

        $page = $this->get(route('home'));
        $page->assertSee('Den här e-postadressen är redan anmäld.', false);
        $page->assertSee('class="form-result form-result--info"', false);
        $page->assertSee('ingen ny sparades', false);
        Mail::assertNothingSent();
    }

    public function test_every_signup_is_saved_when_no_notification_address_is_configured(): void
    {
        Mail::fake();
        $this->configureDeliverableMail();
        config(['bagspar.lead_notification_email' => null]);

        $this->from(route('home'))
            ->post(route('pilot-leads.store'), [
                'company' => 'Snickeriet Lind',
                'email' => 'lind@example.test',
            ])
            ->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true);

        $this->from(route('home'))
            ->post(route('pilot-leads.store'), [
                'company' => 'Båghantverket',
                'email' => 'verkstad@example.test',
            ])
            ->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true);

        $this->assertDatabaseCount('pilot_leads', 2);
        $this->assertDatabaseHas('pilot_leads', [
            'company' => 'Snickeriet Lind',
            'email' => 'lind@example.test',
            'message' => null,
        ]);
        $this->assertDatabaseHas('pilot_leads', [
            'company' => 'Båghantverket',
            'email' => 'verkstad@example.test',
            'message' => null,
        ]);
        $this->assertNotNull(PilotLead::query()->where('email', 'lind@example.test')->first()?->consented_at);
        $this->assertNotNull(PilotLead::query()->where('email', 'verkstad@example.test')->first()?->consented_at);
        Mail::assertSent(PilotLeadConfirmation::class, 2);
        Mail::assertNotSent(PilotLeadNotification::class);
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
        $this->configureDeliverableMail();
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);
        $serverVariables = ['REMOTE_ADDR' => '192.0.2.18'];

        for ($submissionNumber = 0; $submissionNumber < 5; $submissionNumber++) {
            $this->withServerVariables($serverVariables)
                ->post(route('pilot-leads.store'), [
                    'company' => 'Båghantverket',
                    'email' => "verkstad{$submissionNumber}@example.test",
                ])
                ->assertRedirect(route('home').'#form-result');
        }

        $this->withServerVariables($serverVariables)
            ->post(route('pilot-leads.store'), [
                'company' => 'Båghantverket',
                'email' => 'verkstad-extra@example.test',
            ])
            ->assertTooManyRequests();

        $this->assertDatabaseCount('pilot_leads', 5);
        Mail::assertSent(PilotLeadNotification::class, 5);
        Mail::assertSent(PilotLeadConfirmation::class, 5);
    }

    public function test_mail_failure_does_not_undo_the_saved_lead_or_hide_confirmation(): void
    {
        $this->configureDeliverableMail();
        config(['bagspar.lead_notification_email' => 'pilot-team@example.test']);
        Mail::shouldReceive('to')
            ->twice()
            ->andThrow(new RuntimeException('Mail transport is unavailable.'));

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), $this->validSubmission());

        $response->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true)
            ->assertSessionHas('pilot_lead_confirmation_sent', false);

        $lead = PilotLead::query()->where('email', 'verkstad@example.test')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Båghantverket', $lead->company);

        $page = $this->get(route('home'));
        $page->assertSee('Pilotplatsen är reserverad.', false);
        $page->assertSee('Vi har sparat verkstadens namn och din e-post. Vi hör av oss inom några vardagar.', false);
        $page->assertDontSee('Vi har sparat verkstadens namn och din e-post. Du får en bekräftelse via e-post.', false);
    }

    #[TestWith(['array', null])]
    #[TestWith(['log', null])]
    #[TestWith(['postmark', ''])]
    #[TestWith(['postmark', '   '])]
    public function test_signup_saves_the_lead_without_sending_a_confirmation_when_mail_is_not_configured(string $mailer, ?string $postmarkKey): void
    {
        Mail::fake();
        config([
            'mail.default' => $mailer,
            'services.postmark.key' => $postmarkKey,
            'bagspar.lead_notification_email' => 'pilot-team@example.test',
        ]);

        $response = $this->from(route('home'))
            ->post(route('pilot-leads.store'), $this->validSubmission());

        $response->assertRedirect(route('home').'#form-result')
            ->assertSessionHas('pilot_lead_received', true)
            ->assertSessionHas('pilot_lead_confirmation_sent', false);

        $this->assertNotNull(PilotLead::query()->where('email', 'verkstad@example.test')->first());
        Mail::assertSent(PilotLeadNotification::class);
        Mail::assertNotSent(PilotLeadConfirmation::class);

        $page = $this->get(route('home'));
        $page->assertSee('inom några vardagar', false);
        $page->assertDontSee('bekräftelse', false);
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

    private function configureDeliverableMail(): void
    {
        config([
            'mail.default' => 'postmark',
            'services.postmark.key' => 'server-token-test',
        ]);
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
