<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Mail\ClientStatusLinkMail;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProjectClientStatusController extends Controller
{
    public function update(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $project);

        $action = $request->string('action')->toString();
        abort_unless(in_array($action, ['enable', 'disable', 'rotate'], true), 422);

        if ($action === 'disable') {
            $project->client_status_enabled = false;
            $project->save();

            return back()->with('status', 'Kundens statuslänk är av.');
        }

        if ($action === 'rotate' || ! is_string($project->client_status_token) || $project->client_status_token === '') {
            $project->issueClientStatusToken();
        }

        $project->client_status_enabled = true;
        $project->save();

        return back()->with('status', $action === 'rotate'
            ? 'En ny statuslänk har skapats. Den gamla länken fungerar inte längre.'
            : 'Kundens statuslänk är på.');
    }

    public function send(Request $request, Project $project): RedirectResponse
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $project);

        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        if (! $project->clientStatusIsPublic()) {
            return back()->withErrors(['client_status' => 'Slå på statuslänken först.']);
        }

        $project->loadMissing('customer');
        $email = $project->customer?->email;

        if (! is_string($email) || trim($email) === '') {
            return back()->withErrors(['client_status' => 'Kunden saknar e-postadress. Lägg till den under Kunder.']);
        }

        $email = trim($email);

        try {
            Mail::to($email)->send(new ClientStatusLinkMail(
                $project,
                route('client-status.show', ['token' => $project->client_status_token]),
            ));
        } catch (Throwable $exception) {
            Log::warning('Client status link mail could not be sent.', [
                'project_id' => $project->getKey(),
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['client_status' => 'Statuslänken kunde inte skickas just nu. Försök igen om en stund.']);
        }

        $project->recordEvent($actor, ProjectEvent::KIND_STATUS_LINK_SENT, 'Statuslänk skickad till '.$email.'.');

        return back()->with('status', 'Statuslänken har skickats till '.$email.'.');
    }
}
