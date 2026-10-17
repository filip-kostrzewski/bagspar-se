<?php

namespace Tests\Feature\Workshop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkshopSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_workshop_document_details(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('Verkstadens uppgifter', false)
            ->assertSee('name="organization_number"', false)
            ->assertSee('name="contact_email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('Telefon', false)
            ->assertSee('name="address"', false)
            ->assertSee('data-file-picker="logo"', false)
            ->assertSee('Välj bild', false);

        $css = file_get_contents(public_path('css/workshop.css'));
        $this->assertIsString($css);
        $this->assertDoesNotMatchRegularExpression('/\.workshop-header__workspace\s*\{[^}]*display:\s*none/', $css);

        $this->patch(route('app.settings.update'), [
            'name' => 'Ekens Fönster',
            'organization_number' => '559214-9370',
            'contact_email' => '  VERKSTAD@EXAMPLE.TEST ',
            'address' => '  Storgatan 1, Uppsala  ',
        ])->assertRedirect()
            ->assertSessionHas('status', 'Sparat');

        $workshop = $owner->workshop->fresh();
        $this->assertSame('Ekens Fönster', $workshop->name);
        $this->assertSame('559214-9370', $workshop->organization_number);
        $this->assertSame('verkstad@example.test', $workshop->contact_email);
        $this->assertSame('Storgatan 1, Uppsala', $workshop->address);
        $this->assertNull($workshop->phone);
        $this->assertNull($workshop->logo_path);
    }

    public function test_owner_can_save_a_swedish_phone_without_clearing_the_hourly_rate(): void
    {
        $owner = User::factory()->create();
        $owner->workshop->update(['hourly_rate_ore' => 65000]);

        $this->actingAs($owner)
            ->patch(route('app.settings.update'), [
                'name' => $owner->workshop->name,
                'phone' => '070-123 45 67',
            ])->assertRedirect()
            ->assertSessionHas('status', 'Sparat');

        $workshop = $owner->workshop->fresh();
        $this->assertSame('+46701234567', $workshop->phone);
        $this->assertSame(65000, $workshop->hourly_rate_ore);

        $this->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('value="070-123 45 67"', false);

        $this->patch(route('app.settings.update'), [
            'name' => $workshop->name,
            'phone' => '+46 70 123 45 67',
        ])->assertRedirect();

        $workshop = $owner->workshop->fresh();
        $this->assertSame('+46701234567', $workshop->phone);
        $this->assertSame(65000, $workshop->hourly_rate_ore);

        $this->patch(route('app.settings.update'), [
            'name' => $workshop->name,
            'phone' => '',
        ])->assertRedirect();

        $workshop = $owner->workshop->fresh();
        $this->assertNull($workshop->phone);
        $this->assertSame(65000, $workshop->hourly_rate_ore);

        $this->patch(route('app.settings.update'), [
            'name' => $workshop->name,
            'phone' => '+46701234567',
        ])->assertRedirect();

        $this->patch(route('app.settings.update'), [
            'name' => $workshop->name,
        ])->assertRedirect();

        $workshop = $owner->workshop->fresh();
        $this->assertSame('+46701234567', $workshop->phone);
        $this->assertSame(65000, $workshop->hourly_rate_ore);
    }

    public function test_an_invalid_phone_is_rejected_in_swedish(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('app.settings.edit'))
            ->patch(route('app.settings.update'), [
                'name' => $owner->workshop->name,
                'phone' => 'hej',
            ])->assertSessionHasErrors([
                'phone' => 'Ange ett svenskt telefonnummer, till exempel 070-123 45 67.',
            ]);

        $this->assertNull($owner->workshop->fresh()->phone);

        $this->patch(route('app.settings.update'), [
            'name' => $owner->workshop->name,
            'phone' => '+47 70 123 45 67',
        ])->assertSessionHasErrors([
            'phone' => 'Ange ett svenskt telefonnummer, till exempel 070-123 45 67.',
        ]);

        $this->assertNull($owner->workshop->fresh()->phone);
    }

    public function test_invalid_organization_number_is_rejected_in_swedish(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('app.settings.edit'))
            ->patch(route('app.settings.update'), [
                'name' => 'Ekens Fönster',
                'organization_number' => '5592149370',
                'contact_email' => 'inte-en-adress',
            ])->assertSessionHasErrors([
                'organization_number' => 'Ange organisationsnumret med sex siffror, bindestreck och fyra siffror.',
                'contact_email' => 'Ange en giltig e-postadress.',
            ]);

        $this->assertSame($owner->workshop->name, $owner->workshop->fresh()->name);
    }

    public function test_logo_is_stored_privately_and_another_workshop_cannot_read_it(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();

        $this->actingAs($owner)
            ->patch(route('app.settings.update'), [
                'name' => $owner->workshop->name,
                'logo' => UploadedFile::fake()->image('logo.png', 80, 60),
            ])->assertRedirect()
            ->assertSessionHas('status', 'Sparat');

        $workshop = $owner->workshop->fresh();
        $this->assertIsString($workshop->logo_path);
        $this->assertStringStartsWith('workshop-logos/'.$workshop->getKey().'/', $workshop->logo_path);
        $this->assertStringEndsWith('.webp', $workshop->logo_path);
        Storage::disk('local')->assertExists($workshop->logo_path);
        $this->assertSame('RIFF', substr(Storage::disk('local')->get($workshop->logo_path), 0, 4));

        $response = $this->get(route('app.settings.logo'));
        $response->assertOk()
            ->assertHeader('Content-Type', 'image/webp')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);

        $this->actingAs($otherOwner)
            ->get(route('app.settings.logo'))
            ->assertNotFound();

        $this->actingAs($owner)
            ->patch(route('app.settings.update'), [
                'name' => $owner->workshop->name,
                'remove_logo' => '1',
            ])->assertRedirect();

        $this->assertNull($owner->workshop->fresh()->logo_path);
        Storage::disk('local')->assertMissing($workshop->logo_path);
    }

    public function test_owner_can_save_and_clear_an_optional_hourly_rate(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->patch(route('app.settings.update'), [
                'name' => 'Ekens Fönster',
                'hourly_rate' => '650,50',
                'hourly_rate_ore' => 1,
            ])->assertRedirect()
            ->assertSessionHas('status', 'Sparat');

        $this->assertSame(65050, $owner->workshop->fresh()->hourly_rate_ore);

        $this->patch(route('app.settings.update'), [
            'name' => 'Ekens Fönster',
        ])->assertRedirect();

        $this->assertSame(65050, $owner->workshop->fresh()->hourly_rate_ore);

        $this->patch(route('app.settings.update'), [
            'name' => 'Ekens Fönster',
            'hourly_rate' => '',
        ])->assertRedirect();

        $this->assertNull($owner->workshop->fresh()->hourly_rate_ore);
    }

    public function test_an_invalid_hourly_rate_is_rejected_in_swedish(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('app.settings.edit'))
            ->patch(route('app.settings.update'), [
                'name' => 'Ekens Fönster',
                'hourly_rate' => '650,50.1',
            ])->assertSessionHasErrors([
                'hourly_rate' => 'Ange timpriset i kronor, till exempel 650 eller 650,50.',
            ]);

        $this->assertNull($owner->workshop->fresh()->hourly_rate_ore);
    }

    public function test_a_member_cannot_change_workshop_settings(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($member)
            ->patch(route('app.settings.update'), [
                'name' => 'Annat namn',
                'hourly_rate' => '100',
                'phone' => '070-123 45 67',
            ])->assertForbidden();

        $workshop = $owner->workshop->fresh();
        $this->assertNotSame('Annat namn', $workshop->name);
        $this->assertNull($workshop->hourly_rate_ore);
        $this->assertNull($workshop->phone);
    }
}
