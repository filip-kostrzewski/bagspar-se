<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Support\SampleProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SampleProjectController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        $existing = $workshop->projects()
            ->where('name', 'like', SampleProject::NAME_PREFIX.'%')
            ->orderBy('id')
            ->first();

        if ($existing instanceof Project) {
            return redirect()
                ->route('app.projects.show', $existing)
                ->with('status', 'Exempelprojektet finns redan.');
        }

        $project = SampleProject::createFor($workshop, $actor);

        return redirect()
            ->route('app.projects.show', $project)
            ->with('status', 'Exempelprojektet är skapat. Ta bort det när du vill.');
    }
}
