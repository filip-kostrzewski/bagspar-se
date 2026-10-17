<?php

namespace Tests\Feature\Workshop;

use App\Mail\QuoteSentMail;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_edit_page_creates_a_draft_with_three_empty_rows(): void
    {
        [$owner, $project] = $this->projectWithCustomer();

        $this->assertNull($project->quote);

        $response = $this->actingAs($owner)
            ->get(route('app.projects.quote.edit', $project))
            ->assertOk()
            ->assertSee('Offert', false)
            ->assertSee('Spara offert', false)
            ->assertSee('Fyll i en rad per båge', false)
            ->assertSee('Skicka till kunden', false)
            ->assertSee('Ladda ner PDF', false)
            ->assertSee('Priset gäller före eventuellt ROT-avdrag, som görs på fakturan enligt Skatteverkets regler.', false);

        $this->assertSame(3, substr_count($response->getContent(), '][description]"'));

        $quote = $project->fresh()->quote;
        $this->assertNotNull($quote);
        $this->assertSame(Quote::STATUS_DRAFT, $quote->status);
        $this->assertSame($owner->workshop_id, $quote->workshop_id);

        $this->get(route('app.projects.quote.edit', $project))->assertOk();
        $this->assertSame(1, Quote::query()->count());
    }

    public function test_saving_stores_lines_and_ignores_empty_rows(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        $edit = route('app.projects.quote.edit', $project);

        $this->actingAs($owner)
            ->from($edit)
            ->patch(route('app.projects.quote.update', $project), [
                'introduction' => 'Tack för förfrågan.',
                'valid_until' => '2026-12-31',
                'lines' => [
                    ['description' => 'Renovering av båge K1', 'quantity' => '2', 'unit_price' => '4 500'],
                    ['description' => '', 'quantity' => '', 'unit_price' => ''],
                    ['description' => 'Montering', 'quantity' => '', 'unit_price' => '1200,50'],
                    ['description' => '', 'quantity' => '3', 'unit_price' => ''],
                ],
            ])
            ->assertRedirect($edit)
            ->assertSessionHas('status', 'Offerten har sparats.')
            ->assertSessionHasNoErrors();

        $quote = $project->fresh()->quote;
        $this->assertSame('Tack för förfrågan.', $quote->introduction);
        $this->assertSame('2026-12-31', $quote->valid_until->toDateString());
        $lines = $quote->lines;
        $this->assertCount(2, $lines);
        $this->assertSame('Renovering av båge K1', $lines[0]->description);
        $this->assertSame(2, $lines[0]->quantity);
        $this->assertSame(450000, $lines[0]->unit_price_ore);
        $this->assertSame(1, $lines[1]->quantity);
        $this->assertSame(120050, $lines[1]->unit_price_ore);

        $this->get($edit)
            ->assertOk()
            ->assertSee('value="Renovering av båge K1"', false)
            ->assertSee('value="4 500"', false)
            ->assertSee('value="1 200,50"', false)
            ->assertSee('Tack för förfrågan.', false);

        $this->patch(route('app.projects.quote.update', $project), ['lines' => []])->assertSessionHasNoErrors();
        $this->assertCount(0, $project->fresh()->quote->lines);
        $this->assertNull($project->fresh()->quote->introduction);
    }

    public function test_a_row_without_a_price_fails_with_a_swedish_error(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        $edit = route('app.projects.quote.edit', $project);

        $this->actingAs($owner)
            ->from($edit)
            ->patch(route('app.projects.quote.update', $project), [
                'lines' => [
                    ['description' => 'Fel rad', 'quantity' => '1', 'unit_price' => ''],
                ],
            ])
            ->assertRedirect($edit)
            ->assertSessionHasErrors(['lines.0.unit_price' => 'Rad 1: ange priset i kronor.']);

        $this->from($edit)
            ->patch(route('app.projects.quote.update', $project), [
                'lines' => [
                    ['description' => 'Okej', 'quantity' => '1', 'unit_price' => '100'],
                    ['description' => 'Fel pris', 'quantity' => '1', 'unit_price' => 'abc'],
                    ['description' => '', 'quantity' => '1', 'unit_price' => '500'],
                    ['description' => 'Noll', 'quantity' => '0', 'unit_price' => '500'],
                ],
            ])
            ->assertRedirect($edit);

        $this->get($edit)
            ->assertOk()
            ->assertSee('Rad 2: ange priset i kronor, till exempel 4500 eller 4500,50.', false)
            ->assertSee('Rad 3: ange en beskrivning.', false)
            ->assertSee('Rad 4: antalet måste vara minst 1.', false)
            ->assertSee('value="abc"', false);
        $this->assertCount(0, Quote::query()->firstOrFail()->lines);
    }

    public function test_fill_adds_one_line_per_sash_without_duplicating(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'K1', 'room' => 'Kök']);
        WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'V2', 'room' => null]);
        $edit = route('app.projects.quote.edit', $project);

        $this->actingAs($owner)
            ->from($edit)
            ->post(route('app.projects.quote.fill', $project))
            ->assertRedirect($edit)
            ->assertSessionHas('status', '2 rader lades till. Fyll i priserna och spara.');

        $lines = $project->fresh()->quote->lines;
        $this->assertSame(
            ['Renovering av båge K1 · Kök', 'Renovering av båge V2'],
            $lines->pluck('description')->all(),
        );
        $this->assertSame([1, 1], $lines->pluck('quantity')->all());
        $this->assertSame([0, 0], $lines->pluck('unit_price_ore')->all());

        WindowSash::factory()->create(['project_id' => $project->getKey(), 'code' => 'W3', 'room' => null]);
        $this->post(route('app.projects.quote.fill', $project))
            ->assertSessionHas('status', 'En rad lades till. Fyll i priset och spara.');
        $this->assertCount(3, $project->fresh()->quote->lines);

        $this->post(route('app.projects.quote.fill', $project))
            ->assertSessionHas('status', 'Alla bågar har redan en rad.');
        $this->assertCount(3, $project->fresh()->quote->lines);
    }

    public function test_totals_are_computed_exclusive_vat_and_inclusive(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        $quote = Quote::factory()->create(['project_id' => $project->getKey()]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'quantity' => 2, 'unit_price_ore' => 450000, 'sort_order' => 0]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'quantity' => 1, 'unit_price_ore' => 120000, 'sort_order' => 1]);

        $quote = $quote->fresh('lines');
        $this->assertSame(1020000, $quote->subtotalOre());
        $this->assertSame(255000, $quote->vatOre());
        $this->assertSame(1275000, $quote->totalOre());

        $this->actingAs($owner)
            ->get(route('app.projects.quote.edit', $project))
            ->assertOk()
            ->assertSee('10 200 kr', false)
            ->assertSee('2 550 kr', false)
            ->assertSee('12 750 kr', false);
    }

    public function test_sending_requires_a_line_a_total_and_a_customer_email(): void
    {
        Mail::fake();
        [$owner, $project, $customer] = $this->projectWithCustomer();
        $edit = route('app.projects.quote.edit', $project);
        $send = route('app.projects.quote.send', $project);

        $this->actingAs($owner)->from($edit)->post($send)
            ->assertRedirect($edit)
            ->assertSessionHasErrors(['quote' => 'Lägg till minst en rad innan du skickar offerten.']);

        $quote = $project->fresh()->quote;
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'unit_price_ore' => 0]);

        $this->from($edit)->post($send)
            ->assertSessionHasErrors(['quote' => 'Offerten har ingen summa. Fyll i priserna och spara innan du skickar.']);

        $quote->lines()->update(['unit_price_ore' => 450000]);
        $customer->update(['email' => null]);

        $this->from($edit)->post($send)
            ->assertRedirect($edit)
            ->assertSessionHasErrors(['quote' => 'Kunden saknar e-postadress. Lägg till e-posten under Kunder och försök igen.']);

        $quote->refresh();
        $this->assertSame(Quote::STATUS_DRAFT, $quote->status);
        $this->assertNull($quote->token);
        Mail::assertNothingSent();
    }

    public function test_sending_issues_a_token_marks_the_quote_sent_and_mails_the_customer(): void
    {
        Mail::fake();
        [$owner, $project, $customer] = $this->projectWithCustomer();
        $owner->workshop->update(['name' => 'Ekens Fönster', 'contact_email' => 'verkstad@example.test']);
        $quote = Quote::factory()->create(['project_id' => $project->getKey()]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'quantity' => 2, 'unit_price_ore' => 450000]);
        $edit = route('app.projects.quote.edit', $project);

        $this->actingAs($owner)
            ->from($edit)
            ->post(route('app.projects.quote.send', $project))
            ->assertRedirect($edit)
            ->assertSessionHas('status', 'Offerten har skickats till kund@example.test.');

        $quote->refresh();
        $this->assertSame(Quote::STATUS_SENT, $quote->status);
        $this->assertSame(48, strlen((string) $quote->token));
        $this->assertNotNull($quote->sent_at);
        $token = $quote->token;

        Mail::assertSent(QuoteSentMail::class, function (QuoteSentMail $mail) use ($customer, $token): bool {
            $html = $mail->render();

            return $mail->hasTo($customer->email)
                && $mail->url === route('quote.show', ['token' => $token])
                && $mail->envelope()->subject === 'Offert från Ekens Fönster: Villa Ek'
                && $mail->hasReplyTo('verkstad@example.test')
                && str_contains($html, 'Hej Ada Ek!')
                && str_contains($html, '11 250 kr')
                && str_contains($html, $mail->url);
        });

        $this->assertDatabaseHas('project_events', [
            'project_id' => $project->getKey(),
            'kind' => 'quote-sent',
            'user_id' => $owner->getKey(),
        ]);

        $this->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Offert skickad till kund@example.test.', false);

        $this->from($edit)->post(route('app.projects.quote.send', $project))->assertSessionHas('status');
        $this->assertSame($token, $quote->fresh()->token);
        Mail::assertSent(QuoteSentMail::class, 2);
    }

    public function test_editing_a_sent_quote_keeps_the_token(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        $quote = Quote::factory()->sent()->create(['project_id' => $project->getKey()]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey()]);
        $token = $quote->token;

        $this->actingAs($owner)
            ->patch(route('app.projects.quote.update', $project), [
                'lines' => [['description' => 'Ny rad', 'quantity' => '1', 'unit_price' => '900']],
            ])
            ->assertSessionHasNoErrors();

        $quote->refresh();
        $this->assertSame($token, $quote->token);
        $this->assertSame(Quote::STATUS_SENT, $quote->status);
        $this->assertSame(['Ny rad'], $quote->lines->pluck('description')->all());
    }

    public function test_an_accepted_quote_cannot_be_edited_or_sent(): void
    {
        Mail::fake();
        [$owner, $project] = $this->projectWithCustomer();
        $quote = Quote::factory()->accepted()->create([
            'project_id' => $project->getKey(),
            'accepted_name' => 'Ada Ek',
        ]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'description' => 'Låst rad']);
        $edit = route('app.projects.quote.edit', $project);

        $this->actingAs($owner)
            ->get($edit)
            ->assertOk()
            ->assertSee('Godkänd av kunden', false)
            ->assertSee('Ada Ek', false)
            ->assertSee('Låst rad', false)
            ->assertDontSee('Spara offert', false)
            ->assertDontSee('name="lines[', false);

        $this->from($edit)
            ->patch(route('app.projects.quote.update', $project), [
                'lines' => [['description' => 'Ändrad', 'quantity' => '1', 'unit_price' => '1']],
            ])
            ->assertRedirect($edit)
            ->assertSessionHasErrors(['quote' => 'Offerten är godkänd av kunden och kan inte ändras.']);

        $this->from($edit)->post(route('app.projects.quote.fill', $project))->assertSessionHasErrors('quote');
        $this->from($edit)->post(route('app.projects.quote.send', $project))->assertSessionHasErrors('quote');

        $this->assertSame(['Låst rad'], $quote->fresh()->lines->pluck('description')->all());
        Mail::assertNothingSent();
    }

    public function test_the_pdf_downloads_for_a_draft(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        $quote = Quote::factory()->create(['project_id' => $project->getKey(), 'introduction' => 'Hej']);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey()]);

        $response = $this->actingAs($owner)
            ->get(route('app.projects.quote.pdf', $project))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('villa-ek-offert.pdf', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_a_user_from_another_workshop_gets_404(): void
    {
        [, $project] = $this->projectWithCustomer();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('app.projects.quote.edit', $project))->assertNotFound();
        $this->patch(route('app.projects.quote.update', $project), [])->assertNotFound();
        $this->post(route('app.projects.quote.fill', $project))->assertNotFound();
        $this->post(route('app.projects.quote.send', $project))->assertNotFound();
        $this->get(route('app.projects.quote.pdf', $project))->assertNotFound();
        $this->assertSame(0, Quote::query()->count());
    }

    public function test_the_project_page_shows_the_quote_section_in_each_state(): void
    {
        [$owner, $project] = $this->projectWithCustomer();
        $show = route('app.projects.show', $project);

        $this->actingAs($owner)
            ->get($show)
            ->assertOk()
            ->assertSeeInOrder(['Statuslänk', 'TILL KUNDEN', 'Offert', 'Utkast', 'Skriv offert'], false)
            ->assertSee('href="'.route('app.projects.quote.edit', $project).'"', false);

        $quote = Quote::factory()->sent()->create(['project_id' => $project->getKey()]);
        QuoteLine::factory()->create(['quote_id' => $quote->getKey(), 'quantity' => 2, 'unit_price_ore' => 450000]);

        $this->get($show)
            ->assertOk()
            ->assertSee('Skickad', false)
            ->assertSee(route('quote.show', ['token' => $quote->token]), false)
            ->assertSee('11 250 kr inkl. moms', false);

        $quote->update([
            'status' => Quote::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'accepted_name' => '<b>Ada</b>',
        ]);

        $this->get($show)
            ->assertOk()
            ->assertSee('Godkänd', false)
            ->assertSee('&lt;b&gt;Ada&lt;/b&gt;', false)
            ->assertDontSee('<b>Ada</b>', false);
    }

    /**
     * @return array{0: User, 1: Project, 2: Customer}
     */
    private function projectWithCustomer(): array
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
            'contact_name' => 'Ada Ek',
            'email' => 'kund@example.test',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'name' => 'Villa Ek',
        ]);

        return [$owner, $project, $customer];
    }
}
