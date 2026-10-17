<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientStatusApprovalController extends Controller
{
    public function store(Request $request, string $token): RedirectResponse
    {
        $project = Project::query()
            ->where('client_status_token', $token)
            ->where('client_status_enabled', true)
            ->firstOrFail();

        $back = redirect()->route('client-status.show', ['token' => $token]);

        if ($project->delivery_approved_at !== null) {
            return $back->with('status', 'Leveransen är redan godkänd.');
        }

        if (! $project->allSashesAreReady()) {
            return $back->with('status', 'Du kan godkänna leveransen när alla bågar är klara.');
        }

        $name = $request->input('name');

        if (is_string($name)) {
            $request->merge(['name' => trim($name)]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'confirm' => ['accepted'],
        ], [
            'name.required' => 'Ange ditt namn.',
            'name.string' => 'Ange ditt namn som text.',
            'name.max' => 'Namnet får vara högst 120 tecken.',
            'confirm.accepted' => 'Bekräfta att du godkänner leveransen.',
        ]);

        $saved = Project::query()
            ->whereKey($project->getKey())
            ->whereNull('delivery_approved_at')
            ->update([
                'delivery_approved_name' => $validated['name'],
                'delivery_approved_at' => now(),
            ]);

        if ($saved === 0) {
            return $back->with('status', 'Leveransen är redan godkänd.');
        }

        return $back->with('status', 'Leveransen är godkänd.');
    }
}
