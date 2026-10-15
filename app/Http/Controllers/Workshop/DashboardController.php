<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\WindowSash;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $workshop = $this->currentWorkshop($request);
        $projects = $workshop->projects()
            ->withCount('windowSashes')
            ->latest()
            ->get();

        return view('app.dashboard', [
            'workshop' => $workshop,
            'projects' => $projects,
            'activeProjectsCount' => $projects->where('status', 'active')->count(),
            'completedProjectsCount' => $projects->where('status', 'completed')->count(),
            'sashesInProgressCount' => WindowSash::query()
                ->whereHas('project', fn ($query) => $query->where('workshop_id', $workshop->getKey()))
                ->where('stage', '!=', 'ready')
                ->count(),
        ]);
    }
}
