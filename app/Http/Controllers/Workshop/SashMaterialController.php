<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SashEvent;
use App\Models\SashMaterial;
use App\Models\User;
use App\Models\WindowSash;
use App\Support\Kronor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class SashMaterialController extends Controller
{
    public function store(Request $request, Project $project, WindowSash $sash): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $actor = $this->actor($request);
        $validated = $this->validatedMaterial($request);

        DB::transaction(function () use ($actor, $sash, $validated): void {
            $material = $sash->materials()->create($validated);
            $sash->recordEvent($actor, SashEvent::KIND_MATERIAL, null, 'Material lades till: '.$material->historyLabel().'.');
        });

        return back()->with('status', 'Materialet har sparats.');
    }

    public function update(Request $request, Project $project, WindowSash $sash, SashMaterial $material): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $material = $sash->materials()->whereKey($material->getKey())->firstOrFail();
        $actor = $this->actor($request);
        $validated = $this->validatedMaterial($request);
        $before = $material->historyLabel();

        DB::transaction(function () use ($actor, $before, $material, $sash, $validated): void {
            $material->update($validated);
            $sash->recordEvent($actor, SashEvent::KIND_MATERIAL, null, 'Material ändrades: '.$before.' blev '.$material->historyLabel().'.');
        });

        return back()->with('status', 'Materialet har ändrats.');
    }

    public function destroy(Request $request, Project $project, WindowSash $sash, SashMaterial $material): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $sash = $this->sashForProject($project, $sash);
        $material = $sash->materials()->whereKey($material->getKey())->firstOrFail();
        $actor = $this->actor($request);
        $label = $material->historyLabel();

        DB::transaction(function () use ($actor, $label, $material, $sash): void {
            $sash->recordEvent($actor, SashEvent::KIND_MATERIAL, null, 'Material togs bort: '.$label.'.');
            $material->delete();
        });

        return back()->with('status', 'Materialraden har tagits bort.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }

    /**
     * @return array{type: string, description: string, quantity: mixed, unit: string, unit_price_ore?: int|null}
     */
    private function validatedMaterial(Request $request): array
    {
        $quantity = $request->input('quantity');
        if (is_string($quantity)) {
            $request->merge(['quantity' => str_replace(',', '.', trim($quantity))]);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(SashMaterial::TYPES)],
            'description' => ['required', 'string', 'max:160'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999.99', 'decimal:0,2'],
            'unit' => ['required', 'string', 'max:20'],
            'unit_price' => ['nullable', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    Kronor::parseOptional($value);
                } catch (InvalidArgumentException) {
                    $fail('Ange à-priset i kronor, till exempel 800 eller 800,50.');
                }
            }],
        ], [
            'type.required' => 'Välj typ av material.',
            'type.in' => 'Välj glas, kitt, färg, beslag eller övrigt.',
            'description.required' => 'Ange en beskrivning.',
            'description.max' => 'Beskrivningen får vara högst 160 tecken.',
            'quantity.required' => 'Ange mängden.',
            'quantity.numeric' => 'Ange mängden som ett positivt tal.',
            'quantity.min' => 'Mängden måste vara ett positivt tal.',
            'quantity.max' => 'Mängden får vara högst 9 999,99.',
            'quantity.decimal' => 'Mängden får ha högst två decimaler.',
            'unit.required' => 'Ange en enhet.',
            'unit.max' => 'Enheten får vara högst 20 tecken.',
            'unit_price.max' => 'À-priset får vara högst 1 000 000 kr.',
        ]);

        $material = [
            'type' => $validated['type'],
            'description' => trim($validated['description']),
            'quantity' => $validated['quantity'],
            'unit' => trim($validated['unit']),
        ];

        if ($request->exists('unit_price')) {
            $material['unit_price_ore'] = Kronor::parseOptional($validated['unit_price'] ?? null);
        }

        return $material;
    }
}
