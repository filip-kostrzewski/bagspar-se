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
        $showingArchived = $request->boolean('arkiv');
        $currentProjects = $workshop->projects()->notArchived();
        $projects = $workshop->projects()
            ->when($showingArchived, fn ($query) => $query->archived(), fn ($query) => $query->notArchived())
            ->withCount('windowSashes')
            ->latest()
            ->get();

        return view('app.dashboard', [
            'workshop' => $workshop,
            'projects' => $projects,
            'showingArchived' => $showingArchived,
            'archivedCount' => $workshop->projects()->archived()->count(),
            'hasAnyProjects' => $workshop->projects()->exists(),
            'activeProjectsCount' => (clone $currentProjects)->where('status', 'active')->count(),
            'completedProjectsCount' => (clone $currentProjects)->where('status', 'completed')->count(),
            'sashesInProgressCount' => WindowSash::query()
                ->whereHas('project', fn ($query) => $query->where('workshop_id', $workshop->getKey())->whereNull('archived_at'))
                ->where('stage', '!=', 'ready')
                ->count(),
        ]);
    }
}
