<?php

namespace Tests\Feature;

use App\Mail\QuoteAcceptedMail;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuotePublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_draft_or_unknown_token_is_not_found(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id]);
        $draft = Quote::factory()->create([
            'project_id' => $project->getKey(),
            'token' => str_repeat('a', 48),
        ]);

        $this->get(route('quote.show', ['token' => $draft->token]))->assertNotFound();
        $this->post(route('quote.accept', ['token' => $draft->token]), ['name' => 'Ada'])->assertNotFound();
        $this->get(route('quote.show', ['token' => str_repeat('b', 48)]))->assertNotFound();
        $this->get('/offert/kort')->assertNotFound();
        $this->assertNull($draft->fresh()->accepted_at);
    }

    public function test_a_sent_quote_shows_lines_totals_the_rot_note_and_the_accept_form(): void
    {
        $owner = User::factory()->create();
        $owner->workshop->update(['name' => 'Ekens Fönster', 'contact_email' => 'verkstad@example.test']);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'customer_name' => 'BRF Eken',
        ]);
        $quote = Quote::factory()->sent()->create([
            'project_id' => $project->getKey(),
            'introduction' => 'Tack för förfrågan <b>fet</b>',
            'valid_until' => '2026-12-31',
        ]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'description' => 'Renovering av båge K1', 'quantity' => 2, 'unit_price_ore' => 450000, 'sort_order' => 0]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'description' => 'Montering', 'quantity' => 1, 'unit_price_ore' => 120000, 'sort_order' => 1]);

        $this->get(route('quote.show', ['token' => $quote->token]))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('OFFERT', false)
            ->assertSee('Villa Ek', false)
            ->assertSee('Ekens Fönster', false)
            ->assertSee('BRF Eken', false)
            ->assertSee('Renovering av båge K1', false)
            ->assertSee('Montering', false)
            ->assertSee('9 000 kr', false)
            ->assertSee('10 200 kr', false)
            ->assertSee('2 550 kr', false)
            ->assertSee('12 750 kr', false)
            ->assertSee('Offerten gäller till 31 dec 2026', false)
            ->assertSee('Priset gäller före eventuellt ROT-avdrag, som görs på fakturan enligt Skatteverkets regler.', false)
            ->assertSee('Tack för förfrågan &lt;b&gt;fet&lt;/b&gt;', false)
            ->assertDontSee('<b>fet</b>', false)
            ->assertSee('novalidate', false)
            ->assertSee('name="name"', false)
            ->assertSee('Godkänn offert', false);
    }

    public function test_accepting_stores_the_name_and_time_mails_the_owner_and_is_idempotent(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-10-16 10:15:00', 'UTC'));

        $owner = User::factory()->create(['email' => 'agare@example.test']);
        User::factory()->member()->create(['workshop_id' => $owner->workshop_id, 'email' => 'medarbetare@example.test']);
        $project = Project::factory()->create(['workshop_id' => $owner->workshop_id, 'name' => 'Villa Ek']);
        $quote = Quote::factory()->sent()->create(['project_id' => $project->getKey()]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey()]);
        $page = route('quote.show', ['token' => $quote->token]);
        $accept = route('quote.accept', ['token' => $quote->token]);

        $this->from($page)->post($accept, [])
            ->assertRedirect($page)
            ->assertSessionHasErrors(['name' => 'Ange ditt namn.']);
        $this->from($page)->post($accept, ['name' => str_repeat('Å', 151)])
            ->assertSessionHasErrors(['name' => 'Namnet får vara högst 150 tecken.']);
        $this->assertNull($quote->fresh()->accepted_at);
        Mail::assertNothingSent();

        $this->from($page)->post($accept, ['name' => '  <script>alert(1)</script>  '])
            ->assertRedirect($page)
            ->assertSessionHas('status', 'Offerten är godkänd.');

        $quote->refresh();
        $this->assertSame(Quote::STATUS_ACCEPTED, $quote->status);
        $this->assertSame('<script>alert(1)</script>', $quote->accepted_name);
        $this->assertTrue($quote->accepted_at?->equalTo(Carbon::parse('2026-10-16 10:15:00', 'UTC')));

        Mail::assertSent(QuoteAcceptedMail::class, 1);
        Mail::assertSent(QuoteAcceptedMail::class, function (QuoteAcceptedMail $mail) use ($project): bool {
            $html = $mail->render();

            return $mail->hasTo('agare@example.test')
                && ! $mail->hasTo('medarbetare@example.test')
                && $mail->envelope()->subject === 'Offerten är godkänd: Villa Ek'
                && str_contains($html, route('app.projects.show', $project))
                && ! str_contains($html, '<script>alert(1)</script>');
        });

        $this->assertDatabaseHas('project_events', [
            'project_id' => $project->getKey(),
            'kind' => 'quote-accepted',
            'user_id' => null,
            'user_name' => '<script>alert(1)</script>',
        ]);

        $this->get($page)
            ->assertOk()
            ->assertSee('Offerten godkändes 16 okt 2026 12:15 av', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('name="name"', false);

        $this->from($page)->post($accept, ['name' => 'Någon annan'])
            ->assertRedirect($page)
            ->assertSessionHas('status', 'Offerten är godkänd.');

        $this->assertSame('<script>alert(1)</script>', $quote->fresh()->accepted_name);
        Mail::assertSent(QuoteAcceptedMail::class, 1);
        $this->assertSame(1, $project->events()->where('kind', 'quote-accepted')->count());

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Godkänd', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
