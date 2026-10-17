<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\SashEvent;
use App\Models\SashMaterial;
use App\Models\SashPhoto;
use App\Models\SashTimeEntry;
use App\Models\WindowSash;
use Illuminate\Support\Carbon;

final class CustomerDataExport
{
    /**
     * @return array{
     *     kund: array{namn: string, kontaktperson: ?string, telefon: ?string, adress: ?string, epost: ?string},
     *     projekt: list<array<string, mixed>>
     * }
     */
    public function payload(Customer $customer): array
    {
        $projects = $customer->projects()
            ->with([
                'windowSashes.photos',
                'windowSashes.events',
                'windowSashes.timeEntries',
                'windowSashes.materials',
                'events',
            ])
            ->orderBy('id')
            ->get();

        return [
            'kund' => [
                'namn' => $customer->name,
                'kontaktperson' => $customer->contact_name,
                'telefon' => $customer->phone,
                'adress' => $customer->address,
                'epost' => $customer->email,
            ],
            'projekt' => $projects->map(function (Project $project): array {
                return [
                    'namn' => $project->name,
                    'plats' => $project->location,
                    'anteckningar' => $project->notes,
                    'status' => $project->status === 'completed' ? 'Avslutad' : 'Pågår',
                    'leveransdatum' => $project->delivery_date?->toDateString(),
                    'kundnamn' => $project->customer_name,
                    'bagar' => $project->windowSashes
                        ->sortBy('code')
                        ->values()
                        ->map(fn (WindowSash $sash): array => $this->sash($project, $sash))
                        ->all(),
                    'historik' => $project->events
                        ->sortBy('id')
                        ->values()
                        ->map(fn (ProjectEvent $event): array => $this->eventRow($event->created_at, $event->user_name, $event->kind, $event->body))
                        ->all(),
                ];
            })->all(),
        ];
    }

    public function json(Customer $customer): string
    {
        return json_encode(
            $this->payload($customer),
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        );
    }

    public function csv(Customer $customer): string
    {
        $payload = $this->payload($customer);
        $handle = fopen('php://temp', 'w+b');

        if ($handle === false) {
            return '';
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Typ', 'Kund', 'Projekt', 'Båge', 'Tid', 'Uppgift', 'Värde'], ';', '"', '\\');

        $customerName = $payload['kund']['namn'];

        foreach ([
            'Namn' => $payload['kund']['namn'],
            'Kontaktperson' => $payload['kund']['kontaktperson'],
            'Telefon' => $payload['kund']['telefon'],
            'Adress' => $payload['kund']['adress'],
            'E-post' => $payload['kund']['epost'],
        ] as $label => $value) {
            $this->put($handle, ['Kund', $customerName, '', '', '', $label, $this->text($value)]);
        }

        foreach ($payload['projekt'] as $project) {
            $projectName = $this->text($project['namn'] ?? null);

            foreach ([
                'Namn' => $project['namn'] ?? null,
                'Plats' => $project['plats'] ?? null,
                'Anteckningar' => $project['anteckningar'] ?? null,
                'Status' => $project['status'] ?? null,
                'Beräknad leverans' => $this->swedishDay(is_string($project['leveransdatum'] ?? null) ? $project['leveransdatum'] : null),
                'Kundnamn' => $project['kundnamn'] ?? null,
            ] as $label => $value) {
                $this->put($handle, ['Projekt', $customerName, $projectName, '', '', $label, $this->text($value)]);
            }

            foreach ($project['bagar'] as $sash) {
                $code = $this->text($sash['kod'] ?? null);

                foreach ([
                    'Rum' => $sash['rum'] ?? null,
                    'Placering' => $sash['placering'] ?? null,
                    'Etapp' => $sash['etapp'] ?? null,
                    'Anteckningar' => $sash['anteckningar'] ?? null,
                ] as $label => $value) {
                    $this->put($handle, ['Båge', $customerName, $projectName, $code, '', $label, $this->text($value)]);
                }

                foreach ($sash['historik'] as $event) {
                    $this->put($handle, [
                        'Historik',
                        $customerName,
                        $projectName,
                        $code,
                        $this->text($event['tid'] ?? null),
                        $this->text($event['anvandare'] ?? null),
                        $this->text($event['text'] ?? null),
                    ]);
                }

                foreach ($sash['bilder'] as $photo) {
                    $this->put($handle, [
                        'Bild',
                        $customerName,
                        $projectName,
                        $code,
                        '',
                        $this->text($photo['tillfalle'] ?? null),
                        $this->text($photo['url'] ?? null),
                    ]);
                }

                foreach ($sash['arbetstid'] as $entry) {
                    $minutes = is_numeric($entry['minuter'] ?? null) ? (int) $entry['minuter'] : 0;
                    $note = $this->text($entry['anteckning'] ?? null);
                    $duration = WorkDuration::label($minutes);

                    $this->put($handle, [
                        'Arbetstid',
                        $customerName,
                        $projectName,
                        $code,
                        $this->swedishDay(is_string($entry['datum'] ?? null) ? $entry['datum'] : null),
                        $this->text($entry['anvandare'] ?? null),
                        $note === '' ? $duration : $duration.'. '.$note,
                    ]);
                }

                foreach ($sash['material'] as $material) {
                    $this->put($handle, [
                        'Material',
                        $customerName,
                        $projectName,
                        $code,
                        '',
                        $this->text($material['typ'] ?? null),
                        trim($this->text($material['mangd'] ?? null).' '.$this->text($material['enhet'] ?? null).' '.$this->text($material['beskrivning'] ?? null)),
                    ]);
                }
            }

            foreach ($project['historik'] as $event) {
                $this->put($handle, [
                    'Historik',
                    $customerName,
                    $projectName,
                    '',
                    $this->text($event['tid'] ?? null),
                    $this->text($event['anvandare'] ?? null),
                    $this->text($event['text'] ?? null),
                ]);
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return is_string($csv) ? $csv : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function sash(Project $project, WindowSash $sash): array
    {
        return [
            'kod' => $sash->code,
            'rum' => $sash->room,
            'placering' => $sash->location,
            'etapp' => $sash->stageLabel(),
            'anteckningar' => $sash->notes,
            'historik' => $sash->events
                ->sortBy('id')
                ->values()
                ->map(function (SashEvent $event): array {
                    $text = is_string($event->body) && $event->body !== ''
                        ? $event->body
                        : ($event->kind === SashEvent::KIND_STAGE ? WindowSash::labelForStage($event->stage) : null);

                    return $this->eventRow($event->created_at, $event->user_name, $event->kind, $text);
                })
                ->all(),
            'arbetstid' => $sash->timeEntries
                ->sortBy('id')
                ->values()
                ->map(fn (SashTimeEntry $entry): array => [
                    'datum' => $entry->worked_on?->toDateString(),
                    'minuter' => $entry->minutes,
                    'anvandare' => $entry->user_name,
                    'anteckning' => $entry->note,
                ])
                ->all(),
            'material' => $sash->materials
                ->sortBy('id')
                ->values()
                ->map(fn (SashMaterial $material): array => [
                    'typ' => SashMaterial::labelForType($material->type),
                    'beskrivning' => $material->description,
                    'mangd' => SashMaterial::quantityLabel($material->quantity),
                    'enhet' => $material->unit,
                ])
                ->all(),
            'bilder' => $sash->photos
                ->sortBy('id')
                ->values()
                ->map(fn (SashPhoto $photo): array => [
                    'tillfalle' => $photo->documentMomentLabel() ?? 'Foto',
                    'filnamn' => $photo->original_filename,
                    'url' => route('app.sashes.photos.show', [$project, $sash, $photo]),
                ])
                ->all(),
        ];
    }

    /**
     * @return array{tid: ?string, anvandare: string, slag: string, etikett: string, text: ?string}
     */
    private function eventRow(mixed $createdAt, string $userName, string $kind, ?string $text): array
    {
        $timestamp = $createdAt instanceof Carbon
            ? SwedishDate::formatDateTime($createdAt)
            : null;
        $label = $this->eventLabel($kind);

        return [
            'tid' => $timestamp,
            'anvandare' => $userName,
            'slag' => $label,
            'etikett' => $label,
            'text' => $text,
        ];
    }

    private function eventLabel(string $kind): string
    {
        return match ($kind) {
            SashEvent::KIND_STAGE => 'etapp',
            SashEvent::KIND_NOTE => 'anteckning',
            SashEvent::KIND_PHOTO => 'bild',
            SashEvent::KIND_TIME => 'tid',
            SashEvent::KIND_MATERIAL => 'material',
            ProjectEvent::KIND_SASH_REMOVED => 'båge borttagen',
            default => $kind,
        };
    }

    /**
     * @param  resource  $handle
     * @param  list<string>  $row
     */
    private function put(mixed $handle, array $row): void
    {
        fputcsv($handle, array_map($this->cell(...), $row), ';', '"', '\\');
    }

    private function text(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return '';
    }

    private function swedishDay(?string $iso): string
    {
        if (! is_string($iso) || preg_match('/^\d{4}-\d{2}-\d{2}/', $iso) !== 1) {
            return '';
        }

        return SwedishDate::format(Carbon::parse(substr($iso, 0, 10)));
    }

    private function cell(string $value): string
    {
        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
