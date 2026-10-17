<?php

namespace Tests\Feature\Workshop;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\SashEvent;
use App\Models\SashPhoto;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerGdprTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_export_the_customer_as_json_and_csv(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => '=BRF <script>alert(1)</script>',
            'contact_name' => 'Ada Ek',
            'phone' => '070-111 22 33',
            'address' => 'Storgatan 1',
            'email' => 'ada@example.test',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'name' => 'Villa Ek',
            'customer_name' => $customer->name,
            'location' => 'Våning 2',
            'notes' => 'Behåll originalglaset.',
            'status' => 'active',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'V2-H',
            'room' => 'Kök',
            'location' => 'Höger',
            'stage' => 'glazing',
            'notes' => 'Kitt saknas.',
        ]);
        SashEvent::factory()->create([
            'sash_id' => $sash->getKey(),
            'user_id' => $member->getKey(),
            'user_name' => 'Bo Berg',
            'kind' => SashEvent::KIND_NOTE,
            'stage' => null,
            'body' => 'Nytt kitt.',
        ]);
        $photo = SashPhoto::factory()->create([
            'sash_id' => $sash->getKey(),
            'moment' => 'before',
            'original_filename' => 'fore.webp',
        ]);
        ProjectEvent::factory()->create([
            'project_id' => $project->getKey(),
            'user_id' => $owner->getKey(),
            'user_name' => 'Anna Andersson',
            'kind' => ProjectEvent::KIND_SASH_REMOVED,
            'body' => 'Bågen B-1 togs bort.',
        ]);
        $loose = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => null,
            'name' => 'Löst projekt',
            'customer_name' => $customer->name,
        ]);
        $foreign = Customer::factory()->create([
            'workshop_id' => $other->workshop_id,
            'name' => 'Hemlig granne',
        ]);
        Project::factory()->create([
            'workshop_id' => $other->workshop_id,
            'customer_id' => $foreign->getKey(),
            'name' => 'Grannens projekt',
            'customer_name' => 'Hemlig granne',
        ]);

        $this->get(route('app.customers.export.json', $customer))->assertRedirect(route('login'));

        $this->actingAs($other)->get(route('app.customers.export.json', $customer))->assertNotFound();
        $this->actingAs($other)->get(route('app.customers.export.csv', $customer))->assertNotFound();

        $this->actingAs($member)
            ->get(route('app.customers.edit', $customer))
            ->assertOk()
            ->assertSee('Exportera kunddata', false)
            ->assertSee(route('app.customers.export.json', $customer), false)
            ->assertSee(route('app.customers.export.csv', $customer), false)
            ->assertDontSee('Radera kund', false)
            ->assertSee('=BRF &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $json = $this->get(route('app.customers.export.json', $customer));
        $json->assertOk();
        $json->assertHeader('Content-Type', 'application/json; charset=UTF-8');
        $json->assertHeader('Content-Disposition', 'attachment; filename="kund-'.$customer->getKey().'.json"');
        $body = $json->getContent();
        $this->assertIsString($body);
        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringContainsString('\u003Cscript\u003Ealert(1)\u003C\/script\u003E', $body);
        $this->assertStringNotContainsString('Hemlig granne', $body);
        $this->assertStringNotContainsString('Löst projekt', $body);
        $this->assertStringNotContainsString('Grannens projekt', $body);
        $json->assertJsonPath('kund.namn', '=BRF <script>alert(1)</script>');
        $json->assertJsonPath('kund.telefon', '070-111 22 33');
        $json->assertJsonPath('kund.epost', 'ada@example.test');
        $json->assertJsonPath('projekt.0.namn', 'Villa Ek');
        $json->assertJsonPath('projekt.0.anteckningar', 'Behåll originalglaset.');
        $json->assertJsonPath('projekt.0.bagar.0.kod', 'V2-H');
        $json->assertJsonPath('projekt.0.bagar.0.etapp', 'Glasning');
        $json->assertJsonPath('projekt.0.bagar.0.historik.0.text', 'Nytt kitt.');
        $json->assertJsonPath('projekt.0.bagar.0.historik.0.anvandare', 'Bo Berg');
        $json->assertJsonPath('projekt.0.bagar.0.bilder.0.url', route('app.sashes.photos.show', [$project, $sash, $photo]));
        $json->assertJsonPath('projekt.0.historik.0.text', 'Bågen B-1 togs bort.');
        $this->assertSame($loose->name, 'Löst projekt');

        $csvResponse = $this->get(route('app.customers.export.csv', $customer));
        $csvResponse->assertOk();
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $csvResponse->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Typ;Kund;Projekt;Båge;Uppgift;Värde', $csv);
        $this->assertStringContainsString("'=BRF <script>alert(1)</script>", $csv);
        $this->assertStringContainsString('Villa Ek', $csv);
        $this->assertStringContainsString('V2-H', $csv);
        $this->assertStringContainsString('Nytt kitt.', $csv);
        $this->assertStringContainsString('Bågen B-1 togs bort.', $csv);
        $this->assertStringContainsString(route('app.sashes.photos.show', [$project, $sash, $photo]), $csv);
        $this->assertStringNotContainsString('Hemlig granne', $csv);
        $this->assertStringNotContainsString('Löst projekt', $csv);
    }

    public function test_only_the_owner_can_erase_a_customer_by_typing_the_name(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'name' => 'Villa Ek',
            'customer_name' => 'BRF Eken',
            'notes' => 'Behåll den här anteckningen.',
        ]);
        $loose = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => null,
            'name' => 'Löst projekt',
            'customer_name' => 'BRF Eken',
        ]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/bild.webp';
        Storage::disk('local')->put($path, 'bild');
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $path]);

        $this->actingAs($owner)
            ->get(route('app.customers.edit', $customer))
            ->assertOk()
            ->assertSee('Radera kund', false)
            ->assertSee('data-confirm="Radera kunden? Det går inte att ångra."', false)
            ->assertSee('Skriv BRF Eken för att bekräfta.', false)
            ->assertSee('value="anonymize"', false)
            ->assertSee('value="delete-projects"', false);

        $this->actingAs($member)
            ->delete(route('app.customers.destroy', $customer), [
                'removal' => 'anonymize',
                'confirmation' => 'BRF Eken',
            ])
            ->assertForbidden();
        $this->assertModelExists($customer);

        $this->actingAs($other)
            ->delete(route('app.customers.destroy', $customer), [
                'removal' => 'anonymize',
                'confirmation' => 'BRF Eken',
            ])
            ->assertNotFound();

        $this->actingAs($owner)
            ->from(route('app.customers.edit', $customer))
            ->delete(route('app.customers.destroy', $customer), [
                'removal' => 'anonymize',
                'confirmation' => 'Fel namn',
            ])
            ->assertRedirect(route('app.customers.edit', $customer))
            ->assertInvalid(['confirmation' => 'Skriv kundens namn för att bekräfta.']);
        $this->assertModelExists($customer);
        $this->assertSame('BRF Eken', $project->refresh()->customer_name);

        $this->from(route('app.customers.edit', $customer))
            ->delete(route('app.customers.destroy', $customer), [
                'removal' => 'radera-allt',
                'confirmation' => 'BRF Eken',
            ])
            ->assertRedirect(route('app.customers.edit', $customer))
            ->assertInvalid(['removal' => 'Välj om projekten ska behållas eller tas bort.']);
        $this->assertModelExists($customer);

        $this->delete(route('app.customers.destroy', $customer), [
            'removal' => 'anonymize',
            'confirmation' => 'BRF Eken',
        ])
            ->assertRedirect(route('app.customers.index'))
            ->assertSessionHas('status', 'Kunden har raderats. Projekten är kvar utan kunduppgifter.');

        $this->assertModelMissing($customer);
        $project->refresh();
        $this->assertNull($project->customer_id);
        $this->assertSame(Customer::ANONYMIZED_NAME, $project->customer_name);
        $this->assertSame('Behåll den här anteckningen.', $project->notes);
        $this->assertModelExists($sash);
        Storage::disk('local')->assertExists($path);
        $loose->refresh();
        $this->assertSame('BRF Eken', $loose->customer_name);
        $this->assertNull($loose->customer_id);
    }

    public function test_the_owner_can_delete_the_customer_together_with_the_projects(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'customer_name' => 'BRF Eken',
        ]);
        $kept = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Annat projekt',
        ]);
        $sash = WindowSash::factory()->create(['project_id' => $project->getKey()]);
        $path = 'sash-photos/'.$project->getKey().'/'.$sash->getKey().'/bild.webp';
        $unsafe = 'workshop-logos/logo.webp';
        Storage::disk('local')->put($path, 'bild');
        Storage::disk('local')->put($unsafe, 'logo');
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $path]);
        SashPhoto::factory()->create(['sash_id' => $sash->getKey(), 'path' => $unsafe]);

        $this->actingAs($owner)
            ->delete(route('app.customers.destroy', $customer), [
                'removal' => 'delete-projects',
                'confirmation' => 'BRF Eken',
            ])
            ->assertRedirect(route('app.customers.index'))
            ->assertSessionHas('status', 'Kunden, projekten och bildfilerna har tagits bort.');

        $this->assertModelMissing($customer);
        $this->assertModelMissing($project);
        $this->assertModelMissing($sash);
        $this->assertModelExists($kept);
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists($unsafe);
    }

    public function test_erasing_a_customer_without_projects_removes_only_the_customer(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Ensam kund',
        ]);

        $this->actingAs($owner)
            ->delete(route('app.customers.destroy', $customer), [
                'removal' => 'delete-projects',
                'confirmation' => 'Ensam kund',
            ])
            ->assertRedirect(route('app.customers.index'));

        $this->assertModelMissing($customer);
    }
}
