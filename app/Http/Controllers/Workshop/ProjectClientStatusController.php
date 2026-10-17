<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectClientStatusController extends Controller
{
    public function update(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $project);

        $action = $request->string('action')->toString();
        abort_unless(in_array($action, ['enable', 'disable', 'rotate'], true), 422);

        if ($action === 'disable') {
            $project->client_status_enabled = false;
            $project->save();

            return back()->with('status', 'Kundens statuslänk är av.');
        }

        if ($action === 'rotate' || ! is_string($project->client_status_token) || $project->client_status_token === '') {
            $project->issueClientStatusToken();
        }

        $project->client_status_enabled = true;
        $project->save();

        return back()->with('status', $action === 'rotate'
            ? 'En ny statuslänk har skapats. Den gamla länken fungerar inte längre.'
            : 'Kundens statuslänk är på.');
    }
}
