<?php

namespace Tests\Feature;

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageNotification;
use App\Models\ContactMessage;
use App\Models\SpamSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_and_homepage_show_the_form_without_a_consent_checkbox(): void
    {
        $page = $this->get(route('contact'));

        $page->assertOk();
        $page->assertSee('<title>Kontakt — Bågspår</title>', false);
        $page->assertSee('novalidate', false);
        $page->assertSee('Namn <span aria-hidden="true">*</span>', false);
        $page->assertSee('E-post <span aria-hidden="true">*</span>', false);
        $page->assertSee('Meddelande <span aria-hidden="true">*</span>', false);
        $page->assertSee('<label for="page-contact-company">Företag</label>', false);
        $page->assertSee('<label for="page-contact-phone">Telefon</label>', false);
        $page->assertSee('Vi använder uppgifterna bara för att svara på ditt meddelande, se <a href="/integritet">integritetspolicyn</a>.', false);
        $page->assertSee('mailto:'.config('bagspar.contact_email'), false);
        $page->assertSee((string) config('bagspar.contact_email'), false);
        $page->assertSee('href="'.route('contact').'"', false);
        $page->assertDontSee('type="checkbox"', false);
        $page->assertDontSee('captcha', false);

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('href="'.route('contact').'"', false);
        $home->assertSee('>Kontakt</a>', false);
        $home->assertSee('<label for="home-contact-company">Företag</label>', false);
        $home->assertSee('<label for="home-contact-phone">Telefon</label>', false);
        $home->assertSee('action="'.route('contact.store').'"', false);
        $home->assertSee('novalidate', false);
        $home->assertDontSee('type="checkbox"', false);
    }

    public function test_a_valid_message_is_stored_and_sent_to_the_contact_address(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        $this->configureDeliverableMail();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validSubmission());

        $response->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Anna Andersson',
            'company' => 'Båghantverket',
            'email' => 'anna@example.test',
            'phone' => '070-123 45 67',
            'message' => 'Vi vill veta hur märkningen fungerar.',
        ]);
        $this->assertDatabaseCount('spam_submissions', 0);

        Mail::assertSent(ContactMessageNotification::class, function (ContactMessageNotification $mail): bool {
            return $mail->hasTo('kontakt@bagspar.se')
                && $mail->hasReplyTo('anna@example.test', 'Anna Andersson')
                && str_contains($mail->render(), 'Vi vill veta hur märkningen fungerar.');
        });
        Mail::assertSent(ContactMessageConfirmation::class, function (ContactMessageConfirmation $mail): bool {
            return $mail->hasTo('anna@example.test')
                && $mail->hasReplyTo('kontakt@bagspar.se');
        });

        $this->get(route('contact'))
            ->assertSee('Tack! Vi har fått ditt meddelande och återkommer så snart vi kan.', false)
            ->assertDontSee('inom 24', false)
            ->assertDontSee('name="contact_message"', false);
    }

    public function test_homepage_submission_returns_to_the_homepage_form(): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $this->from(route('home'))
            ->post(route('contact.store'), [
                ...$this->validSubmission(),
                'source' => 'home',
            ])
            ->assertRedirect(route('home').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->get(route('home'))
            ->assertSee('Tack! Vi har fått ditt meddelande och återkommer så snart vi kan.', false);
    }

    public function test_company_and_phone_can_be_left_empty(): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $submission = $this->validSubmission();
        unset($submission['contact_company'], $submission['contact_phone']);

        $this->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt');

        $message = ContactMessage::query()->where('email', 'anna@example.test')->first();
        $this->assertNotNull($message);
        $this->assertNull($message->company);
        $this->assertNull($message->phone);
        Mail::assertSent(ContactMessageNotification::class);
    }

    public function test_missing_required_fields_return_swedish_errors_without_saving_or_sending_mail(): void
    {
        Mail::fake();

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), []);

        $response->assertInvalid([
            'contact_name' => 'Fyll i ditt namn.',
            'contact_email' => 'Fyll i din e-postadress.',
            'contact_message' => 'Skriv ett meddelande.',
        ], 'contact');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();

        $this->from(route('contact'))
            ->followingRedirects()
            ->post(route('contact.store'), ['contact_company' => 'Båghantverket'])
            ->assertSee('Fyll i ditt namn.', false)
            ->assertSee('Fyll i din e-postadress.', false)
            ->assertSee('Skriv ett meddelande.', false)
            ->assertSee('id="page-contact-name-error"', false)
            ->assertDontSee('Företagsnamnet får vara högst', false);
    }

    public function test_invalid_email_phone_and_overlong_message_are_rejected(): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        $submission['contact_email'] = 'inte-en-adress';
        $submission['contact_phone'] = 'ring kontoret';
        $submission['contact_message'] = str_repeat('a', 2001);

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertInvalid([
                'contact_email' => 'Ange en giltig e-postadress.',
                'contact_phone' => 'Ange ett giltigt telefonnummer.',
                'contact_message' => 'Meddelandet får vara högst 2000 tecken.',
            ], 'contact');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_filled_honeypot_shows_the_thank_you_without_saving_or_sending_mail(): void
    {
        Mail::fake();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
            ->withHeader('User-Agent', 'SpamBot/1.0')
            ->from(route('contact'))
            ->post(route('contact.store'), [
                ...$this->validSubmission(),
                'website' => 'https://spam.example.test',
            ])
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseCount('contact_messages', 0);
        $spam = SpamSubmission::query()->first();
        $this->assertNotNull($spam);
        $this->assertSame('honeypot', $spam->reason);
        $this->assertSame('203.0.113.50', $spam->ip);
        $this->assertSame('SpamBot/1.0', $spam->user_agent);
        $this->assertSame('Anna Andersson', $spam->payload['contact_name']);
        $this->assertSame('Båghantverket', $spam->payload['contact_company']);
        $this->assertSame('anna@example.test', $spam->payload['contact_email']);
        $this->assertSame('070-123 45 67', $spam->payload['contact_phone']);
        $this->assertSame('Vi vill veta hur märkningen fungerar.', $spam->payload['contact_message']);
        $this->assertSame('page', $spam->payload['source']);
        $this->assertSame('https://spam.example.test', $spam->payload['website']);
        $this->assertArrayNotHasKey('form_started_at', $spam->payload);
        Mail::assertNothingSent();

        $this->get(route('contact'))
            ->assertSee('Tack! Vi har fått ditt meddelande och återkommer så snart vi kan.', false)
            ->assertDontSee('name="contact_message"', false);
    }

    #[TestWith([''])]
    #[TestWith(['   '])]
    public function test_an_empty_honeypot_still_delivers_the_message(string $website): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $this->post(route('contact.store'), [
            ...$this->validSubmission(),
            'website' => $website,
        ])->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'anna@example.test']);
        $this->assertDatabaseCount('spam_submissions', 0);
        Mail::assertSent(ContactMessageNotification::class);
    }

    public function test_submission_faster_than_three_seconds_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        $this->freezeTime();

        $submission = $this->validSubmission();
        $submission['form_started_at'] = Crypt::encryptString((string) now()->subSeconds(2)->getTimestamp());

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('too_fast');
    }

    public function test_submission_after_three_seconds_is_delivered(): void
    {
        Mail::fake();
        $this->freezeTime();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $submission = $this->validSubmission();
        $submission['form_started_at'] = Crypt::encryptString((string) now()->subSeconds(3)->getTimestamp());

        $this->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'anna@example.test']);
        $this->assertDatabaseCount('spam_submissions', 0);
        Mail::assertSent(ContactMessageNotification::class);
    }

    public function test_submission_without_a_started_at_token_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        unset($submission['form_started_at']);

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('too_fast');
    }

    public function test_tampered_started_at_token_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        $submission['form_started_at'] = 'not-a-token';

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('too_fast');
    }

    public function test_started_at_token_that_is_not_a_timestamp_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        $submission['form_started_at'] = Crypt::encryptString('not-a-timestamp');

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('too_fast');
    }

    #[TestWith(['contact_name', 'xKjqPzvw'])]
    #[TestWith(['contact_name', 'bcdfghj'])]
    #[TestWith(['contact_message', 'abcdfghjka'])]
    #[TestWith(['contact_name', 'xKjqaPzvw'])]
    public function test_gibberish_name_or_message_shows_success_without_sending_mail(string $field, string $value): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        $submission[$field] = $value;

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('gibberish');
        $spam = SpamSubmission::query()->first();
        $this->assertNotNull($spam);
        $this->assertSame($value, $spam->payload[$field]);
    }

    public function test_a_swedish_name_and_message_are_delivered(): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);
        $submission = $this->validSubmission();
        $submission['contact_name'] = 'Christer Bergström';
        $submission['contact_message'] = 'Vi vill veta hur märkningen av bågarna fungerar i verkstaden.';

        $this->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Christer Bergström',
            'message' => 'Vi vill veta hur märkningen av bågarna fungerar i verkstaden.',
        ]);
        $this->assertDatabaseCount('spam_submissions', 0);
        Mail::assertSent(ContactMessageNotification::class);
    }

    #[TestWith(['bot@lettersmail.com', 'bot@lettersmail.com'])]
    #[TestWith(['bot@foo.lettersmail.com', 'bot@foo.lettersmail.com'])]
    #[TestWith(['bot@abclettersmail.com', 'bot@abclettersmail.com'])]
    #[TestWith(['Bot@LettersMail.COM', 'bot@lettersmail.com'])]
    public function test_lettersmail_sender_domains_show_success_without_sending_mail(string $email, string $storedEmail): void
    {
        Mail::fake();
        $submission = $this->validSubmission();
        $submission['contact_email'] = $email;

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('blocked_email_domain');
        $spam = SpamSubmission::query()->first();
        $this->assertNotNull($spam);
        $this->assertSame($storedEmail, $spam->payload['contact_email']);
    }

    #[TestWith(['bot@lettersmail.co'])]
    #[TestWith(['bot@lettersmail.com.example.test'])]
    public function test_similar_email_domains_are_delivered(string $email): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);
        $submission = $this->validSubmission();
        $submission['contact_email'] = $email;

        $this->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', ['email' => $email]);
        $this->assertDatabaseCount('spam_submissions', 0);
        Mail::assertSent(ContactMessageNotification::class);
    }

    public function test_contact_pages_skip_turnstile_when_keys_are_missing(): void
    {
        config([
            'services.turnstile.site_key' => null,
            'services.turnstile.secret' => null,
        ]);

        $contact = $this->get(route('contact'));
        $contact->assertOk();
        $contact->assertDontSee('cf-turnstile', false);
        $contact->assertDontSee('challenges.cloudflare.com', false);
        $contact->assertSee('name="form_started_at"', false);
        $contact->assertSee('name="website"', false);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertDontSee('cf-turnstile', false);
        $home->assertSee('name="form_started_at"', false);
    }

    #[TestWith(['site-key-test', null])]
    #[TestWith([null, 'secret-key-test'])]
    #[TestWith(['', ''])]
    #[TestWith(['   ', '   '])]
    public function test_turnstile_is_skipped_when_either_key_is_missing(?string $siteKey, ?string $secret): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        config([
            'bagspar.contact_email' => 'kontakt@bagspar.se',
            'services.turnstile.site_key' => $siteKey,
            'services.turnstile.secret' => $secret,
        ]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('cf-turnstile', false);

        $this->post(route('contact.store'), $this->validSubmission())
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'anna@example.test']);
        $this->assertDatabaseCount('spam_submissions', 0);
        Mail::assertSent(ContactMessageNotification::class);
    }

    public function test_contact_pages_render_turnstile_when_keys_are_configured(): void
    {
        config([
            'services.turnstile.site_key' => 'site-key-test',
            'services.turnstile.secret' => 'secret-key-test',
        ]);

        $contact = $this->get(route('contact'));
        $contact->assertOk();
        $contact->assertSee('class="cf-turnstile"', false);
        $contact->assertSee('data-sitekey="site-key-test"', false);
        $contact->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false);
        $contact->assertDontSee('secret-key-test', false);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('class="cf-turnstile"', false);
        $home->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false);
        $home->assertDontSee('secret-key-test', false);
    }

    public function test_missing_turnstile_token_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        $this->enableTurnstile();

        $this->from(route('contact'))
            ->post(route('contact.store'), $this->validSubmission())
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('turnstile');
    }

    public function test_rejected_turnstile_token_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);
        $this->enableTurnstile();

        $submission = $this->validSubmission();
        $submission['cf-turnstile-response'] = 'invalid-token';

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('turnstile');
        Http::assertSentCount(1);
    }

    public function test_unreachable_turnstile_is_stored_as_spam_without_sending_mail(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::failedConnection(),
        ]);
        $this->enableTurnstile();

        $submission = $this->validSubmission();
        $submission['cf-turnstile-response'] = 'turnstile-token';

        $this->from(route('contact'))
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertSilentSpam('turnstile');
    }

    public function test_verified_turnstile_submission_is_stored_and_sent(): void
    {
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);
        $this->configureDeliverableMail();
        $this->enableTurnstile();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $submission = $this->validSubmission();
        $submission['cf-turnstile-response'] = 'turnstile-token';

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('contact.store'), $submission)
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Anna Andersson',
            'email' => 'anna@example.test',
            'message' => 'Vi vill veta hur märkningen fungerar.',
        ]);
        $this->assertDatabaseCount('spam_submissions', 0);
        Mail::assertSent(ContactMessageNotification::class, function (ContactMessageNotification $mail): bool {
            return $mail->hasTo('kontakt@bagspar.se')
                && $mail->hasReplyTo('anna@example.test', 'Anna Andersson');
        });
        Mail::assertSent(ContactMessageConfirmation::class);
        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && ($data['secret'] ?? null) === 'secret-key-test'
                && ($data['response'] ?? null) === 'turnstile-token'
                && ($data['remoteip'] ?? null) === '203.0.113.10';
        });
    }

    public function test_requests_from_one_ip_are_limited_to_five_per_minute(): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);
        $serverVariables = ['REMOTE_ADDR' => '192.0.2.44'];

        for ($submissionNumber = 0; $submissionNumber < 5; $submissionNumber++) {
            $submission = $this->validSubmission();
            $submission['contact_email'] = "anna{$submissionNumber}@example.test";

            $this->withServerVariables($serverVariables)
                ->post(route('contact.store'), $submission)
                ->assertRedirect(route('contact').'#kontakt');
        }

        $extra = $this->validSubmission();
        $extra['contact_email'] = 'extra@example.test';

        $this->withServerVariables($serverVariables)
            ->post(route('contact.store'), $extra)
            ->assertTooManyRequests();

        $this->assertDatabaseCount('contact_messages', 5);
        Mail::assertSent(ContactMessageNotification::class, 5);
    }

    #[TestWith(['array', null])]
    #[TestWith(['log', null])]
    #[TestWith(['postmark', ''])]
    #[TestWith(['postmark', '   '])]
    public function test_confirmation_is_skipped_when_outbound_mail_is_not_configured(string $mailer, ?string $postmarkKey): void
    {
        Mail::fake();
        config([
            'mail.default' => $mailer,
            'services.postmark.key' => $postmarkKey,
            'bagspar.contact_email' => 'kontakt@bagspar.se',
        ]);

        $this->post(route('contact.store'), $this->validSubmission())
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'anna@example.test']);
        Mail::assertSent(ContactMessageNotification::class);
        Mail::assertNotSent(ContactMessageConfirmation::class);
    }

    public function test_mail_failure_still_stores_the_message_and_shows_the_thank_you(): void
    {
        $this->configureDeliverableMail();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);
        Mail::shouldReceive('to')
            ->twice()
            ->andThrow(new RuntimeException('Mail transport is unavailable.'));

        $this->from(route('contact'))
            ->post(route('contact.store'), $this->validSubmission())
            ->assertRedirect(route('contact').'#kontakt')
            ->assertSessionHas('contact_message_received', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'anna@example.test']);

        $this->get(route('contact'))
            ->assertSee('Tack! Vi har fått ditt meddelande och återkommer så snart vi kan.', false);
    }

    public function test_message_text_is_escaped_in_the_notification_mail(): void
    {
        Mail::fake();
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $submission = $this->validSubmission();
        $submission['contact_name'] = 'Anna <script>alert(1)</script>';
        $submission['contact_message'] = 'Hej <b>verkstad</b>';

        $this->post(route('contact.store'), $submission);

        Mail::assertSent(ContactMessageNotification::class, function (ContactMessageNotification $mail): bool {
            $html = $mail->render();

            return str_contains($html, 'Anna &lt;script&gt;alert(1)&lt;/script&gt;')
                && str_contains($html, 'Hej &lt;b&gt;verkstad&lt;/b&gt;')
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

    private function enableTurnstile(): void
    {
        config([
            'services.turnstile.site_key' => 'site-key-test',
            'services.turnstile.secret' => 'secret-key-test',
        ]);
    }

    private function assertSilentSpam(string $reason): void
    {
        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertDatabaseCount('spam_submissions', 1);
        $this->assertDatabaseHas('spam_submissions', [
            'reason' => $reason,
        ]);
        Mail::assertNothingSent();
    }

    /**
     * @return array<string, string>
     */
    private function validSubmission(): array
    {
        return [
            'contact_name' => 'Anna Andersson',
            'contact_company' => 'Båghantverket',
            'contact_email' => '  ANNA@EXAMPLE.TEST ',
            'contact_phone' => '070-123 45 67',
            'contact_message' => 'Vi vill veta hur märkningen fungerar.',
            'source' => 'page',
            'form_started_at' => Crypt::encryptString((string) now()->subSeconds(10)->getTimestamp()),
        ];
    }
}
