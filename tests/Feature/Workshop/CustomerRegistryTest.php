<?php

namespace Tests\Feature\Workshop;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\CustomerBackfill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_text_customer_names_become_one_customer_per_workshop_without_losing_the_text(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
            'contact_name' => 'Ada Ek',
        ]);
        $first = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'customer_name' => 'BRF Eken',
        ]);
        $second = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Alm',
            'customer_name' => '  brf eken  ',
        ]);
        $foreign = Project::factory()->create([
            'workshop_id' => $other->workshop_id,
            'name' => 'Annat hus',
            'customer_name' => 'BRF Eken',
        ]);
        $blank = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Utan kund',
            'customer_name' => '   ',
        ]);

        CustomerBackfill::run();
        CustomerBackfill::run();

        $first->refresh();
        $second->refresh();
        $foreign->refresh();
        $blank->refresh();

        $this->assertSame(2, Customer::query()->count());
        $this->assertNotNull($first->customer_id);
        $this->assertSame($first->customer_id, $second->customer_id);
        $this->assertSame('BRF Eken', $first->customer?->name);
        $this->assertSame('Ada Ek', $first->customer?->contact_name);
        $this->assertNotSame($first->customer_id, $foreign->customer_id);
        $this->assertSame('BRF Eken', $foreign->customer?->name);
        $this->assertNull($blank->customer_id);
        $this->assertSame('BRF Eken', $first->customer_name);
        $this->assertSame('  brf eken  ', $second->customer_name);
        $this->assertSame('BRF Eken', $foreign->customer_name);
        $this->assertSame('   ', $blank->customer_name);
    }

    public function test_a_member_can_save_a_customer_and_use_it_on_several_projects(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->get(route('app.customers.index'))->assertRedirect(route('login'));

        $this->actingAs($member)
            ->post(route('app.customers.store'), [
                'name' => '',
                'email' => 'inte-en-adress',
            ])
            ->assertSessionHasErrors([
                'name' => 'Ange kundens namn.',
                'email' => 'Ange en giltig e-postadress.',
            ]);

        $this->post(route('app.customers.store'), [
            'name' => '  BRF Eken  ',
            'contact_name' => '  Ada Ek  ',
            'address' => '  Ekgatan 2, Uppsala  ',
            'email' => '  Kontakt@Exempel.TEST  ',
        ])->assertRedirect();

        $customer = Customer::query()->where('workshop_id', $owner->workshop_id)->firstOrFail();
        $this->assertSame('BRF Eken', $customer->name);
        $this->assertSame('Ada Ek', $customer->contact_name);
        $this->assertSame('Ekgatan 2, Uppsala', $customer->address);
        $this->assertSame('kontakt@exempel.test', $customer->email);

        $this->get(route('app.customers.index'))
            ->assertSee('BRF Eken', false)
            ->assertSee('Ada Ek', false)
            ->assertSee('kontakt@exempel.test', false)
            ->assertSee('0 projekt', false);

        $this->post(route('app.projects.store'), [
            'name' => 'Villa Ek',
            'customer_name' => 'brf eken',
            'location' => 'Våning 2',
            'notes' => 'HEMLIG PROJEKTANTECKNING',
        ])->assertRedirect();

        $first = Project::query()->where('name', 'Villa Ek')->firstOrFail();
        $this->assertSame($customer->getKey(), $first->customer_id);
        $this->assertSame('BRF Eken', $first->customer_name);

        $this->post(route('app.projects.store'), [
            'name' => 'Villa Alm',
            'customer_id' => $customer->getKey(),
            'customer_name' => '',
        ])->assertRedirect();

        $second = Project::query()->where('name', 'Villa Alm')->firstOrFail();
        $this->assertSame($customer->getKey(), $second->customer_id);
        $this->assertSame('BRF Eken', $second->customer_name);
        $this->assertSame(1, Customer::query()->where('workshop_id', $owner->workshop_id)->count());

        $sash = WindowSash::factory()->create([
            'project_id' => $first->getKey(),
            'code' => 'V2-H',
            'notes' => 'HEMLIG BÅGANTECKNING',
        ]);

        $this->get(route('app.projects.show', $first))
            ->assertSee('BRF Eken', false)
            ->assertSee('Ada Ek', false);

        $this->get(route('app.projects.report', $first))
            ->assertSee('BRF Eken', false)
            ->assertSee('Ada Ek', false)
            ->assertSee('kontakt@exempel.test', false)
            ->assertSee('Ekgatan 2, Uppsala', false)
            ->assertDontSee('HEMLIG PROJEKTANTECKNING', false)
            ->assertDontSee('HEMLIG BÅGANTECKNING', false);

        $this->post(route('app.projects.client-status.update', $first), ['action' => 'enable'])->assertRedirect();
        $token = $first->fresh()?->client_status_token;
        $this->assertIsString($token);

        $this->get(route('client-status.show', ['token' => $token]))
            ->assertSee('BRF Eken', false)
            ->assertSee('Ada Ek', false)
            ->assertSee('kontakt@exempel.test', false)
            ->assertSee('Ekgatan 2, Uppsala', false)
            ->assertSee('V2-H', false)
            ->assertDontSee('HEMLIG PROJEKTANTECKNING', false)
            ->assertDontSee('HEMLIG BÅGANTECKNING', false);

        $this->patch(route('app.projects.update', $first), ['status' => 'completed'])->assertRedirect();
        $this->assertSame($customer->getKey(), $first->fresh()?->customer_id);

        $this->patch(route('app.projects.update', $second), [
            'customer_name' => '   ',
            'customer_id' => '',
        ])->assertRedirect();
        $second->refresh();
        $this->assertNull($second->customer_id);
        $this->assertNull($second->customer_name);
        $this->assertModelExists($customer);
        $this->assertNotNull($sash->fresh());
    }

    public function test_another_workshops_customer_cannot_be_opened_or_linked(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_name' => 'Egen kund',
        ]);
        $foreign = Customer::factory()->create([
            'workshop_id' => $other->workshop_id,
            'name' => 'Hemlig BRF',
            'email' => 'hemlig@example.test',
        ]);

        $this->actingAs($owner)
            ->get(route('app.customers.edit', $foreign))
            ->assertNotFound();

        $this->patch(route('app.customers.update', $foreign), [
            'name' => 'Stulen',
            'email' => 'stulen@example.test',
        ])->assertNotFound();

        $this->patch(route('app.projects.update', $project), [
            'customer_id' => $foreign->getKey(),
        ])->assertNotFound();

        $this->assertSame('Hemlig BRF', $foreign->fresh()?->name);
        $this->assertNull($project->fresh()?->customer_id);
        $this->assertSame('Egen kund', $project->fresh()?->customer_name);
    }
}
