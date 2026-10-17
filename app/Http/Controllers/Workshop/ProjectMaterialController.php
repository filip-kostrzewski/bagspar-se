<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SashMaterial;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectMaterialController extends Controller
{
    public function show(Request $request, Project $project): View
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        $materials = SashMaterial::query()
            ->whereHas('sash', fn ($query) => $query->where('project_id', $project->getKey()))
            ->orderBy('id')
            ->get();

        $groups = [];

        foreach ($materials as $material) {
            $description = trim($material->description);
            $unit = trim($material->unit);
            $key = $material->type.'|'.mb_strtolower($description).'|'.mb_strtolower($unit);

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'label' => SashMaterial::labelForType($material->type),
                    'description' => $description,
                    'unit' => $unit,
                    'quantity' => 0.0,
                ];
            }

            $groups[$key]['quantity'] += (float) $material->quantity;
        }

        $lines = array_map(function (array $group): array {
            $group['quantity_label'] = SashMaterial::quantityLabel($group['quantity']);

            return $group;
        }, array_values($groups));

        return view('app.projects.materials', [
            'workshop' => $workshop,
            'project' => $project,
            'lines' => $lines,
        ]);
    }
}
