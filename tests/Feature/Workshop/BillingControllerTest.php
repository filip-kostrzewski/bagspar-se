<?php

namespace Tests\Feature\Workshop;

use App\Mail\SubscriptionOrderConfirmation;
use App\Mail\SubscriptionOrderNotification;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validOrder(array $overrides = []): array
    {
        return array_merge([
            'plan' => 'yearly',
            'organization_number' => '559214-9370',
            'billing_email' => '  FAKTURA@EXAMPLE.TEST ',
            'billing_reference' => 'Anna',
            'terms' => '1',
        ], $overrides);
    }

    public function test_the_owner_sees_the_order_form_with_the_price(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('app.billing.edit'))
            ->assertOk()
            ->assertSee('Beställ abonnemang', false)
            ->assertSee('name="plan"', false)
            ->assertSee('590 kr/mån', false)
            ->assertSee('>Abonnemang</a>', false);
    }

    public function test_the_page_prices_extra_users_beyond_the_three_that_are_included(): void
    {
        $owner = User::factory()->create();
        User::factory()->member()->count(4)->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.billing.edit'))
            ->assertOk()
            ->assertSee('Per månad · 888 kr/mån', false)
            ->assertSee('5 av 3 som ingår · 2 extra', false);
    }

    public function test_the_owner_orders_a_yearly_subscription_and_both_mails_are_sent(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => 'faktura@example.test']);
        $owner = User::factory()->create();
        $workshop = $owner->workshop;

        $this->actingAs($owner)
            ->post(route('app.billing.store'), $this->validOrder())
            ->assertRedirect(route('app.billing.edit'))
            ->assertSessionHas('status', 'Tack för beställningen. Fakturan kommer till faktura@example.test.');

        $workshop->refresh();
        $this->assertSame('yearly', $workshop->subscription_plan);
        $this->assertSame('faktura@example.test', $workshop->billing_email);
        $this->assertSame('559214-9370', $workshop->organization_number);
        $this->assertSame('Anna', $workshop->billing_reference);
        $this->assertNotNull($workshop->subscription_ordered_at);

        Mail::assertSent(SubscriptionOrderNotification::class, function (SubscriptionOrderNotification $mail) use ($workshop): bool {
            $mail->assertSeeInHtml($workshop->name);
            $mail->assertSeeInHtml('5 900 kr/år');
            $mail->assertSeeInHtml('559214-9370');

            return $mail->hasTo('faktura@example.test') && $mail->isFirstOrder;
        });
        Mail::assertSent(SubscriptionOrderConfirmation::class, function (SubscriptionOrderConfirmation $mail): bool {
            $mail->assertSeeInHtml('5 900 kr/år');

            return $mail->hasTo('faktura@example.test')
                && $mail->envelope()->subject === 'Tack för din beställning av Bågspår';
        });
    }

    public function test_the_owner_sees_the_confirmation_after_ordering(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->followingRedirects()
            ->post(route('app.billing.store'), $this->validOrder(['plan' => 'monthly']))
            ->assertOk()
            ->assertSee('Tack för beställningen. Fakturan kommer till faktura@example.test.', false)
            ->assertSee('Ändra abonnemanget', false);
    }

    public function test_the_notification_is_skipped_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => null]);
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('app.billing.store'), $this->validOrder())
            ->assertRedirect(route('app.billing.edit'));

        Mail::assertNotSent(SubscriptionOrderNotification::class);
        Mail::assertSent(SubscriptionOrderConfirmation::class);
    }

    public function test_changing_the_plan_keeps_the_original_order_time(): void
    {
        Mail::fake();
        config(['bagspar.lead_notification_email' => 'faktura@example.test']);
        $this->travelTo('2026-10-17 12:00:00');
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('app.billing.store'), $this->validOrder());
        $orderedAt = $owner->workshop->fresh()->subscription_ordered_at;
        $this->assertNotNull($orderedAt);

        $this->travel(1)->day();

        $this->actingAs($owner)
            ->post(route('app.billing.store'), $this->validOrder(['plan' => 'monthly']))
            ->assertRedirect(route('app.billing.edit'))
            ->assertSessionHas('status', 'Abonnemanget är uppdaterat.');

        $workshop = $owner->workshop->fresh();
        $this->assertSame('monthly', $workshop->subscription_plan);
        $this->assertTrue($orderedAt->equalTo($workshop->subscription_ordered_at));
        Mail::assertSent(SubscriptionOrderNotification::class, fn (SubscriptionOrderNotification $mail): bool => ! $mail->isFirstOrder);
    }

    public function test_a_member_cannot_order(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($member)
            ->post(route('app.billing.store'), $this->validOrder())
            ->assertForbidden();

        $this->assertNull($owner->workshop->fresh()->subscription_ordered_at);
        Mail::assertNothingSent();
    }

    public function test_an_invalid_organization_number_is_rejected_in_swedish(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('app.billing.edit'))
            ->post(route('app.billing.store'), $this->validOrder(['organization_number' => '5592149370']))
            ->assertRedirect(route('app.billing.edit'))
            ->assertSessionHasErrors([
                'organization_number' => 'Ange organisationsnumret med sex siffror, bindestreck och fyra siffror.',
            ]);

        $this->assertNull($owner->workshop->fresh()->subscription_ordered_at);
        Mail::assertNothingSent();
    }

    public function test_the_terms_must_be_accepted(): void
    {
        $owner = User::factory()->create();
        $order = $this->validOrder();
        unset($order['terms']);

        $this->actingAs($owner)
            ->from(route('app.billing.edit'))
            ->post(route('app.billing.store'), $order)
            ->assertSessionHasErrors(['terms' => 'Godkänn villkoren för att beställa.']);

        $this->assertNull($owner->workshop->fresh()->subscription_ordered_at);
    }

    public function test_an_unknown_plan_is_rejected(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('app.billing.edit'))
            ->post(route('app.billing.store'), $this->validOrder(['plan' => 'weekly']))
            ->assertSessionHasErrors(['plan' => 'Välj månads- eller årsabonnemang.']);
    }

    public function test_a_blank_reference_is_stored_as_null(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('app.billing.store'), $this->validOrder(['billing_reference' => '   ']))
            ->assertSessionHasNoErrors();

        $this->assertNull($owner->workshop->fresh()->billing_reference);
    }

    public function test_an_ordered_workshop_sees_the_change_form_and_its_plan(): void
    {
        $owner = User::factory()->create();
        $owner->workshop->update([
            'subscription_plan' => Workshop::PLAN_YEARLY,
            'subscription_ordered_at' => now(),
            'billing_email' => 'faktura@example.test',
        ]);

        $this->actingAs($owner)
            ->get(route('app.billing.edit'))
            ->assertOk()
            ->assertSee('Ändra abonnemanget', false)
            ->assertSee('Årsabonnemang är beställt', false)
            ->assertSee('Beställt, väntar på faktura', false);
    }
}
