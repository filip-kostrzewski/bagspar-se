<?php

namespace Tests\Feature;

use Tests\TestCase;

class TermsPageTest extends TestCase
{
    public function test_terms_page_states_the_pilot_price_and_the_right_to_cancel(): void
    {
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $response = $this->get(route('terms'));

        $response->assertOk();
        $response->assertSee('<title>Allmänna villkor — Bågspår</title>', false);
        $response->assertSee('Prodi Digital AB', false);
        $response->assertSee('559214-9370', false);
        $response->assertSee('Tegelbrukets väg 41', false);
        $response->assertSee('195 59 Märsta', false);
        $response->assertSee('kostnadsfritt till 31 december 2026', false);
        $response->assertSee('Därefter 790 kr/mån exkl. moms. Ingen bindningstid och ingen automatisk debitering – du väljer själv om du fortsätter.', false);
        $response->assertDontSee('3 månader', false);
        $response->assertSee('Ingen betalning dras automatiskt', false);
        $response->assertSee('Efter piloten: 790 kr/mån exkl. moms. Ingen bindningstid, säg upp när du vill.', false);
        $response->assertSee('Ingen bindningstid och ingen uppsägningstid.', false);
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

    public function test_footer_links_terms_next_to_the_privacy_policy(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'href="'.route('privacy').'"',
                'Integritetspolicy',
                'href="'.route('terms').'"',
                'Villkor',
            ], false);
    }
}
