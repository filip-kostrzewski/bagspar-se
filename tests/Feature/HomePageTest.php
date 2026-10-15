<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_homepage_describes_the_product_and_shows_the_pilot_offer(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Se hur ett arbetskort ser ut', false);
        $response->assertSee('href="'.route('demo').'"', false);
        $response->assertSee('Gratis pilot i 3 månader för de första 10 verkstäderna', false);
        $response->assertSee('790 kr/mån exkl. moms efter piloten. Ingen bindningstid.', false);
        $response->assertSee('Piloten startar när tre verkstäder anmält sig – du får tillgång till prototypen direkt.', false);
        $response->assertSee('Bågspår är byggt offline-först', false);
        $response->assertSee('Fungerar i svag täckning', false);
        $response->assertSee('Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta', false);
        $response->assertSee('name="company"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('href="'.route('privacy').'"', false);
        $response->assertSee('href="'.route('guides.documentation').'"', false);
        $response->assertSee('href="'.route('guides.work-card').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $response->assertSee('<link rel="manifest" href="'.route('manifest').'">', false);
        $response->assertSee('rel="apple-touch-icon"', false);
        $response->assertDontSee('name="message"', false);
        $response->assertDontSee('name="contact_consent"', false);
        $response->assertDontSee('tänkt', false);
        $response->assertDontSee('planerat', false);
        $response->assertDontSee('tanken är', false);
        $response->assertDontSee('Så fungerar idén', false);
        $response->assertDontSee('mailto:', false);
    }

    public function test_custom_pilot_offer_replaces_the_default_headline(): void
    {
        config(['bagspar.pilot_offer' => 'Eget erbjudande för testet']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Eget erbjudande för testet', false);
        $response->assertDontSee('Gratis pilot i 3 månader för de första 10 verkstäderna', false);
        $response->assertSee('790 kr/mån exkl. moms efter piloten. Ingen bindningstid.', false);
    }

    public function test_contact_email_is_shown_in_the_footer_only_when_configured(): void
    {
        config(['bagspar.contact_email' => 'kontakt@example.test']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('mailto:kontakt@example.test', false);
    }
}
