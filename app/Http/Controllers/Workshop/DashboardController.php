<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WindowSash;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $workshop = $this->currentWorkshop($request);
        $showingArchived = $request->boolean('arkiv');
        $deliveryFilter = $request->string('leverans')->toString();
        $filters = [
            'kund' => mb_substr(trim($request->string('kund')->toString()), 0, 160),
            'rum' => mb_substr(trim($request->string('rum')->toString()), 0, 120),
            'etapp' => WindowSash::stageFromFilter($request->string('etapp')->toString()),
            'leverans' => in_array($deliveryFilter, ['forsenade', 'kommande'], true) ? $deliveryFilter : '',
        ];
        [$today, $upcomingEnd] = $this->deliveryWindow();

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
            ->when($filters['leverans'] === 'forsenade', fn (Builder $query) => $query->deliveryOverdue($today))
            ->when($filters['leverans'] === 'kommande', fn (Builder $query) => $query->deliveryDuring($today, $upcomingEnd))
            ->with('customer')
            ->withCount('windowSashes')
            ->latest()
            ->get();

        return view('app.dashboard', [
            'workshop' => $workshop,
            'projects' => $projects,
            'showingArchived' => $showingArchived,
            'filters' => $filters,
            'hasActiveFilters' => $filters['kund'] !== '' || $filters['rum'] !== '' || $filters['etapp'] !== '' || $filters['leverans'] !== '',
            'overdueProjects' => $showingArchived ? collect() : $this->deliveryProjects($workshop)->deliveryOverdue($today)->get(),
            'upcomingProjects' => $showingArchived ? collect() : $this->deliveryProjects($workshop)->deliveryDuring($today, $upcomingEnd)->get(),
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

    /**
     * @return array{0: string, 1: string}
     */
    private function deliveryWindow(): array
    {
        $today = now()->timezone('Europe/Stockholm')->startOfDay();

        return [$today->toDateString(), $today->copy()->addDays(6)->toDateString()];
    }

    /**
     * @return HasMany<Project, Workshop>
     */
    private function deliveryProjects(Workshop $workshop): HasMany
    {
        return $workshop->projects()
            ->notArchived()
            ->with('customer')
            ->orderBy('delivery_date')
            ->orderBy('id');
    }

    private function like(string $value): string
    {
        return '%'.addcslashes($value, '%_\\').'%';
    }
}
