<?php

namespace Tests\Feature;

use Tests\TestCase;

class TermsPageTest extends TestCase
{
    public function test_terms_page_states_the_price_the_invoice_terms_and_the_right_to_cancel(): void
    {
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $response = $this->get(route('terms'));

        $response->assertOk();
        $response->assertSee('<title>Allmänna villkor — Bågspår</title>', false);
        $response->assertSee('Prodi Digital AB', false);
        $response->assertSee('559214-9370', false);
        $response->assertSee('Tegelbrukets väg 41', false);
        $response->assertSee('195 59 Märsta', false);
        $response->assertSee('kostnadsfritt i 30 dagar', false);
        $response->assertSee('590 kr/mån exkl. moms per verkstad', false);
        $response->assertSee('5 900 kr/år', false);
        $response->assertSee('30 dagars betalningsvillkor', false);
        $response->assertSee('Ingen bindningstid utöver den betalningsperiod', false);
        $response->assertSee('dröjsmålsränta', false);
        $response->assertDontSee('3 månader', false);
        $response->assertSee('ladda ner eller skriva ut en projektrapport', false);
        $response->assertSee('ROT-underlag', false);
        $response->assertSee('inte skatterådgivning', false);
        $response->assertSee('skicka en offert som kunden godkänner via en länk; offerten är verkstadens eget dokument', false);
        $response->assertDontSee('Godkänn leverans', false);
        $response->assertSee('Verkstadens ägare skapar kontot och kan bjuda in medarbetare.', false);
        $response->assertSee('Medarbetare kan inte ändra verkstadens uppgifter eller ta bort projekt.', false);
        $response->assertDontSee('Ett konto per verkstad.', false);
        $response->assertSee('href="'.route('privacy').'"', false);
        $response->assertSee('integritetspolicyn', false);
        $response->assertSee('mailto:kontakt@bagspar.se', false);
        $response->assertDontSee('tel:', false);
        $response->assertDontSee('Fortnox', false);
        $response->assertDontSee('medarbetare kan inte bjudas in', false);
    }

    public function test_terms_make_the_workshop_controller_and_include_the_dpa(): void
    {
        $response = $this->get(route('terms'));

        $response->assertOk();
        $response->assertSee('är verkstaden personuppgiftsansvarig', false);
        $response->assertSee('Prodi Digital AB är personuppgiftsbiträde.', false);
        $response->assertSee('href="'.route('dpa').'"', false);
        $response->assertSee('är en del av dessa villkor och godkänns tillsammans med dem.', false);
        $response->assertDontSee('separat personuppgiftsbiträdesavtal krävs', false);
        $response->assertDontSee('krävs ett separat personuppgiftsbiträdesavtal', false);
        $response->assertDontSee('Kontakta oss innan kontot används för sådan behandling.', false);
    }

    public function test_footer_links_terms_next_to_the_privacy_policy(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'href="'.route('privacy').'"',
                'Integritetspolicy',
                'href="'.route('terms').'"',
                'Villkor',
                'href="'.route('dpa').'"',
                'Personuppgiftsbiträdesavtal',
                'href="'.route('contact').'"',
            ], false);
    }
}
