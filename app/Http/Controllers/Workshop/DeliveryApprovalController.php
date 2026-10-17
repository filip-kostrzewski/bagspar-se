<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeliveryApprovalController extends Controller
{
    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $workshop);

        $project->forceFill([
            'delivery_approved_at' => null,
            'delivery_approved_name' => null,
        ])->save();

        return back()->with('status', 'Godkännandet har tagits bort.');
    }
}
