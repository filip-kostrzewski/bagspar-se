<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(Request $request): View
    {
        return view('app.projects.create', [
            'workshop' => $this->currentWorkshop($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'customer_name' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'name.required' => 'Ange ett projektnamn.',
            'name.max' => 'Projektnamnet får vara högst 160 tecken.',
            'customer_name.max' => 'Kundnamnet får vara högst 160 tecken.',
            'location.max' => 'Platsen får vara högst 255 tecken.',
            'notes.max' => 'Anteckningarna får vara högst 5 000 tecken.',
        ]);

        $project = $workshop->projects()->create($validated);

        return redirect()->route('app.projects.show', $project)->with('status', 'Projektet har sparats.');
    }

    public function show(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sashes = $project->windowSashes()->orderBy('code')->get();

        return view('app.projects.show', compact('workshop', 'project', 'sashes'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'customer_name' => ['sometimes', 'nullable', 'string', 'max:160'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'completed'])],
        ], [
            'name.required' => 'Ange ett projektnamn.',
            'name.max' => 'Projektnamnet får vara högst 160 tecken.',
            'customer_name.max' => 'Kundnamnet får vara högst 160 tecken.',
            'location.max' => 'Platsen får vara högst 255 tecken.',
            'notes.max' => 'Anteckningarna får vara högst 5 000 tecken.',
            'status.in' => 'Välj en giltig projektstatus.',
        ]);

        abort_if($validated === [], 422);
        $project->update($validated);

        return back()->with('status', 'Projektet har uppdaterats.');
    }

    public function report(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sashes = $project->windowSashes()->with('photos')->orderBy('code')->get();

        return view('app.projects.report', compact('workshop', 'project', 'sashes'));
    }
}
