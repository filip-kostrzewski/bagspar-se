<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WindowSash;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabelPrintController extends Controller
{
    public function show(Request $request, Project $project, WindowSash $sash): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);

        return view('app.labels.show', compact('workshop', 'project', 'sash'));
    }

    public function sheet(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sashes = $project->windowSashes()->orderBy('code')->get();

        return view('app.labels.sheet', compact('workshop', 'project', 'sashes'));
    }
}
