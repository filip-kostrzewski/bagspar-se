<?php

namespace Tests\Feature\Workshop;

use App\Mail\ProjectReadyMail;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ProjectReadyMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'postmark',
            'services.postmark.key' => 'server-token-test',
        ]);
    }

    public function test_the_customer_is_mailed_once_when_every_sash_is_ready(): void
    {
        Mail::fake();
        [$member, $project, $customer, $first, $second] = $this->readyProject();
        $project->issueClientStatusToken();
        $project->client_status_enabled = false;
        $project->save();
        $token = $project->client_status_token;

        $this->actingAs($member)
            ->patch(route('app.sashes.update', [$project, $first]), ['stage' => 'ready'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Arbetskortet har uppdaterats.');

        Mail::assertNothingSent();
        $this->assertNull($project->fresh()?->customer_ready_notified_at);
        $this->assertFalse((bool) $project->fresh()?->client_status_enabled);

        $this->patch(route('app.sashes.update', [$project, $second]), ['stage' => 'ready'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Arbetskortet har uppdaterats. Kunden har fått ett mejl om att alla bågar är klara.');

        $project->refresh();
        $this->assertNotNull($project->customer_ready_notified_at);
        $this->assertTrue($project->client_status_enabled);
        $this->assertSame($token, $project->client_status_token);

        Mail::assertSent(ProjectReadyMail::class, function (ProjectReadyMail $mail) use ($customer, $token): bool {
            $html = $mail->render();

            return $mail->hasTo($customer->email)
                && $mail->url === route('client-status.show', ['token' => $token])
                && str_contains($html, 'Villa Ek')
                && str_contains($html, 'Hej Ada Ek!')
                && str_contains($html, $mail->url)
                && ! str_contains($html, 'HEMLIG BÅGANTECKNING')
                && $mail->envelope()->subject === 'Alla bågar är klara — Villa Ek';
        });

        $this->patch(route('app.sashes.update', [$project, $second]), ['notes' => 'HEMLIG BÅGANTECKNING'])
            ->assertSessionHas('status', 'Arbetskortet har uppdaterats.');
        Mail::assertSent(ProjectReadyMail::class, 1);

        $this->patch(route('app.sashes.update', [$project, $second]), ['stage' => 'painting']);
        $this->assertNull($project->fresh()?->customer_ready_notified_at);
        Mail::assertSent(ProjectReadyMail::class, 1);

        $this->patch(route('app.sashes.update', [$project, $second]), ['stage' => 'ready'])
            ->assertSessionHas('status', 'Arbetskortet har uppdaterats. Kunden har fått ett mejl om att alla bågar är klara.');
        Mail::assertSent(ProjectReadyMail::class, 2);

        $this->post(route('app.sashes.store', $project), ['code' => 'V3'])
            ->assertSessionHas('status', 'Arbetskortet har skapats.');
        $this->assertNull($project->fresh()?->customer_ready_notified_at);

        $third = WindowSash::query()->where('code', 'V3')->firstOrFail();
        $this->patch(route('app.sashes.update', [$project, $third]), ['stage' => 'ready']);
        Mail::assertSent(ProjectReadyMail::class, 3);
    }

    public function test_no_mail_is_sent_without_the_choice_or_a_customer_email(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
            'email' => null,
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'customer_name' => 'BRF Eken',
            'notify_customer_when_ready' => true,
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'stage' => 'painting',
        ]);

        $this->actingAs($owner)
            ->patch(route('app.sashes.update', [$project, $sash]), ['stage' => 'ready'])
            ->assertSessionHas('status', 'Arbetskortet har uppdaterats.');

        Mail::assertNothingSent();
        $this->assertNull($project->fresh()?->customer_ready_notified_at);
        $this->assertFalse((bool) $project->fresh()?->client_status_enabled);

        $customer->update(['email' => 'kund@example.test']);
        $project->update(['notify_customer_when_ready' => false]);

        $this->patch(route('app.projects.update', $project), [
            'form_context' => 'project',
            'name' => $project->name,
            'customer_id' => $customer->getKey(),
            'notify_customer_when_ready' => '1',
        ])->assertSessionHas('status', 'Projektet har uppdaterats. Kunden har fått ett mejl om att alla bågar är klara.');

        Mail::assertSent(ProjectReadyMail::class, 1);
        $this->assertTrue((bool) $project->fresh()?->notify_customer_when_ready);

        $this->patch(route('app.projects.update', $project), ['status' => 'completed'])
            ->assertSessionHas('status', 'Projektet har uppdaterats.');
        $this->assertTrue((bool) $project->fresh()?->notify_customer_when_ready);
        Mail::assertSent(ProjectReadyMail::class, 1);
    }

    public function test_a_mail_failure_still_saves_the_sash_stage(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'email' => 'kund@example.test',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
            'notify_customer_when_ready' => true,
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'stage' => 'painting',
        ]);

        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('postmark down'));

        $this->actingAs($owner)
            ->patch(route('app.sashes.update', [$project, $sash]), ['stage' => 'ready'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Arbetskortet har uppdaterats.');

        $this->assertSame('ready', $sash->fresh()?->stage);
        $this->assertNull($project->fresh()?->customer_ready_notified_at);
    }

    public function test_a_new_project_keeps_the_mail_choice_off_until_it_is_selected(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('app.projects.store'), ['name' => 'Utan mejl'])
            ->assertRedirect();

        $this->assertFalse((bool) Project::query()->where('name', 'Utan mejl')->firstOrFail()->notify_customer_when_ready);

        $this->post(route('app.projects.store'), [
            'name' => 'Med mejl',
            'notify_customer_when_ready' => '1',
        ])->assertRedirect();

        $this->assertTrue((bool) Project::query()->where('name', 'Med mejl')->firstOrFail()->notify_customer_when_ready);
        Mail::assertNothingSent();
    }

    /**
     * @return array{0: User, 1: Project, 2: Customer, 3: WindowSash, 4: WindowSash}
     */
    private function readyProject(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);
        $owner->workshop->update([
            'name' => 'Ekens Fönster',
            'contact_email' => 'verkstad@example.test',
        ]);
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'BRF Eken',
            'contact_name' => 'Ada Ek',
            'email' => 'kund@example.test',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'customer_id' => $customer->getKey(),
            'customer_name' => 'BRF Eken',
            'notes' => 'HEMLIG PROJEKTANTECKNING',
            'notify_customer_when_ready' => true,
        ]);
        $first = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'V2-H',
            'stage' => 'painting',
            'notes' => 'HEMLIG BÅGANTECKNING',
        ]);
        $second = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'code' => 'V2-V',
            'stage' => 'painting',
        ]);

        return [$member, $project, $customer, $first, $second];
    }
}
