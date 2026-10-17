<?php

namespace App\Support;

use App\Mail\OutboundMail;
use App\Mail\ProjectReadyMail;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReadyCustomerNotifier
{
    public function sync(Project $project): bool
    {
        $project->refresh();

        if (! $project->allSashesAreReady()) {
            if ($project->customer_ready_notified_at !== null) {
                $project->customer_ready_notified_at = null;
                $project->save();
            }

            return false;
        }

        if (! $project->notify_customer_when_ready || $project->customer_ready_notified_at !== null) {
            return false;
        }

        $project->loadMissing('customer');
        $email = $project->customer?->email;

        if (! is_string($email) || trim($email) === '' || ! OutboundMail::isConfigured()) {
            return false;
        }

        if (! $project->clientStatusIsPublic()) {
            if (! is_string($project->client_status_token) || $project->client_status_token === '') {
                $project->issueClientStatusToken();
            }

            $project->client_status_enabled = true;
            $project->save();
        }

        $token = $project->client_status_token;

        if (! is_string($token) || $token === '') {
            return false;
        }

        if (! CustomerMailLimit::allow($project->workshop()->firstOrFail())) {
            Log::warning('Ready notification skipped: daily customer mail limit reached.', [
                'project_id' => $project->getKey(),
            ]);

            return false;
        }

        try {
            Mail::to($email)->send(new ProjectReadyMail(
                $project,
                route('client-status.show', ['token' => $token]),
            ));
        } catch (Throwable $exception) {
            Log::warning('Ready notification could not be sent.', [
                'project_id' => $project->getKey(),
                'exception' => $exception::class,
            ]);

            return false;
        }

        $project->customer_ready_notified_at = now();
        $project->save();

        return true;
    }
}
