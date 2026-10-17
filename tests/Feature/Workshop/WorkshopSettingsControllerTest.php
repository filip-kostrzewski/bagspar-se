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
            ->assertSee('Välj bild', false)
            ->assertSee('Ingen logotyp', false);

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

    public function test_settings_page_states_the_seat_price_and_the_current_user_count(): void
    {
        $owner = User::factory()->create();
        User::factory()->member()->create(['workshop_id' => $owner->workshop_id]);

        $this->actingAs($owner)
            ->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('Tre användare ingår, varje ytterligare användare kostar 149 kr/mån.', false)
            ->assertSee('Verkstaden har just nu 2 användare.', false)
            ->assertSee('href="'.route('app.billing.edit').'"', false);
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

    public function test_a_broken_or_invisible_logo_is_not_shown(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $workshop = $owner->workshop;
        $brokenPath = 'workshop-logos/'.$workshop->getKey().'/broken.webp';
        Storage::disk('local')->put($brokenPath, 'inte-en-bild');
        $workshop->update(['logo_path' => $brokenPath]);

        $this->actingAs($owner)
            ->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('Ingen logotyp', false)
            ->assertDontSee('settings-logo-preview', false);

        $this->get(route('app.settings.logo'))->assertNotFound();

        $transparent = imagecreatetruecolor(24, 24);
        imagealphablending($transparent, false);
        imagesavealpha($transparent, true);
        $clear = imagecolorallocatealpha($transparent, 0, 0, 0, 127);
        imagefilledrectangle($transparent, 0, 0, 24, 24, $clear === false ? 0 : $clear);
        ob_start();
        imagewebp($transparent);
        $webp = ob_get_clean();
        imagedestroy($transparent);
        $this->assertIsString($webp);
        Storage::disk('local')->put($brokenPath, $webp);

        $this->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('Ingen logotyp', false)
            ->assertDontSee('settings-logo-preview', false);
        $this->get(route('app.settings.logo'))->assertNotFound();
    }

    public function test_a_logo_is_served_with_its_real_image_type(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $image = imagecreatetruecolor(30, 20);
        $red = imagecolorallocate($image, 180, 30, 30);
        imagefilledrectangle($image, 0, 0, 30, 20, $red === false ? 0 : $red);
        ob_start();
        imagejpeg($image, null, 90);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        $this->assertIsString($jpeg);

        $path = 'workshop-logos/'.$owner->workshop->getKey().'/logo.webp';
        Storage::disk('local')->put($path, $jpeg);
        $owner->workshop->update(['logo_path' => $path]);

        $this->actingAs($owner)
            ->get(route('app.settings.edit'))
            ->assertOk()
            ->assertSee('settings-logo-preview', false)
            ->assertDontSee('Ingen logotyp', false);

        $response = $this->get(route('app.settings.logo'));
        $response->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->assertStringStartsWith("\xFF\xD8\xFF", (string) $response->getContent());
    }

    public function test_settings_logo_preview_shows_the_stored_image(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $image = imagecreatetruecolor(40, 24);
        $green = imagecolorallocate($image, 20, 120, 60);
        imagefilledrectangle($image, 0, 0, 40, 24, $green === false ? 0 : $green);
        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);
        $this->assertIsString($png);

        $path = 'workshop-logos/'.$owner->workshop->getKey().'/logo.png';
        Storage::disk('local')->put($path, $png);
        $owner->workshop->update(['logo_path' => $path]);

        $preview = 'data:image/png;base64,'.base64_encode($png);

        $page = $this->actingAs($owner)->get(route('app.settings.edit'));

        $page->assertOk()
            ->assertSee('<img class="settings-logo-preview" src="'.$preview.'"', false)
            ->assertSee('alt="Nuvarande logotyp för '.$owner->workshop->name.'"', false)
            ->assertSee('width="72"', false)
            ->assertSee('height="72"', false)
            ->assertDontSee('src="'.route('app.settings.logo').'"', false)
            ->assertDontSee('Ingen logotyp', false);

        $this->assertSame(1, preg_match('/<img class="settings-logo-preview" src="([^"]+)"/', (string) $page->getContent(), $matches));
        $decoded = base64_decode(substr(html_entity_decode($matches[1], ENT_QUOTES), strlen('data:image/png;base64,')), true);
        $this->assertIsString($decoded);
        $rendered = imagecreatefromstring($decoded);
        $this->assertNotFalse($rendered);
        $color = imagecolorsforindex($rendered, imagecolorat($rendered, 1, 1));
        imagedestroy($rendered);
        $this->assertSame(20, $color['red']);
        $this->assertSame(120, $color['green']);
        $this->assertSame(60, $color['blue']);

        $css = file_get_contents(public_path('css/workshop.css'));
        $this->assertIsString($css);
        $this->assertMatchesRegularExpression('/\.settings-logo-preview\s*\{[^}]*object-fit:\s*contain;/s', $css);
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
