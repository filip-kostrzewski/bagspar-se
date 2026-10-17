<?php

namespace Tests\Feature\Workshop;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\EmbeddedImage;
use App\Support\ProjectReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_report_downloads_as_a_private_pdf_with_embedded_photos(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'notes' => 'HEMLIGANTECKNING',
        ]);
        $sash = WindowSash::factory()->create([
            'project_id' => $project->getKey(),
            'notes' => 'kittanteckning',
        ]);

        $this->actingAs($owner);
        $withoutNotes = $this->get(route('app.projects.report.pdf', $project));
        $withoutNotes->assertOk();
        $withoutNotes->assertHeader('Content-Type', 'application/pdf');
        $withoutNotes->assertHeader('Content-Disposition', 'attachment; filename="villa-ek-rapport.pdf"');
        $withoutNotes->assertHeader('X-Content-Type-Options', 'nosniff');
        $cache = (string) $withoutNotes->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cache);
        $this->assertStringContainsString('no-store', $cache);

        $plain = $withoutNotes->getContent();
        $this->assertIsString($plain);
        $this->assertStringStartsWith('%PDF', $plain);
        $this->assertStringNotContainsString('http://', $plain);
        $this->assertStringNotContainsString('https://', $plain);
        $this->assertStringNotContainsString("\xFF\xD8\xFF", $plain);

        $withNotes = $this->get(route('app.projects.report.pdf', [
            'project' => $project,
            'anteckningar' => 1,
        ]))->getContent();
        $withTime = $this->get(route('app.projects.report.pdf', [
            'project' => $project,
            'arbetstid' => 1,
        ]))->getContent();
        $this->assertIsString($withNotes);
        $this->assertIsString($withTime);
        $this->assertNotSame($plain, $withNotes);
        $this->assertNotSame($plain, $withTime);

        $this->post(route('app.sashes.photos.store', [$project, $sash]), [
            'photo' => UploadedFile::fake()->image('fore.jpg', 40, 30),
            'moment' => 'before',
        ])->assertRedirect();

        $withPhoto = $this->get(route('app.projects.report.pdf', $project))->getContent();
        $this->assertIsString($withPhoto);
        $this->assertStringStartsWith('%PDF', $withPhoto);
        $this->assertStringContainsString("\xFF\xD8\xFF", $withPhoto);
        $this->assertStringNotContainsString('http://', $withPhoto);
        $this->assertStringNotContainsString('https://', $withPhoto);
        $this->assertNull(EmbeddedImage::jpegDataUri('../secrets.webp', 'sash-photos/'));
        $this->assertNull(EmbeddedImage::jpegDataUri('workshop-logos/1/logo.webp', 'sash-photos/'));
    }

    public function test_another_workshop_cannot_download_the_report(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);

        $this->get(route('app.projects.report.pdf', $project))
            ->assertRedirect(route('login'));

        $this->actingAs($other)
            ->get(route('app.projects.report.pdf', $project))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('app.projects.report.pdf', $project))
            ->assertOk();
    }

    public function test_the_pdf_markup_includes_the_customer_phone_and_the_status_qr(): void
    {
        $owner = User::factory()->create();
        $owner->workshop->update(['phone' => '+4681234567']);
        $customer = Customer::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'phone' => '070-111 22 33',
        ]);
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'customer_id' => $customer->getKey(),
        ]);
        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('d', 48),
        ])->save();

        $html = view('app.projects.report-pdf', [
            ...ProjectReport::data($owner->workshop, $project->fresh(), false, false),
            'photoEmbeds' => [],
            'logoEmbed' => null,
        ])->render();

        $this->assertStringContainsString('070-111 22 33', $html);
        $this->assertStringContainsString('08-123 45 67', $html);
        $this->assertStringContainsString('Skanna koden', $html);
        $this->assertStringContainsString('page-break-inside: avoid', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('alt="QR-kod till kundens status"', $html);
        $this->assertMatchesRegularExpression(
            '/<table class="keep-together"[\s\S]*Status för kunden[\s\S]*Skanna koden[\s\S]*data:image\/png;base64,[\s\S]*'.preg_quote(route('client-status.show', ['token' => str_repeat('d', 48)]), '/').'[\s\S]*<\/table>/',
            $html,
        );
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $this->pngFromDataUri($html));

        $project->forceFill(['client_status_enabled' => false])->save();

        $withoutLink = view('app.projects.report-pdf', [
            ...ProjectReport::data($owner->workshop, $project->fresh(), false, false),
            'photoEmbeds' => [],
            'logoEmbed' => null,
        ])->render();

        $this->assertStringContainsString('070-111 22 33', $withoutLink);
        $this->assertStringContainsString('08-123 45 67', $withoutLink);
        $this->assertStringNotContainsString('Skanna koden', $withoutLink);
        $this->assertStringNotContainsString('data:image/png;base64,', $withoutLink);
    }

    public function test_the_downloaded_pdf_contains_the_qr_image_when_the_status_link_is_enabled(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
        ]);
        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('d', 48),
        ])->save();

        $this->actingAs($owner);
        $withQr = $this->get(route('app.projects.report.pdf', $project))->assertOk()->getContent();
        $this->assertIsString($withQr);
        $this->assertStringStartsWith('%PDF', $withQr);
        $this->assertSame(1, preg_match_all('/\/Subtype\s*\/Image/', $withQr));
        $this->assertMatchesRegularExpression('/\/Width\s+320/', $withQr);
        $this->assertMatchesRegularExpression('/\/Height\s+320/', $withQr);

        $project->forceFill(['client_status_enabled' => false])->save();
        $withoutQr = $this->get(route('app.projects.report.pdf', $project))->assertOk()->getContent();
        $this->assertIsString($withoutQr);
        $this->assertStringStartsWith('%PDF', $withoutQr);
        $this->assertSame(0, preg_match_all('/\/Subtype\s*\/Image/', $withoutQr));
        $this->assertGreaterThan(strlen($withoutQr), strlen($withQr));
    }

    public function test_a_long_report_keeps_the_status_qr_block_on_one_page(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'workshop_id' => $owner->workshop_id,
            'name' => 'Villa Ek',
            'notes' => str_repeat('Anteckning om bågen. ', 40),
        ]);
        $project->forceFill([
            'client_status_enabled' => true,
            'client_status_token' => str_repeat('d', 48),
        ])->save();

        foreach (range(1, 8) as $number) {
            WindowSash::factory()->create([
                'project_id' => $project->getKey(),
                'code' => 'B-'.$number,
                'stage' => 'repair',
                'notes' => str_repeat('Kitt och färg kontrolleras på den här bågen. ', 30),
            ]);
        }

        $this->actingAs($owner);
        $pdf = $this->get(route('app.projects.report.pdf', [
            'project' => $project,
            'anteckningar' => 1,
        ]))->assertOk()->getContent();
        $this->assertIsString($pdf);
        $this->assertGreaterThan(1, preg_match_all('/\/Type\s*\/Page(?!s)/', $pdf));

        $statusPage = null;
        $openingPage = null;

        foreach ($this->pdfContentStreams($pdf) as $stream) {
            if ($this->pdfStreamContains($stream, 'Projektrapport')) {
                $openingPage = $stream;
            }

            if ($this->pdfStreamContains($stream, 'Status för kunden')) {
                $statusPage = $stream;
            }
        }

        $this->assertIsString($openingPage);
        $this->assertIsString($statusPage);
        $this->assertNotSame($openingPage, $statusPage);
        $this->assertTrue($this->pdfStreamContains($statusPage, 'Skanna koden'));
        $this->assertStringContainsString(' Do', $statusPage);
        $this->assertStringNotContainsString(' Do', $openingPage);
    }

    /**
     * @return list<string>
     */
    private function pdfContentStreams(string $pdf): array
    {
        preg_match_all('/<<[^>]*\/Length\s+(\d+)[^>]*>>\s*stream\r?\n/s', $pdf, $matches, PREG_OFFSET_CAPTURE);
        $streams = [];

        foreach ($matches[0] as $index => $match) {
            $decoded = @gzuncompress(substr($pdf, $match[1] + strlen($match[0]), (int) $matches[1][$index][0]));

            if (is_string($decoded)) {
                $streams[] = $decoded;
            }
        }

        return $streams;
    }

    private function pdfStreamContains(string $stream, string $text): bool
    {
        $encoded = mb_convert_encoding($text, 'UTF-16BE', 'UTF-8');

        return is_string($encoded) && str_contains($stream, $encoded);
    }

    private function pngFromDataUri(string $html): string
    {
        $this->assertSame(1, preg_match('/src="data:image\/png;base64,([^"]+)"/', $html, $matches));
        $png = base64_decode($matches[1], true);
        $this->assertIsString($png);

        return $png;
    }
}
