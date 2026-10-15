<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_homepage_describes_the_product_and_shows_the_pilot_offer(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Prova exempel', false);
        $response->assertSee('href="'.route('demo').'"', false);
        $response->assertSee('Starta gratis pilot', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('Logga in', false);
        $response->assertSee('href="'.route('login').'"', false);
        $response->assertSee('Gratis pilot i 3 månader', false);
        $response->assertSee('790 kr/mån per verkstad exkl. moms', false);
        $response->assertSee('En inloggning, utan medarbetarinbjudningar.', false);
        $response->assertSee('Utskrivbar QR-etikett', false);
        $response->assertSee('Öppnar det privata arbetskortet efter inloggning.', false);
        $response->assertSee('Upp till 20 per båge · högst 5 MB per foto.', false);
        $response->assertSee('Skriv ut eller spara som PDF i webbläsaren.', false);
        $response->assertSee('Fortsättning avtalas separat. Inget debiteras automatiskt.', false);
        $response->assertSee('2-KÖK-F1-B2', false);
        $response->assertSee('Ett exempel med testuppgifter är öppet utan konto.', false);
        $response->assertSee('Det här formuläret skapar inget konto.', false);
        $response->assertSee('Verkstadskontot behöver internetanslutning.', false);
        $response->assertSee('Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta', false);
        $response->assertSee('name="company"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('href="'.route('privacy').'"', false);
        $response->assertSee('href="'.route('guides.documentation').'"', false);
        $response->assertSee('href="'.route('guides.work-card').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $response->assertSee('href="https://wisegent.se/se"', false);
        $response->assertSee('Wisegent – system för städföretag', false);
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
        $response->assertSee('Lämna verkstadens namn och e-post om du vill stämma av piloten.', false);
    }

    public function test_homepage_promises_an_email_confirmation_only_when_mail_can_be_sent(): void
    {
        config([
            'mail.default' => 'postmark',
            'services.postmark.key' => 'server-token-test',
        ]);

        $response = $this->get(route('home'));

        $response->assertSee('Du får en bekräftelse via e-post.', false);
        $response->assertSee('Lämna verkstadens namn och e-post om du vill stämma av piloten.', false);
        $response->assertDontSee('skickar en bekräftelse', false);
    }

    public function test_custom_pilot_offer_replaces_the_default_headline(): void
    {
        config(['bagspar.pilot_offer' => 'Eget erbjudande för testet']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Eget erbjudande för testet', false);
        $response->assertDontSee('Gratis pilot i 3 månader', false);
        $response->assertSee('790 kr/mån per verkstad exkl. moms', false);
    }

    public function test_contact_email_is_shown_in_the_footer(): void
    {
        config(['bagspar.contact_email' => 'kontakt@example.test']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('mailto:kontakt@example.test', false);
    }
}
