<?php

namespace Tests\Feature;

use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WorkshopAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_workshop_admin_is_not_found_when_the_token_is_unset(): void
    {
        config(['bagspar.admin_token' => null]);
        $workshop = Workshop::factory()->create();

        $this->get(route('admin.workshops'))->assertNotFound();
        $this->withBasicAuth('bagspar', 'secret')
            ->get(route('admin.workshops'))
            ->assertNotFound();
        $this->withBasicAuth('bagspar', 'secret')
            ->patch(route('admin.workshops.update', $workshop), ['paid_until' => '2027-03-31'])
            ->assertNotFound();
    }

    public function test_missing_credentials_are_rejected(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $workshop = Workshop::factory()->create();

        $this->get(route('admin.workshops'))->assertUnauthorized();
        $this->withBasicAuth('bagspar', 'wrong')
            ->get(route('admin.workshops'))
            ->assertUnauthorized();
        $this->patch(route('admin.workshops.update', $workshop), ['paid_until' => '2027-03-31'])
            ->assertUnauthorized();

        $this->assertNull($workshop->fresh()->paid_until);
    }

    public function test_the_list_shows_every_workshop_with_its_status(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $this->travelTo('2026-10-17 12:00:00');
        Workshop::factory()->create(['name' => 'Provverkstaden', 'trial_ends_at' => now()->addDays(5)]);
        Workshop::factory()->create(['name' => 'Låsta Fönster', 'trial_ends_at' => now()->subDays(5)]);
        Workshop::factory()->create([
            'name' => 'Betalda Bågar',
            'trial_ends_at' => now()->subDays(50),
            'paid_until' => now()->addDays(20),
            'subscription_ordered_at' => now()->subDays(40),
        ]);
        Workshop::factory()->create([
            'name' => 'Beställda Bågar',
            'trial_ends_at' => now()->subDays(5),
            'subscription_ordered_at' => now()->subDays(2),
        ]);

        $this->withBasicAuth('bagspar', 'secret')
            ->get(route('admin.workshops'))
            ->assertOk()
            ->assertSee('4 verkstäder', false)
            ->assertSee('Provverkstaden', false)
            ->assertSee('Låsta Fönster', false)
            ->assertSee('Betalda Bågar', false)
            ->assertSee('Beställda Bågar', false)
            ->assertSee('admin-status--trial', false)
            ->assertSee('admin-status--locked', false)
            ->assertSee('admin-status--active', false)
            ->assertSee('admin-status--ordered', false)
            ->assertSeeInOrder(['Beställda Bågar', 'Beställt'], false);
    }

    public function test_the_list_is_empty_without_workshops(): void
    {
        config(['bagspar.admin_token' => 'secret']);

        $this->withBasicAuth('bagspar', 'secret')
            ->get(route('admin.workshops'))
            ->assertOk()
            ->assertSee('Inga verkstäder ännu.', false);
    }

    public function test_recording_a_paid_period_stores_the_end_of_the_swedish_day_in_utc(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $this->travelTo('2026-10-17 12:00:00');
        $workshop = Workshop::factory()->create([
            'access_reminder_sent_at' => now()->subDay(),
        ]);

        $this->withBasicAuth('bagspar', 'secret')
            ->patch(route('admin.workshops.update', $workshop), [
                'subscription_plan' => 'monthly',
                'paid_until' => '2027-03-31',
            ])
            ->assertRedirect(route('admin.workshops'))
            ->assertSessionHas('status', $workshop->name.' är uppdaterad.');

        $workshop->refresh();
        $this->assertSame('monthly', $workshop->subscription_plan);
        $this->assertTrue(
            Carbon::parse('2027-03-31 23:59:59', 'Europe/Stockholm')->utc()->equalTo($workshop->paid_until),
        );
        $this->assertNotNull($workshop->subscription_ordered_at);
        $this->assertTrue(now()->equalTo($workshop->subscription_ordered_at));
        $this->assertNull($workshop->access_reminder_sent_at);
        $this->assertTrue($workshop->hasAccess());
    }

    public function test_an_existing_order_time_is_kept_when_a_paid_period_is_recorded(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $this->travelTo('2026-10-17 12:00:00');
        $orderedAt = now()->subDays(3);
        $workshop = Workshop::factory()->create(['subscription_ordered_at' => $orderedAt]);

        $this->withBasicAuth('bagspar', 'secret')
            ->patch(route('admin.workshops.update', $workshop), [
                'subscription_plan' => 'yearly',
                'paid_until' => '2027-10-31',
            ])
            ->assertRedirect(route('admin.workshops'));

        $this->assertTrue($orderedAt->equalTo($workshop->fresh()->subscription_ordered_at));
    }

    public function test_the_reminder_is_kept_when_the_paid_date_does_not_change(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $this->travelTo('2026-10-17 12:00:00');
        $paidUntil = Carbon::parse('2027-03-31 23:59:59', 'Europe/Stockholm')->utc();
        $reminderSentAt = now()->subDay();
        $workshop = Workshop::factory()->create([
            'subscription_plan' => 'monthly',
            'subscription_ordered_at' => now()->subDays(40),
            'paid_until' => $paidUntil,
            'access_reminder_sent_at' => $reminderSentAt,
        ]);

        $this->withBasicAuth('bagspar', 'secret')
            ->patch(route('admin.workshops.update', $workshop), [
                'subscription_plan' => 'yearly',
                'paid_until' => '2027-03-31',
            ])
            ->assertRedirect(route('admin.workshops'));

        $workshop->refresh();
        $this->assertSame('yearly', $workshop->subscription_plan);
        $this->assertTrue($reminderSentAt->equalTo($workshop->access_reminder_sent_at));
    }

    public function test_an_empty_paid_date_clears_the_paid_period(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $workshop = Workshop::factory()->create([
            'subscription_plan' => 'monthly',
            'subscription_ordered_at' => now()->subDays(40),
            'paid_until' => now()->addDays(10),
        ]);

        $this->withBasicAuth('bagspar', 'secret')
            ->patch(route('admin.workshops.update', $workshop), [
                'subscription_plan' => '',
                'paid_until' => '',
            ])
            ->assertRedirect(route('admin.workshops'));

        $workshop->refresh();
        $this->assertNull($workshop->paid_until);
        $this->assertNull($workshop->subscription_plan);
    }

    public function test_an_invalid_plan_or_date_is_rejected(): void
    {
        config(['bagspar.admin_token' => 'secret']);
        $workshop = Workshop::factory()->create();

        $this->withBasicAuth('bagspar', 'secret')
            ->from(route('admin.workshops'))
            ->patch(route('admin.workshops.update', $workshop), [
                'subscription_plan' => 'weekly',
                'paid_until' => '2027-03-31',
            ])
            ->assertRedirect(route('admin.workshops'))
            ->assertSessionHasErrors('subscription_plan');

        $this->withBasicAuth('bagspar', 'secret')
            ->from(route('admin.workshops'))
            ->patch(route('admin.workshops.update', $workshop), [
                'subscription_plan' => 'monthly',
                'paid_until' => '31/03/2027',
            ])
            ->assertSessionHasErrors('paid_until');

        $this->assertNull($workshop->fresh()->paid_until);
    }

    public function test_the_workshop_admin_is_kept_out_of_the_sitemap(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('admin/verkstader', false);
    }
}
