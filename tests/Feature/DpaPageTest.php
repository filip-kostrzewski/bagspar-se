<?php

namespace Tests\Feature;

use Tests\TestCase;

class DpaPageTest extends TestCase
{
    public function test_dpa_page_names_the_parties_and_the_article_28_content(): void
    {
        config(['bagspar.contact_email' => 'kontakt@bagspar.se']);

        $response = $this->get(route('dpa'));

        $response->assertOk();
        $response->assertSee('<title>Personuppgiftsbiträdesavtal — Bågspår</title>', false);
        $response->assertSee('Personuppgiftsbiträdesavtal', false);
        $response->assertSee('artikel 28', false);
        $response->assertSee('personuppgiftsansvarig', false);
        $response->assertSee('personuppgiftsbiträde', false);
        $response->assertSee('Prodi Digital AB', false);
        $response->assertSee('559214-9370', false);
        $response->assertSee('Uppdaterad 17 oktober 2026 · Version 1.0', false);
        $response->assertSee('Laravel Forge', false);
        $response->assertSee('Cloudflare', false);
        $response->assertSee('Postmark', false);
        $response->assertSee('30 dagar i förväg', false);
        $response->assertSee('48 timmar', false);
        $response->assertSee('inom 30 dagar', false);
        $response->assertSee('standardavtalsklausuler', false);
        $response->assertSee('mailto:kontakt@bagspar.se', false);
        $response->assertSee('href="'.route('terms').'"', false);
        $response->assertSee('href="'.route('privacy').'"', false);
        $response->assertDontSee('ISO', false);
    }

    public function test_dpa_page_is_in_the_sitemap(): void
    {
        config(['app.url' => 'https://example.test']);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('https://example.test/personuppgiftsbitradesavtal', false);
    }
}
