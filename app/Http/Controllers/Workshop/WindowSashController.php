<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\SashEvent;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\ReadyCustomerNotifier;
use App\Support\StoredSashPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WindowSashController extends Controller
{
    public function create(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);

        return view('app.sashes.create', compact('workshop', 'project'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:120', Rule::unique('window_sashes', 'code')->where('project_id', $project->getKey())],
            'room' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:160'],
            'stage' => ['sometimes', 'required', Rule::in(WindowSash::STAGES)],
            'notes' => ['nullable', 'string', 'max:3000'],
        ], [
            'code.required' => 'Ange en kod för bågen.',
            'code.string' => 'Ange bågens kod som text.',
            'code.max' => 'Koden får vara högst 120 tecken.',
            'code.unique' => 'Koden används redan i det här projektet.',
            'room.string' => 'Ange rummet som text.',
            'room.max' => 'Rummet får vara högst 120 tecken.',
            'location.string' => 'Ange placeringen som text.',
            'location.max' => 'Placeringen får vara högst 160 tecken.',
            'stage.in' => 'Välj ett giltigt arbetssteg.',
            'notes.string' => 'Ange anteckningarna som text.',
            'notes.max' => 'Anteckningarna får vara högst 3 000 tecken.',
        ]);

        $sash = $project->windowSashes()->create($validated + ['stage' => 'received']);
        $actor = $request->user();

        if ($actor instanceof User) {
            $sash->recordEvent($actor, SashEvent::KIND_STAGE, $sash->stage);

            if (is_string($sash->notes) && $sash->notes !== '') {
                $sash->recordEvent($actor, SashEvent::KIND_NOTE, null, $sash->notes);
            }
        }

        app(ReadyCustomerNotifier::class)->sync($project);

        return redirect()->route('app.sashes.show', [$project, $sash])->with('status', 'Sparat');
    }

    public function show(Request $request, Project $project, WindowSash $sash): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $photos = $sash->photos()->inDocumentOrder()->get();
        $events = $sash->events()->orderBy('id')->get();
        $timeEntries = $sash->timeEntries()->orderBy('worked_on')->orderBy('id')->get();
        $materials = $sash->materials()->orderBy('id')->get();

        return view('app.sashes.show', compact('workshop', 'project', 'sash', 'photos', 'events', 'timeEntries', 'materials'));
    }

    public function update(Request $request, Project $project, WindowSash $sash): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $validated = $request->validate([
            'code' => ['sometimes', 'required', 'string', 'max:120', Rule::unique('window_sashes', 'code')
                ->where('project_id', $project->getKey())
                ->ignore($sash->getKey())],
            'room' => ['sometimes', 'nullable', 'string', 'max:120'],
            'location' => ['sometimes', 'nullable', 'string', 'max:160'],
            'stage' => ['sometimes', 'required', Rule::in(WindowSash::STAGES)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:3000'],
        ], [
            'code.required' => 'Ange en kod för bågen.',
            'code.string' => 'Ange bågens kod som text.',
            'code.max' => 'Koden får vara högst 120 tecken.',
            'code.unique' => 'Koden används redan i det här projektet.',
            'room.string' => 'Ange rummet som text.',
            'room.max' => 'Rummet får vara högst 120 tecken.',
            'location.string' => 'Ange placeringen som text.',
            'location.max' => 'Placeringen får vara högst 160 tecken.',
            'stage.in' => 'Välj ett giltigt arbetssteg.',
            'notes.string' => 'Ange anteckningarna som text.',
            'notes.max' => 'Anteckningarna får vara högst 3 000 tecken.',
        ]);

        abort_if($validated === [], 422);
        $previousStage = $sash->stage;
        $previousNotes = $sash->notes;
        $sash->update($validated);
        $actor = $request->user();

        if ($actor instanceof User) {
            if (array_key_exists('stage', $validated) && $sash->stage !== $previousStage) {
                $sash->recordEvent($actor, SashEvent::KIND_STAGE, $sash->stage);
            }

            if (array_key_exists('notes', $validated) && $sash->notes !== $previousNotes) {
                $sash->recordEvent($actor, SashEvent::KIND_NOTE, null, $sash->notes);
            }
        }

        $sent = app(ReadyCustomerNotifier::class)->sync($project);
        $message = $sent
            ? 'Sparat. Kunden har fått ett mejl om att alla bågar är klara.'
            : 'Sparat';

        return back()->with('status', $message);
    }

    public function destroy(Request $request, Project $project, WindowSash $sash): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        Gate::authorize('delete', $project);

        $paths = $sash->photos()->pluck('path');
        $actor = $request->user();
        $code = $sash->code;

        DB::transaction(function () use ($actor, $project, $sash, $code): void {
            if ($actor instanceof User) {
                $project->recordEvent($actor, ProjectEvent::KIND_SASH_REMOVED, 'Bågen '.$code.' togs bort.');
            }

            $sash->delete();
        });

        foreach ($paths as $path) {
            StoredSashPhoto::delete($path);
        }

        app(ReadyCustomerNotifier::class)->sync($project);

        return redirect()
            ->route('app.projects.show', $project)
            ->with('status', 'Bågen och bildfilerna har tagits bort.');
    }
}
