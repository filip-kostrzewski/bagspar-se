<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SashEvent;
use App\Models\SashTimeEntry;
use App\Models\User;
use App\Models\WindowSash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SashTimeEntryController extends Controller
{
    public function store(Request $request, Project $project, WindowSash $sash): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $actor = $this->actor($request);
        $validated = $this->validatedTime($request);

        DB::transaction(function () use ($actor, $sash, $validated): void {
            $entry = $sash->timeEntries()->create([
                'user_id' => $actor->getKey(),
                'user_name' => $actor->name,
                'worked_on' => $validated['worked_on'],
                'minutes' => $validated['minutes'],
                'note' => $validated['note'],
            ]);

            $sash->recordEvent($actor, SashEvent::KIND_TIME, null, 'Tid lades till: '.$entry->historyLabel().'.');
        });

        return back()->with('status', 'Tiden har sparats.');
    }

    public function update(Request $request, Project $project, WindowSash $sash, SashTimeEntry $entry): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $entry = $sash->timeEntries()->whereKey($entry->getKey())->firstOrFail();
        $actor = $this->actor($request);
        $this->authorizeTimeChange($actor, $entry);
        $validated = $this->validatedTime($request);
        $before = $entry->historyLabel();

        DB::transaction(function () use ($actor, $entry, $sash, $validated, $before): void {
            $entry->update([
                'worked_on' => $validated['worked_on'],
                'minutes' => $validated['minutes'],
                'note' => $validated['note'],
            ]);

            $sash->recordEvent($actor, SashEvent::KIND_TIME, null, 'Tid ändrades: '.$before.' blev '.$entry->historyLabel().'.');
        });

        return back()->with('status', 'Tiden har ändrats.');
    }

    public function destroy(Request $request, Project $project, WindowSash $sash, SashTimeEntry $entry): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $entry = $sash->timeEntries()->whereKey($entry->getKey())->firstOrFail();
        $actor = $this->actor($request);
        $this->authorizeTimeChange($actor, $entry);
        $label = $entry->historyLabel();

        DB::transaction(function () use ($actor, $entry, $label, $sash): void {
            $sash->recordEvent($actor, SashEvent::KIND_TIME, null, 'Tid togs bort: '.$label.'.');
            $entry->delete();
        });

        return back()->with('status', 'Tiden har tagits bort.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }

    private function authorizeTimeChange(User $actor, SashTimeEntry $entry): void
    {
        abort_unless($actor->isOwner() || $entry->user_id === $actor->getKey(), 403);
    }

    /**
     * @return array{worked_on: string, minutes: int, note: ?string}
     */
    private function validatedTime(Request $request): array
    {
        $validated = $request->validate([
            'worked_on' => ['required', 'date_format:Y-m-d'],
            'hours' => ['nullable', 'integer', 'min:0', 'max:99'],
            'minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'worked_on.required' => 'Ange datumet för tiden.',
            'worked_on.date_format' => 'Ange datumet som ett datum.',
            'hours.integer' => 'Ange timmarna som ett heltal.',
            'hours.min' => 'Timmarna kan inte vara negativa.',
            'hours.max' => 'Timmarna får vara högst 99.',
            'minutes.integer' => 'Ange minuterna som ett heltal.',
            'minutes.min' => 'Minuterna kan inte vara negativa.',
            'minutes.max' => 'Minuterna får vara högst 59.',
            'note.max' => 'Anteckningen får vara högst 500 tecken.',
        ]);

        $hours = (int) ($validated['hours'] ?? 0);
        $minutes = (int) ($validated['minutes'] ?? 0);
        $total = ($hours * 60) + $minutes;

        if ($total < 1) {
            throw ValidationException::withMessages([
                'hours' => 'Ange tiden i timmar eller minuter.',
            ]);
        }

        $note = $validated['note'] ?? null;

        return [
            'worked_on' => $validated['worked_on'],
            'minutes' => $total,
            'note' => is_string($note) && trim($note) !== '' ? trim($note) : null,
        ];
    }
}
