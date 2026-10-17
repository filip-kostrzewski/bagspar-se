<?php

namespace App\Support;

use App\Models\Project;
use App\Models\SashEvent;
use App\Models\User;
use App\Models\WindowSash;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;

final class SampleProject
{
    public const NAME_PREFIX = 'Exempelprojekt';

    public const NAME = 'Exempelprojekt – Villa Björk';

    public const NOTES = 'Det här är ett exempel som visar hur ett projekt ser ut. Ta bort det när du vill.';

    private const ADDRESS = 'Björkvägen 4, 756 52 Uppsala';

    /**
     * @var list<array{code: string, room: string, location: string, stage: string, notes: string, time?: list<array{days_ago: int, minutes: int, note: string}>, materials?: list<array{type: string, description: string, quantity: string, unit: string, unit_price_ore: int}>}>
     */
    private const SASHES = [
        [
            'code' => '2-KÖK-F1-B1',
            'room' => 'Kök',
            'location' => 'Vänster ytterbåge',
            'stage' => 'ready',
            'notes' => 'Två rutor spruckna',
            'time' => [
                ['days_ago' => 3, 'minutes' => 90, 'note' => 'Skrapning'],
                ['days_ago' => 2, 'minutes' => 120, 'note' => 'Trälagning'],
            ],
            'materials' => [
                ['type' => 'kitt', 'description' => 'Linoljekitt', 'quantity' => '1', 'unit' => 'kg', 'unit_price_ore' => 16500],
                ['type' => 'glas', 'description' => 'Fönsterglas 3 mm', 'quantity' => '2', 'unit' => 'st', 'unit_price_ore' => 32000],
            ],
        ],
        [
            'code' => '2-KÖK-F1-B2',
            'room' => 'Kök',
            'location' => 'Höger ytterbåge',
            'stage' => 'glazing',
            'notes' => 'Kittfals urkratsad, redo för glasning',
        ],
        [
            'code' => '2-VRD-F1-B1',
            'room' => 'Vardagsrum',
            'location' => 'Vänster ytterbåge',
            'stage' => 'painting',
            'notes' => 'Grundmålad, andra strykningen kvar',
        ],
        [
            'code' => '2-VRD-F2-B1',
            'room' => 'Vardagsrum',
            'location' => 'Vänster innerbåge',
            'stage' => 'repair',
            'notes' => 'Rötskada nedre hörn, ca 8 cm',
            'time' => [
                ['days_ago' => 1, 'minutes' => 60, 'note' => 'Skrapning'],
            ],
            'materials' => [
                ['type' => 'övrigt', 'description' => 'Träfyllnadsmassa', 'quantity' => '0.5', 'unit' => 'kg', 'unit_price_ore' => 24000],
            ],
        ],
        [
            'code' => '2-SOV-F1-B1',
            'room' => 'Sovrum',
            'location' => 'Vänster ytterbåge',
            'stage' => 'scraping',
            'notes' => 'Gammal färg flagnar, skrapas ner till trät',
        ],
        [
            'code' => '2-SOV-F1-B2',
            'room' => 'Sovrum',
            'location' => 'Höger ytterbåge',
            'stage' => 'received',
            'notes' => 'Ett beslag saknas, kittet är sprött',
        ],
    ];

    public static function createFor(Workshop $workshop, User $actor): Project
    {
        return DB::transaction(function () use ($workshop, $actor): Project {
            $customer = $workshop->customers()->create([
                'name' => 'Exempelkund: Familjen Björk',
                'contact_name' => 'Anna Björk',
                'address' => self::ADDRESS,
            ]);

            $project = $workshop->projects()->create([
                'customer_id' => $customer->getKey(),
                'name' => self::NAME,
                'customer_name' => $customer->name,
                'location' => self::ADDRESS,
                'notes' => self::NOTES,
                'delivery_date' => now()->addWeeks(3)->toDateString(),
            ]);
            $project->issueClientStatusToken();
            $project->client_status_enabled = true;
            $project->save();

            foreach (self::SASHES as $definition) {
                self::createSash($project, $actor, $definition);
            }

            return $project;
        });
    }

    /**
     * @param  array{code: string, room: string, location: string, stage: string, notes: string, time?: list<array{days_ago: int, minutes: int, note: string}>, materials?: list<array{type: string, description: string, quantity: string, unit: string, unit_price_ore: int}>}  $definition
     */
    private static function createSash(Project $project, User $actor, array $definition): void
    {
        $sash = $project->windowSashes()->create([
            'code' => $definition['code'],
            'room' => $definition['room'],
            'location' => $definition['location'],
            'stage' => 'received',
            'notes' => $definition['notes'],
        ]);

        $sash->recordEvent($actor, SashEvent::KIND_STAGE, 'received');
        $sash->recordEvent($actor, SashEvent::KIND_NOTE, null, $definition['notes']);

        $finalIndex = array_search($definition['stage'], WindowSash::STAGES, true);

        foreach (array_slice(WindowSash::STAGES, 1, (int) $finalIndex) as $stage) {
            $sash->update(['stage' => $stage]);
            $sash->recordEvent($actor, SashEvent::KIND_STAGE, $stage);
        }

        foreach ($definition['time'] ?? [] as $time) {
            $entry = $sash->timeEntries()->create([
                'user_id' => $actor->getKey(),
                'user_name' => $actor->name,
                'worked_on' => now()->subDays($time['days_ago'])->toDateString(),
                'minutes' => $time['minutes'],
                'note' => $time['note'],
            ]);

            $sash->recordEvent($actor, SashEvent::KIND_TIME, null, 'Tid lades till: '.$entry->historyLabel().'.');
        }

        foreach ($definition['materials'] ?? [] as $row) {
            $material = $sash->materials()->create($row);

            $sash->recordEvent($actor, SashEvent::KIND_MATERIAL, null, 'Material lades till: '.$material->historyLabel().'.');
        }
    }
}
