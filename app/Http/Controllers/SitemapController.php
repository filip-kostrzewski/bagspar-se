<?php

namespace App\Http\Controllers;

use DOMDocument;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const FALLBACK_BASE_URL = 'https://bagspar.se';

    /**
     * Named routes for public HTML pages.
     *
     * @var list<string>
     */
    private const PUBLIC_PAGES = [
        'home',
        'demo',
        'guides.documentation',
        'guides.work-card',
        'guides.marking',
        'guides.index',
        'guides.document-renovation',
        'guides.brf-report',
        'guides.intake-protocol',
        'privacy',
        'terms',
        'dpa',
        'contact',
    ];

    public function __invoke(): Response
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $urlset = $document->createElementNS(
            'http://www.sitemaps.org/schemas/sitemap/0.9',
            'urlset',
        );
        $document->appendChild($urlset);

        foreach (self::PUBLIC_PAGES as $routeName) {
            $url = $document->createElementNS(
                'http://www.sitemaps.org/schemas/sitemap/0.9',
                'url',
            );
            $location = $document->createElementNS(
                'http://www.sitemaps.org/schemas/sitemap/0.9',
                'loc',
            );
            $location->appendChild($document->createTextNode($this->absoluteUrl($routeName)));
            $url->appendChild($location);
            $urlset->appendChild($url);
        }

        return response($document->saveXML() ?: '', 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function absoluteUrl(string $routeName): string
    {
        $path = route($routeName, absolute: false);

        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    private function baseUrl(): string
    {
        $configured = config('app.url');

        if (! is_string($configured)) {
            return self::FALLBACK_BASE_URL;
        }

        $configured = rtrim(trim($configured), '/');
        $scheme = parse_url($configured, PHP_URL_SCHEME);
        $host = parse_url($configured, PHP_URL_HOST);

        if (! in_array($scheme, ['http', 'https'], true) || ! is_string($host) || $host === '') {
            return self::FALLBACK_BASE_URL;
        }

        return $configured;
    }
}
