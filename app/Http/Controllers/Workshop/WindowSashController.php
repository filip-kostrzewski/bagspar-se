<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WindowSash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WindowSashController extends Controller
{
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
            'code.max' => 'Koden får vara högst 120 tecken.',
            'code.unique' => 'Koden används redan i det här projektet.',
            'room.max' => 'Rummet får vara högst 120 tecken.',
            'location.max' => 'Placeringen får vara högst 160 tecken.',
            'stage.in' => 'Välj ett giltigt arbetssteg.',
            'notes.max' => 'Anteckningarna får vara högst 3 000 tecken.',
        ]);

        $project->windowSashes()->create($validated + ['stage' => 'received']);

        return back()->with('status', 'Arbetskortet har skapats.');
    }

    public function show(Request $request, Project $project, WindowSash $sash): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $photos = $sash->photos()->latest()->get();

        return view('app.sashes.show', compact('workshop', 'project', 'sash', 'photos'));
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
            'code.max' => 'Koden får vara högst 120 tecken.',
            'code.unique' => 'Koden används redan i det här projektet.',
            'room.max' => 'Rummet får vara högst 120 tecken.',
            'location.max' => 'Placeringen får vara högst 160 tecken.',
            'stage.in' => 'Välj ett giltigt arbetssteg.',
            'notes.max' => 'Anteckningarna får vara högst 3 000 tecken.',
        ]);

        abort_if($validated === [], 422);
        $sash->update($validated);

        return back()->with('status', 'Arbetskortet har uppdaterats.');
    }
}
