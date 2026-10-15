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
        $response->assertSee('Reservera pilotplats', false);
        $response->assertSee('Gratis pilot i 3 månader', false);
        $response->assertSee('790 kr/mån exkl. moms efter piloten. Ingen bindningstid.', false);
        $response->assertSee('2-KÖK-F1-B2', false);
        $response->assertSee('Exempelarbetskortet kan ni använda redan nu, utan att anmäla er.', false);
        $response->assertSee('Ett eget konto ingår i piloten.', false);
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
        $response->assertDontSee('första 10', false);
        $response->assertDontSee('tre verkstäder', false);
        $response->assertDontSee('prototyp', false);
        $response->assertDontSee('B-04', false);
        $response->assertSee('mailto:'.config('bagspar.contact_email'), false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"price":"790"', false);
        $response->assertDontSee('aggregateRating', false);
        $response->assertDontSee('tel:', false);
        $response->assertDontSee('bekräftelse', false);
        $response->assertDontSee('skickar en bekräftelse', false);
        $response->assertSee('Vi hör av oss inom några vardagar.', false);
    }

    public function test_homepage_promises_an_email_confirmation_only_when_mail_can_be_sent(): void
    {
        config([
            'mail.default' => 'postmark',
            'services.postmark.key' => 'server-token-test',
        ]);

        $response = $this->get(route('home'));

        $response->assertSee('Du får en bekräftelse via e-post.', false);
        $response->assertSee('Vi hör av oss inom några vardagar.', false);
        $response->assertDontSee('skickar en bekräftelse', false);
    }

    public function test_custom_pilot_offer_replaces_the_default_headline(): void
    {
        config(['bagspar.pilot_offer' => 'Eget erbjudande för testet']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Eget erbjudande för testet', false);
        $response->assertDontSee('Gratis pilot i 3 månader', false);
        $response->assertSee('790 kr/mån exkl. moms efter piloten. Ingen bindningstid.', false);
    }

    public function test_contact_email_is_shown_in_the_footer(): void
    {
        config(['bagspar.contact_email' => 'kontakt@example.test']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('mailto:kontakt@example.test', false);
    }
}
