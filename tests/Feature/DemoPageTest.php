<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoPageTest extends TestCase
{
    public function test_demo_page_shows_an_example_work_card(): void
    {
        $response = $this->get(route('demo'));

        $response->assertOk();
        $response->assertSee('Exempel', false);
        $response->assertSee('Exempelverkstaden', false);
        $response->assertSee('Exempelvägen 12', false);
        $response->assertSee('2-KÖK-F1-B2', false);
        $response->assertSee('vänster innerbåge', false);
        $response->assertSeeInOrder([
            'Inlämnad',
            'Skrapning',
            'Kittning',
            'Målning',
            'Klar',
        ], false);
        $response->assertSee('demo-qr.svg', false);
        $response->assertSee('QR-kod', false);
        $response->assertSee('Illustrationer', false);
        $response->assertSee('job-photo__badge', false);
        $response->assertSee('Illustration, före. Inte ett fotografi.', false);
        $response->assertSee('inte fotografier', false);
        $response->assertSee('job-card--wide', false);
        $response->assertDontSee('>EXEMPEL<', false);
        $response->assertSee('>Stäng<', false);
        $response->assertSee('data-reset-confirm="Återställ exemplet? Ändringar du gjort på kortet tas bort."', false);
        $response->assertSee('Reservera pilotplats', false);
        $response->assertSee('demo-page__layout', false);
        $response->assertSee('data-sync-url="/demo/synk"', false);
        $response->assertSee('js/demo.js?v='.config('bagspar.asset_version'), false);
        $response->assertSee('data-sw-url="'.asset('sw.js').'?v='.config('bagspar.asset_version').'"', false);
        $response->assertSee('offline-först', false);
        $response->assertDontSee('B-04', false);
        $response->assertDontSee('Bilder, exempel', false);
        $response->assertDontSee('Före, exempel', false);
    }

    public function test_service_worker_precaches_the_demo_card(): void
    {
        $worker = file_get_contents(public_path('sw.js'));
        $siteScript = file_get_contents(public_path('js/site.js'));

        $this->assertIsString($worker);
        $this->assertIsString($siteScript);
        $this->assertStringContainsString("'/demo'", $worker);
        $this->assertStringContainsString("'/css/site.css'", $worker);
        $this->assertStringContainsString("'/js/demo.js'", $worker);
        $this->assertStringContainsString("'/images/demo-qr.svg'", $worker);
        $this->assertStringContainsString("document.querySelector('script[data-sw-url]')", $siteScript);
        $this->assertStringContainsString('serviceWorkerScript.dataset.swUrl', $siteScript);
        $this->assertStringNotContainsString("register('/sw.js')", $siteScript);
    }

    public function test_demo_changes_sync_and_ignore_unexpected_fields(): void
    {
        $demoId = (string) Str::uuid();

        $stored = $this->postJson(route('demo.sync.store'), [
            'demo_id' => $demoId,
            'step' => 'målning',
            'notes' => [[
                'id' => 'note-1',
                'text' => 'Kittets färg ska vara ljus, exempel.',
                'meta' => 'Exempel',
                'email' => 'secret@example.test',
            ]],
        ]);

        $stored->assertOk();
        $stored->assertJsonPath('card.step', 'målning');
        $stored->assertJsonPath('card.notes.0.text', 'Kittets färg ska vara ljus, exempel.');
        $this->assertArrayNotHasKey('email', $stored->json('card.notes.0'));

        $this->getJson(route('demo.sync.show', ['demo_id' => $demoId]))
            ->assertOk()
            ->assertJsonPath('card.step', 'målning')
            ->assertJsonPath('card.notes.0.id', 'note-1');

        $this->assertSame('målning', Cache::get('bagspar.demo-card.'.$demoId)['step']);
    }

    public function test_invalid_demo_step_is_rejected_without_saving(): void
    {
        $demoId = (string) Str::uuid();

        $this->postJson(route('demo.sync.store'), [
            'demo_id' => $demoId,
            'step' => 'levererad',
            'notes' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors(['step']);

        $this->assertNull(Cache::get('bagspar.demo-card.'.$demoId));
    }

    public function test_unknown_demo_card_has_no_saved_state(): void
    {
        $this->getJson(route('demo.sync.show', ['demo_id' => (string) Str::uuid()]))
            ->assertOk()
            ->assertJsonPath('card', null);
    }
}
