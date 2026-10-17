<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_explains_the_controller_signup_data_and_rights(): void
    {
        config([
            'bagspar.contact_email' => 'kontakt@bagspar.se',
            'bagspar.lead_notification_email' => 'intern@example.test',
        ]);

        $response = $this->get(route('privacy'));

        $response->assertOk();
        $response->assertSee('Prodi Digital AB', false);
        $response->assertSee('559214-9370', false);
        $response->assertSee('Tegelbrukets väg 41', false);
        $response->assertSee('195 59 Märsta', false);
        $response->assertSee('verkstadens eller företagets namn', false);
        $response->assertSee('e-postadress', false);
        $response->assertSee('tidpunkten då anmälan skickades', false);
        $response->assertSee('så länge de behövs för att följa upp anmälan', false);
        $response->assertSee('Meddelanden via kontaktformuläret', false);
        $response->assertSee('Företag och telefonnummer sparas bara om du fyller i dem.', false);
        $response->assertDontSee('Vi ber inte om telefonnummer', false);
        $response->assertSee('30 dagar', false);
        $response->assertSee('Uppgifter i verkstadens konto', false);
        $response->assertSee('Bildfilerna är privata och visas via en skyddad appfunktion', false);
        $response->assertSee('Om den som har länken godkänner leveransen sparas namnet och tidpunkten på projektet.', false);
        $response->assertSee('Om kunden godkänner en offert via länken sparas namnet och tidpunkten på offerten.', false);
        $response->assertDontSee('ROT-underlag', false);
        $response->assertSee('artikel 6.1 b', false);
        $response->assertSee('Integritetsskyddsmyndigheten', false);
        $response->assertSee('mailto:kontakt@bagspar.se', false);
        $response->assertSee('Kontakt och radering', false);
        $response->assertSee('vill att vi raderar uppgifterna', false);
        $response->assertDontSee('intern@example.test', false);
        $response->assertDontSee('valfri text', false);
        $response->assertDontSee('prototyp', false);
        $response->assertDontSee('tel:', false);
    }

    public function test_privacy_policy_shows_the_contact_email_only_when_configured(): void
    {
        config(['bagspar.contact_email' => 'kontakt@example.test']);

        $response = $this->get(route('privacy'));

        $response->assertOk();
        $response->assertSee('mailto:kontakt@example.test', false);
        $response->assertSee('Kontakt', false);
    }

    public function test_privacy_policy_says_the_company_is_processor_for_the_workshops_customer_data(): void
    {
        $response = $this->get(route('privacy'));

        $response->assertOk();
        $response->assertSee('är Prodi Digital AB personuppgiftsbiträde enligt', false);
        $response->assertSee('href="'.route('dpa').'"', false);
        $response->assertSee('Verkstaden är personuppgiftsansvarig, så kontakta verkstaden om dessa uppgifter.', false);
        $response->assertSee('Om du vill få uppgifter i ett konto raderade', false);
    }

    public function test_old_privacy_path_redirects_to_the_policy(): void
    {
        $this->get('/integritet')->assertRedirect('/integritetspolicy');
    }

    public function test_footer_and_signup_form_link_to_the_privacy_policy(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Integritetspolicy', false);
        $response->assertSee('href="'.route('privacy').'"', false);
    }
}
