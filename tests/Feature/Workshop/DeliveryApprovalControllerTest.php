<?php

namespace Tests\Feature\Workshop;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeliveryApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_owner_can_clear_a_delivery_approval(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $project->forceFill([
            'delivery_approved_at' => Carbon::parse('2026-10-16 10:15:00', 'UTC'),
            'delivery_approved_name' => 'Kiko',
        ])->save();

        $this->actingAs($owner)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertSee('Leverans godkänd', false)
            ->assertSee('Ta bort godkännande', false);

        $this->actingAs($member)
            ->get(route('app.projects.show', $project))
            ->assertOk()
            ->assertDontSee('Ta bort godkännande', false);

        $this->delete(route('app.projects.delivery-approval.destroy', $project))
            ->assertForbidden();
        $this->assertSame('Kiko', $project->fresh()->delivery_approved_name);

        $this->actingAs($other)
            ->delete(route('app.projects.delivery-approval.destroy', $project))
            ->assertNotFound();
        $this->assertSame('Kiko', $project->fresh()->delivery_approved_name);

        $this->post(route('auth.logout'));
        $this->delete(route('app.projects.delivery-approval.destroy', $project))
            ->assertRedirect(route('login'));

        $this->actingAs($owner)
            ->from(route('app.projects.show', $project))
            ->delete(route('app.projects.delivery-approval.destroy', $project))
            ->assertRedirect(route('app.projects.show', $project))
            ->assertSessionHas('status', 'Godkännandet har tagits bort.');

        $fresh = $project->fresh();
        $this->assertNull($fresh->delivery_approved_at);
        $this->assertNull($fresh->delivery_approved_name);
    }
}
