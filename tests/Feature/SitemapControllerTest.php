<?php

namespace Tests\Feature;

use DOMDocument;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    #[TestWith(['https://example.test'])]
    #[TestWith(['https://example.test/'])]
    public function test_sitemap_lists_public_pages_with_absolute_urls_from_the_configured_app_url(string $appUrl): void
    {
        config(['app.url' => $appUrl]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertSitemapLists($response->getContent(), [
            'https://example.test/',
            'https://example.test/demo',
            'https://example.test/fonsterrenovering-dokumentation',
            'https://example.test/arbetskort-fonsterbagar',
            'https://example.test/markning-av-fonsterbagar',
            'https://example.test/guider',
            'https://example.test/guider/dokumentera-fonsterrenovering',
            'https://example.test/guider/rapport-till-brf-fonsterrenovering',
            'https://example.test/guider/mottagningsprotokoll-fonsterbagar',
            'https://example.test/integritetspolicy',
            'https://example.test/villkor',
            'https://example.test/personuppgiftsbitradesavtal',
            'https://example.test/kontakt',
        ]);
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['   '])]
    #[TestWith(['not-a-url'])]
    public function test_sitemap_falls_back_to_bagspar_when_app_url_is_not_an_absolute_http_url(?string $appUrl): void
    {
        config(['app.url' => $appUrl]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertSitemapLists($response->getContent(), [
            'https://bagspar.se/',
            'https://bagspar.se/demo',
            'https://bagspar.se/fonsterrenovering-dokumentation',
            'https://bagspar.se/arbetskort-fonsterbagar',
            'https://bagspar.se/markning-av-fonsterbagar',
            'https://bagspar.se/guider',
            'https://bagspar.se/guider/dokumentera-fonsterrenovering',
            'https://bagspar.se/guider/rapport-till-brf-fonsterrenovering',
            'https://bagspar.se/guider/mottagningsprotokoll-fonsterbagar',
            'https://bagspar.se/integritetspolicy',
            'https://bagspar.se/villkor',
            'https://bagspar.se/personuppgiftsbitradesavtal',
            'https://bagspar.se/kontakt',
        ]);
    }

    #[TestWith(['http://bagspar.se'])]
    #[TestWith(['http://bagspar.se/'])]
    public function test_sitemap_lists_https_urls_when_production_app_url_is_http(string $appUrl): void
    {
        $this->useProductionAppUrl($appUrl);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertSitemapLists($response->getContent(), [
            'https://bagspar.se/',
            'https://bagspar.se/demo',
            'https://bagspar.se/fonsterrenovering-dokumentation',
            'https://bagspar.se/arbetskort-fonsterbagar',
            'https://bagspar.se/markning-av-fonsterbagar',
            'https://bagspar.se/guider',
            'https://bagspar.se/guider/dokumentera-fonsterrenovering',
            'https://bagspar.se/guider/rapport-till-brf-fonsterrenovering',
            'https://bagspar.se/guider/mottagningsprotokoll-fonsterbagar',
            'https://bagspar.se/integritetspolicy',
            'https://bagspar.se/villkor',
            'https://bagspar.se/personuppgiftsbitradesavtal',
            'https://bagspar.se/kontakt',
        ]);
    }

    public function test_generated_urls_use_https_when_production_app_url_is_http(): void
    {
        $this->useProductionAppUrl('http://bagspar.se');

        $response = $this->get('/');

        $response->assertSee('<link rel="canonical" href="https://bagspar.se/">', false);
        $response->assertSee('href="https://bagspar.se/css/site.css?v='.config('bagspar.asset_version').'"', false);
        $response->assertSee('href="https://bagspar.se/demo"', false);
        $response->assertSee('href="https://bagspar.se/fonsterrenovering-dokumentation"', false);
        $response->assertSee('href="https://bagspar.se/arbetskort-fonsterbagar"', false);
        $response->assertSee('href="https://bagspar.se/markning-av-fonsterbagar"', false);
        $response->assertSee('href="https://bagspar.se/integritetspolicy"', false);
        $response->assertSee('href="https://bagspar.se/villkor"', false);
        $response->assertSee('href="https://bagspar.se/personuppgiftsbitradesavtal"', false);
        $response->assertSee('href="https://bagspar.se/kontakt"', false);
        $response->assertSee('action="https://bagspar.se/kontakt"', false);
        $response->assertSee('href="https://bagspar.se/guider"', false);
        $response->assertSee('href="https://bagspar.se/guider/dokumentera-fonsterrenovering"', false);
        $response->assertDontSee('wisegent.se', false);
        $response->assertDontSee('http://bagspar.se', false);
        $this->assertSame('https://bagspar.se/integritetspolicy', route('privacy'));
        $this->assertSame('https://bagspar.se/integritetspolicy', redirect()->route('privacy')->getTargetUrl());
        $this->assertSame('https://bagspar.se/demo', route('demo'));
    }

    #[TestWith(['http://bagspar.test'])]
    #[TestWith(['http://localhost'])]
    public function test_sitemap_keeps_http_urls_when_the_app_is_not_in_production(string $appUrl): void
    {
        config(['app.url' => $appUrl]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertSitemapLists($response->getContent(), [
            $appUrl.'/',
            $appUrl.'/demo',
            $appUrl.'/fonsterrenovering-dokumentation',
            $appUrl.'/arbetskort-fonsterbagar',
            $appUrl.'/markning-av-fonsterbagar',
            $appUrl.'/guider',
            $appUrl.'/guider/dokumentera-fonsterrenovering',
            $appUrl.'/guider/rapport-till-brf-fonsterrenovering',
            $appUrl.'/guider/mottagningsprotokoll-fonsterbagar',
            $appUrl.'/integritetspolicy',
            $appUrl.'/villkor',
            $appUrl.'/personuppgiftsbitradesavtal',
            $appUrl.'/kontakt',
        ]);
    }

    public function test_robots_txt_references_the_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertSame(
            "User-agent: *\nDisallow:\n\nSitemap: https://bagspar.se/sitemap.xml\n",
            $robots,
        );
    }

    private function useProductionAppUrl(string $appUrl): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => $appUrl]);
    }

    /**
     * @param  list<string>  $locations
     */
    private function assertSitemapLists(string $xml, array $locations): void
    {
        $document = new DOMDocument;
        $this->assertTrue($document->loadXML($xml));

        $root = $document->documentElement;
        $this->assertNotNull($root);
        $this->assertSame('urlset', $root->localName);
        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', $root->namespaceURI);

        $actual = [];

        foreach ($document->getElementsByTagName('loc') as $location) {
            $actual[] = $location->textContent;
        }

        $this->assertSame($locations, $actual);
    }
}
