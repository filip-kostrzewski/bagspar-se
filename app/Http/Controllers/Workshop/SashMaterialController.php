<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SashMaterial;
use App\Models\WindowSash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SashMaterialController extends Controller
{
    public function store(Request $request, Project $project, WindowSash $sash): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);

        $quantity = $request->input('quantity');
        if (is_string($quantity)) {
            $request->merge(['quantity' => str_replace(',', '.', trim($quantity))]);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(SashMaterial::TYPES)],
            'description' => ['required', 'string', 'max:160'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999.99', 'decimal:0,2'],
            'unit' => ['required', 'string', 'max:20'],
        ], [
            'type.required' => 'Välj typ av material.',
            'type.in' => 'Välj glas, kitt, färg, beslag eller övrigt.',
            'description.required' => 'Ange en beskrivning.',
            'description.max' => 'Beskrivningen får vara högst 160 tecken.',
            'quantity.required' => 'Ange mängden.',
            'quantity.numeric' => 'Ange mängden som ett tal.',
            'quantity.min' => 'Mängden måste vara minst 0,01.',
            'quantity.max' => 'Mängden får vara högst 9 999,99.',
            'quantity.decimal' => 'Mängden får ha högst två decimaler.',
            'unit.required' => 'Ange en enhet.',
            'unit.max' => 'Enheten får vara högst 20 tecken.',
        ]);

        $sash->materials()->create([
            'type' => $validated['type'],
            'description' => trim($validated['description']),
            'quantity' => $validated['quantity'],
            'unit' => trim($validated['unit']),
        ]);

        return back()->with('status', 'Materialet har sparats.');
    }

    public function destroy(Request $request, Project $project, WindowSash $sash, SashMaterial $material): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $material = $sash->materials()->whereKey($material->getKey())->firstOrFail();
        $material->delete();

        return back()->with('status', 'Materialraden har tagits bort.');
    }
}
