<?php

namespace Tests\Feature;

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
}
