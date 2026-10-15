<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class GuidePageTest extends TestCase
{
    #[TestWith([
        'guides.documentation',
        'Dokumentation vid fönsterrenovering — Bågspår',
        'Vilka uppgifter som ska följa med varje båge från demontering till återmontering: plats, skick, glas, material, bilder och arbetssteg.',
        'Vad som ska följa med bågen',
        'guides.work-card',
        'arbetskort för fönsterbågar',
        'guides.marking',
        'märkningen av fönsterbågen',
    ])]
    #[TestWith([
        'guides.work-card',
        'Arbetskort för fönsterbågar — Bågspår',
        'Ett arbetskort per båge, med nummer, plats, QR-kod, arbetssteg, anteckningar och bilder. Kortet öppnas i mobilen.',
        'Ett kort per båge',
        'guides.documentation',
        'dokumentationen av fönsterrenoveringen',
        'guides.marking',
        'märkningen av bågen',
    ])]
    #[TestWith([
        'guides.marking',
        'Märkning av fönsterbågar — Bågspår',
        'Så märker du bågen så att den kommer tillbaka till samma öppning: ett nummer, en fysisk lapp som tål färg, och en QR-kod till arbetskortet.',
        'Samma båge, samma öppning',
        'guides.work-card',
        'arbetskortet',
        'guides.documentation',
        'dokumentationen',
    ])]
    public function test_guide_page_is_useful_and_links_onward(
        string $routeName,
        string $title,
        string $description,
        string $heading,
        string $firstRelatedRoute,
        string $firstRelatedLabel,
        string $secondRelatedRoute,
        string $secondRelatedLabel,
    ): void {
        $response = $this->get(route($routeName));

        $response->assertOk();
        $response->assertSee('<title>'.$title.'</title>', false);
        $response->assertSee('<meta name="description" content="'.$description.'">', false);
        $response->assertSee($heading, false);
        $response->assertSee('href="'.route($firstRelatedRoute).'"', false);
        $response->assertSee($firstRelatedLabel, false);
        $response->assertSee('href="'.route($secondRelatedRoute).'"', false);
        $response->assertSee($secondRelatedLabel, false);
        $response->assertSee('href="'.route('demo').'"', false);
        $response->assertSee('Se hur ett arbetskort ser ut', false);
        $response->assertSee('href="'.route('home').'#intresse"', false);
        $response->assertSee('Anmäl verkstaden', false);
        $response->assertDontSee('tänkt', false);
        $response->assertDontSee('planerat', false);
        $response->assertDontSee('tanken är', false);
    }

    #[TestWith(['guides.documentation'])]
    #[TestWith(['guides.work-card'])]
    #[TestWith(['guides.marking'])]
    public function test_guide_page_has_workshop_depth_and_matching_faq_schema(string $routeName): void
    {
        $response = $this->get(route($routeName));
        $html = $response->getContent();

        $wordCount = $this->mainContentWordCount($html);
        $this->assertGreaterThanOrEqual(900, $wordCount, $routeName.' has '.$wordCount.' words');
        $this->assertLessThanOrEqual(1200, $wordCount, $routeName.' has '.$wordCount.' words');

        $response->assertSee('<h2>', false);
        $response->assertSee('<h3>', false);
        $response->assertSee('2-KÖK-F1-B2', false);
        $response->assertSee('<table', false);
        $response->assertSee('Vanliga misstag', false);
        $response->assertSee('Frågor och svar', false);

        $this->assertSame(1, preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches));
        $schema = json_decode($matches[1], true);
        $this->assertIsArray($schema);
        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertSame('FAQPage', $schema['@type']);
        $this->assertGreaterThanOrEqual(4, count($schema['mainEntity']));
        $this->assertLessThanOrEqual(6, count($schema['mainEntity']));

        foreach ($schema['mainEntity'] as $question) {
            $this->assertSame('Question', $question['@type']);
            $this->assertSame('Answer', $question['acceptedAnswer']['@type']);
            $response->assertSee('<h3>'.$question['name'].'</h3>', false);
            $response->assertSee($question['acceptedAnswer']['text'], false);
        }
    }

    public function test_guide_page_is_indexable_with_an_https_canonical_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://bagspar.se']);

        $response = $this->get(route('guides.documentation'));

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee(
            '<link rel="canonical" href="https://bagspar.se/fonsterrenovering-dokumentation">',
            false,
        );
    }

    private function mainContentWordCount(string $html): int
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        foreach ($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' guide-cta ')]") as $cta) {
            $cta->parentNode?->removeChild($cta);
        }

        $body = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' guide-page__body ')]")->item(0);
        $this->assertNotNull($body);

        $text = html_entity_decode($body->textContent ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return is_array($words) ? count($words) : 0;
    }
}
