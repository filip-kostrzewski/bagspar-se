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
            'https://example.test/integritet',
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
            'https://bagspar.se/integritet',
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
