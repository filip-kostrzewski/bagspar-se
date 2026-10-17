<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\WindowSash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $workshop = $this->currentWorkshop($request);
        $showingArchived = $request->boolean('arkiv');
        $filters = [
            'kund' => mb_substr(trim($request->string('kund')->toString()), 0, 160),
            'rum' => mb_substr(trim($request->string('rum')->toString()), 0, 120),
            'etapp' => $request->string('etapp')->toString(),
        ];

        if (! in_array($filters['etapp'], WindowSash::STAGES, true)) {
            $filters['etapp'] = '';
        }

        $currentProjects = $workshop->projects()->notArchived();
        $projects = $workshop->projects()
            ->when($showingArchived, fn (Builder $query) => $query->archived(), fn (Builder $query) => $query->notArchived())
            ->when($filters['kund'] !== '', function (Builder $query) use ($filters): void {
                $like = $this->like($filters['kund']);
                $query->where(function (Builder $customers) use ($like): void {
                    $customers->where('customer_name', 'like', $like)
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('name', 'like', $like));
                });
            })
            ->when($filters['rum'] !== '' || $filters['etapp'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('windowSashes', function (Builder $sashes) use ($filters): void {
                    if ($filters['rum'] !== '') {
                        $sashes->where('room', 'like', $this->like($filters['rum']));
                    }

                    if ($filters['etapp'] !== '') {
                        $sashes->where('stage', $filters['etapp']);
                    }
                });
            })
            ->with('customer')
            ->withCount('windowSashes')
            ->latest()
            ->get();

        return view('app.dashboard', [
            'workshop' => $workshop,
            'projects' => $projects,
            'showingArchived' => $showingArchived,
            'filters' => $filters,
            'hasActiveFilters' => $filters['kund'] !== '' || $filters['rum'] !== '' || $filters['etapp'] !== '',
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

    private function like(string $value): string
    {
        return '%'.addcslashes($value, '%_\\').'%';
    }
}
