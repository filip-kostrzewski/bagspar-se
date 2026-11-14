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

    public function test_brf_report_guide_is_published_and_listed_in_the_sitemap(): void
    {
        $response = $this->get('/guider/rapport-till-brf-fonsterrenovering');

        $response->assertOk();
        $response->assertSee('<title>Rapport till bostadsrättsföreningen efter fönsterrenovering — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Så sammanställer fönsterverkstaden underlaget till en bostadsrättsförening: en rad per båge, bilder före och efter, steg och anteckningar, samlat i en projektrapport som PDF.">', false);
        $response->assertSee('<h1 class="display-heading">Rapport till bostadsrättsföreningen efter fönsterrenovering</h1>', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $response->assertSee('href="'.route('guides.document-renovation').'"', false);
        $response->assertSee('href="'.route('guides.work-card').'"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('frazy', false);
        $response->assertDontSee('fakty', false);
        $response->assertDontSee('Kiko', false);

        $html = $response->getContent();
        $this->assertGreaterThan(0, $this->mainContentWordCount($html));
        $this->assertArticleSchema(
            $html,
            'Rapport till bostadsrättsföreningen efter fönsterrenovering',
            route('guides.brf-report'),
            '2026-10-27',
        );

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/guider/rapport-till-brf-fonsterrenovering</loc>', false);
    }

    public function test_brf_report_guide_is_linked_from_the_index_and_the_renovation_guide(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('href="'.route('guides.brf-report').'"', false)
            ->assertSee('Rapport till bostadsrättsföreningen', false);

        $this->get(route('guides.document-renovation'))
            ->assertOk()
            ->assertSeeInOrder([
                'Projektrapport till kunden eller föreningen',
                'href="'.route('guides.brf-report').'"',
                'Vanliga misstag',
            ], false);
    }

    public function test_intake_protocol_guide_is_published_and_listed_in_the_sitemap(): void
    {
        $response = $this->get('/guider/mottagningsprotokoll-fonsterbagar');

        $response->assertOk();
        $response->assertSee('<title>Mottagningsprotokoll för fönsterbågar — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Så dokumenterar fönsterverkstaden skicket på varje båge när den hämtas: anmärkning och bild före per båge, och kundens godkännande via länk eller på plats, samlat i ett protokoll som PDF.">', false);
        $response->assertSee('<h1 class="display-heading">Mottagningsprotokoll för fönsterbågar</h1>', false);
        $response->assertSee('MOTTAGNINGSPROTOKOLL', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $response->assertSee('href="'.route('guides.document-renovation').'"', false);
        $response->assertSee('href="'.route('guides.brf-report').'"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('frazy', false);
        $response->assertDontSee('fakty', false);
        $response->assertDontSee('Kiko', false);

        $html = $response->getContent();
        $this->assertGreaterThan(0, $this->mainContentWordCount($html));
        $this->assertArticleSchema(
            $html,
            'Mottagningsprotokoll för fönsterbågar',
            route('guides.intake-protocol'),
            '2026-10-31',
        );

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/guider/mottagningsprotokoll-fonsterbagar</loc>', false);
    }

    public function test_intake_protocol_guide_is_linked_from_the_index_and_the_brf_report_guide(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('href="'.route('guides.intake-protocol').'"', false)
            ->assertSee('Mottagningsprotokoll', false);

        $this->get(route('guides.brf-report'))
            ->assertOk()
            ->assertSeeInOrder([
                'Bilder före och efter, på rätt båge',
                'href="'.route('guides.intake-protocol').'"',
                'Steg och anteckningar som går att lita på',
            ], false);
    }

    public function test_rot_guide_is_published_and_listed_in_the_sitemap(): void
    {
        $response = $this->get('/guider/rotavdrag-fonsterrenovering');

        $response->assertOk();
        $response->assertSee('<title>Rotavdrag vid fönsterrenovering — det verkstaden behöver hålla isär — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Vilken del av en fönsterrenovering som ger rotavdrag enligt Skatteverket, varför tiden i verkstaden inte räknas, och hur ni håller isär tid på plats, tid i verkstaden och material per båge.">', false);
        $response->assertSee('<h1 class="display-heading">Rotavdrag vid fönsterrenovering</h1>', false);
        $response->assertSee('ROTAVDRAG', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.brf-report').'"', false);
        $response->assertSee('href="'.route('guides.intake-protocol').'"', false);
        $response->assertSee('href="https://www.skatteverket.se/foretag/skatterochavdrag/rotochrut/safungerarrotavdraget.4.2ef18e6a125660db8b080002709.html" rel="noopener"', false);
        $response->assertSee('href="https://www.skatteverket.se/foretag/skatterochavdrag/rotochrut/gerarbetetratttillrotavdrag.4.5c1163881590be297b5173bf.html" rel="noopener"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('frazy', false);
        $response->assertDontSee('fakty', false);
        $response->assertDontSee('Kiko', false);

        $html = $response->getContent();
        $this->assertGreaterThan(0, $this->mainContentWordCount($html));
        $this->assertArticleSchema(
            $html,
            'Rotavdrag vid fönsterrenovering',
            route('guides.rot'),
            '2026-11-03',
        );

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/guider/rotavdrag-fonsterrenovering</loc>', false);
    }

    public function test_rot_guide_is_linked_from_the_index_the_intake_protocol_guide_and_the_brf_report_guide(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('href="'.route('guides.rot').'"', false)
            ->assertSee('Rotavdrag', false);

        $this->get(route('guides.intake-protocol'))
            ->assertOk()
            ->assertSeeInOrder([
                'Mottagningsprotokollet i Bågspår',
                'href="'.route('guides.rot').'"',
                'Checklista vid mottagning',
            ], false);

        $this->get(route('guides.brf-report'))
            ->assertOk()
            ->assertSeeInOrder([
                'Projektrapporten',
                'href="'.route('guides.rot').'"',
                'Checklista innan rapporten lämnas',
            ], false);
    }

    public function test_quote_guide_is_published_and_listed_in_the_sitemap(): void
    {
        $response = $this->get('/guider/offert-fonsterrenovering');

        $response->assertOk();
        $response->assertSee('<title>Offert för fönsterrenovering — så skriver verkstaden den per båge — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Så bygger fönsterverkstaden en offert för renovering av fönsterbågar: en rad per båge, tydligt pris före rotavdrag, giltighetstid och kundens godkännande med namn via länk.">', false);
        $response->assertSee('<h1 class="display-heading">Offert för fönsterrenovering</h1>', false);
        $response->assertSee('OFFERT', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.rot').'"', false);
        $response->assertSee('href="'.route('guides.intake-protocol').'"', false);
        $response->assertSee('href="https://www.skatteverket.se/foretag/skatterochavdrag/rotochrut/safungerarrotavdraget.4.2ef18e6a125660db8b080002709.html" rel="noopener"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('frazy', false);
        $response->assertDontSee('fakty', false);
        $response->assertDontSee('Kiko', false);

        $html = $response->getContent();
        $this->assertGreaterThan(0, $this->mainContentWordCount($html));
        $this->assertArticleSchema(
            $html,
            'Offert för fönsterrenovering',
            route('guides.quote'),
            '2026-11-06',
        );

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/guider/offert-fonsterrenovering</loc>', false);
    }

    public function test_quote_guide_is_linked_from_the_index_the_rot_guide_and_the_intake_protocol_guide(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('href="'.route('guides.quote').'"', false)
            ->assertSee('Offert', false);

        $this->get(route('guides.rot'))
            ->assertOk()
            ->assertSeeInOrder([
                'Det som ska stå isär på fakturan',
                'href="'.route('guides.quote').'"',
                'Skriv tiden per båge och plats samma dag',
            ], false);

        $this->get(route('guides.intake-protocol'))
            ->assertOk()
            ->assertSeeInOrder([
                'Mottagningsprotokollet i Bågspår',
                'href="'.route('guides.quote').'"',
                'Checklista vid mottagning',
            ], false);
    }

    public function test_heritage_guide_is_published_and_listed_in_the_sitemap(): void
    {
        $response = $this->get('/guider/fonsterrenovering-kulturhistorisk-byggnad');

        $response->assertOk();
        $response->assertSee('<title>Fönsterrenovering i kulturhistoriskt värdefulla hus — varsamhet och dokumentation — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Vad varsamhetskravet och förvanskningsförbudet i PBL betyder när fönsterverkstaden renoverar bågar i ett äldre eller kulturhistoriskt värdefullt hus, och hur arbetet dokumenteras per båge.">', false);
        $response->assertSee('<h1 class="display-heading">Fönsterrenovering i kulturhistoriskt värdefulla hus</h1>', false);
        $response->assertSee('KULTURVÄRDEN', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.intake-protocol').'"', false);
        $response->assertSee('href="'.route('guides.document-renovation').'"', false);
        $response->assertSee('href="'.route('guides.brf-report').'"', false);
        $response->assertSee('href="https://www.boverket.se/sv/PBL-kunskapsbanken/teman/kulturvarden/kulturvarden-i-plan---och-bygglagen/krav-pa-byggnadsverk-och-tomter/varsamhetskravet/" rel="noopener"', false);
        $response->assertSee('href="https://www.boverket.se/sv/PBL-kunskapsbanken/teman/kulturvarden/kulturvarden-i-plan---och-bygglagen/krav-pa-byggnadsverk-och-tomter/forvanskningsforbudet/" rel="noopener"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('frazy', false);
        $response->assertDontSee('fakty', false);
        $response->assertDontSee('Kiko', false);

        $html = $response->getContent();
        $this->assertGreaterThan(0, $this->mainContentWordCount($html));
        $this->assertArticleSchema(
            $html,
            'Fönsterrenovering i kulturhistoriskt värdefulla hus',
            route('guides.heritage'),
            '2026-11-09',
        );

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/guider/fonsterrenovering-kulturhistorisk-byggnad</loc>', false);
    }

    public function test_heritage_guide_is_linked_from_the_index_the_document_renovation_guide_and_the_brf_guide(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('href="'.route('guides.heritage').'"', false)
            ->assertSee('Kulturvärden', false);

        $this->get(route('guides.document-renovation'))
            ->assertOk()
            ->assertSeeInOrder([
                'Projektrapport till kunden eller föreningen',
                'href="'.route('guides.heritage').'"',
                'Vanliga misstag',
            ], false);

        $this->get(route('guides.brf-report'))
            ->assertOk()
            ->assertSeeInOrder([
                'Projektrapporten',
                'href="'.route('guides.heritage').'"',
                'Checklista innan rapporten lämnas',
            ], false);
    }

    public function test_costing_guide_is_published_and_listed_in_the_sitemap(): void
    {
        $response = $this->get('/guider/efterkalkyl-fonsterrenovering');

        $response->assertOk();
        $response->assertSee('<title>Efterkalkyl för fönsterrenovering — tid och material per båge — Bågspår</title>', false);
        $response->assertSee('<meta name="description" content="Så gör fönsterverkstaden en efterkalkyl: skriv tid och material på varje båge medan arbetet pågår, jämför med offerten och använd siffrorna när nästa offert ska räknas.">', false);
        $response->assertSee('<h1 class="display-heading">Efterkalkyl för fönsterrenovering</h1>', false);
        $response->assertSee('EFTERKALKYL', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.quote').'"', false);
        $response->assertSee('href="'.route('guides.rot').'"', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('href="'.route('contact').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertDontSee('frazy', false);
        $response->assertDontSee('fakty', false);
        $response->assertDontSee('Kiko', false);

        $html = $response->getContent();
        $this->assertGreaterThan(0, $this->mainContentWordCount($html));
        $this->assertArticleSchema(
            $html,
            'Efterkalkyl för fönsterrenovering',
            route('guides.costing'),
            '2026-11-14',
        );

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/guider/efterkalkyl-fonsterrenovering</loc>', false);
    }

    public function test_costing_guide_is_linked_from_the_index_the_quote_guide_and_the_document_renovation_guide(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('href="'.route('guides.costing').'"', false)
            ->assertSee('Efterkalkyl', false);

        $this->get(route('guides.quote'))
            ->assertOk()
            ->assertSeeInOrder([
                'En rad per båge',
                'href="'.route('guides.costing').'"',
                'Rotavdraget hör till fakturan',
            ], false);

        $this->get(route('guides.document-renovation'))
            ->assertOk()
            ->assertSeeInOrder([
                'Projektrapport till kunden eller föreningen',
                'href="'.route('guides.costing').'"',
                'Vanliga misstag',
            ], false);
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

        $this->get(route('guides.brf-report'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/rapport-till-brf-fonsterrenovering">', false);

        $this->get(route('guides.intake-protocol'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/mottagningsprotokoll-fonsterbagar">', false);

        $this->get(route('guides.rot'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/rotavdrag-fonsterrenovering">', false);

        $this->get(route('guides.quote'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/offert-fonsterrenovering">', false);

        $this->get(route('guides.heritage'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/fonsterrenovering-kulturhistorisk-byggnad">', false);

        $this->get(route('guides.costing'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://bagspar.se/guider/efterkalkyl-fonsterrenovering">', false);

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

    private function assertArticleSchema(string $html, string $headline, string $pageUrl, string $publishedOn = '2026-10-17'): void
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
        $this->assertSame($publishedOn, $schema['datePublished']);
        $this->assertSame($publishedOn, $schema['dateModified']);
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
