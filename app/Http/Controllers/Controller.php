<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\WindowSash;
use App\Models\Workshop;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function currentWorkshop(Request $request): Workshop
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return $user->workshop()->firstOrFail();
    }

    protected function projectForWorkshop(Workshop $workshop, Project $project): Project
    {
        return $workshop->projects()->whereKey($project->getKey())->firstOrFail();
    }

    protected function sashForProject(Project $project, WindowSash $sash): WindowSash
    {
        return $project->windowSashes()->whereKey($sash->getKey())->firstOrFail();
    }
}
