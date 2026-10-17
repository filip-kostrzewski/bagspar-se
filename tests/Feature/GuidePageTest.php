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
        'Arbetskort per fönsterbåge med plats, arbetssteg, anteckningar, bilder och en utskrivbar QR-etikett som öppnar kortet efter inloggning.',
        'Ett kort per båge',
        'guides.documentation',
        'dokumentationen av fönsterrenoveringen',
        'guides.marking',
        'märkningen av bågen',
    ])]
    #[TestWith([
        'guides.marking',
        'Märkning av fönsterbågar — Bågspår',
        'Märk fönsterbågen med ett nummer och skriv ut en QR-etikett från arbetskortet. Skanningen öppnar kortet efter inloggning.',
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
        $response->assertSee('Se exempel med testuppgifter', false);
        $response->assertSee('href="'.route('home').'#kontakt"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('tänkt', false);
        $response->assertDontSee('planerat', false);
        $response->assertDontSee('tanken är', false);

        if ($routeName === 'guides.documentation') {
            $response->assertSee('Mottagen betyder', false);
            $response->assertDontSee('Inlämnad betyder', false);
        }

        if (in_array($routeName, ['guides.work-card', 'guides.marking'], true)) {
            $response->assertSee('QR-etikett', false);
            $response->assertSee('Skanning kräver inloggning till verkstadskontot.', false);
            $response->assertDontSee('QR-koden skapas separat', false);
        }
    }

    public function test_work_card_guide_lists_all_six_workshop_stages_in_order(): void
    {
        $this->get(route('guides.work-card'))
            ->assertOk()
            ->assertSeeInOrder([
                '<h3>1. Mottagen</h3>',
                '<h3>2. Skrapning</h3>',
                '<h3>3. Trälagning</h3>',
                '<h3>4. Glasning</h3>',
                '<h3>5. Målning</h3>',
                '<h3>6. Klar</h3>',
            ], false);
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

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $schema = null;

        foreach ($matches[1] as $json) {
            $decoded = json_decode($json, true);

            if (is_array($decoded) && ($decoded['@type'] ?? null) === 'FAQPage') {
                $schema = $decoded;
                break;
            }
        }

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

    public function test_existing_guides_link_to_the_renovation_documentation_guide(): void
    {
        foreach (['guides.documentation', 'guides.work-card', 'guides.marking'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('href="'.route('guides.document-renovation').'"', false)
                ->assertSee('guiden om att dokumentera en fönsterrenovering', false);
        }
    }

    public function test_guide_index_lists_the_articles_and_exposes_article_metadata(): void
    {
        $response = $this->get(route('guides.index'));

        $response->assertOk();
        $response->assertSee('<title>Guider för fönsterverkstaden — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Praktiska guider om märkning, arbetskort, bilder före och efter och projektrapport för fönsterverkstäder.">', false);
        $response->assertSee('Guider för verkstaden', false);
        $response->assertSee('href="'.route('guides.document-renovation').'"', false);
        $response->assertSee('href="'.route('guides.documentation').'"', false);
        $response->assertSee('href="'.route('guides.work-card').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $this->assertArticleSchema($response->getContent(), 'Guider för verkstaden', route('guides.index'));
    }

    public function test_renovation_guide_explains_the_routine_without_promising_a_result(): void
    {
        $response = $this->get(route('guides.document-renovation'));
        $html = $response->getContent();

        $response->assertOk();
        $response->assertSee('<title>Dokumentera en fönsterrenovering — Bågspår</title>', false);
        $response->assertSee('Dokumentera fönsterrenoveringen', false);
        $response->assertSee('Bilder före och efter, per båge', false);
        $response->assertSee('Projektrapport till kunden eller föreningen', false);
        $response->assertSee('Mottagen, Skrapning, Trälagning, Glasning, Målning och Klar', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $response->assertSee('href="'.route('guides.work-card').'"', false);
        $response->assertSee('href="'.route('guides.documentation').'"', false);
        $response->assertSee('I Bågspår kan rapporten laddas ner som PDF eller skrivas ut.', false);
        $response->assertDontSee('i webbläsaren', false);
        $response->assertDontSee('ROT-underlag', false);
        $response->assertSee('Den lovar inte ett resultat', false);
        $response->assertDontSee('tänkt', false);
        $response->assertDontSee('planerat', false);
        $response->assertDontSee('tanken är', false);

        $wordCount = $this->mainContentWordCount($html);
        $this->assertGreaterThanOrEqual(900, $wordCount, 'renovation guide has '.$wordCount.' words');
        $this->assertLessThanOrEqual(1200, $wordCount, 'renovation guide has '.$wordCount.' words');
        $this->assertArticleSchema($html, 'Dokumentera fönsterrenoveringen', route('guides.document-renovation'));
    }

    public function test_new_guide_pages_are_indexable_with_an_https_canonical_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://bagspar.se']);

        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider">', false);

        $this->get(route('guides.document-renovation'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/dokumentera-fonsterrenovering">', false);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/villkor">', false);
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

    private function assertArticleSchema(string $html, string $headline, string $pageUrl): void
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $schema = null;

        foreach ($matches[1] as $json) {
            $decoded = json_decode($json, true);

            if (is_array($decoded) && ($decoded['@type'] ?? null) === 'Article') {
                $schema = $decoded;
                break;
            }
        }

        $this->assertIsArray($schema);
        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertSame('Article', $schema['@type']);
        $this->assertSame($headline, $schema['headline']);
        $this->assertSame('sv', $schema['inLanguage']);
        $this->assertSame('2026-10-17', $schema['datePublished']);
        $this->assertSame('2026-10-17', $schema['dateModified']);
        $this->assertSame($pageUrl, $schema['mainEntityOfPage']['@id']);
        $this->assertSame('Prodi Digital AB', $schema['author']['name']);
        $this->assertSame('Organization', $schema['publisher']['@type']);
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
