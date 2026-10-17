<?php

namespace App\Console\Commands;

use App\Mail\OnboardingNudge;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

#[Signature('bagspar:send-onboarding-nudges')]
#[Description('Mail the owners of trial workshops that have no project five days after registration.')]
class SendOnboardingNudges extends Command
{
    private const NUDGE_AFTER_DAYS = 5;

    public function handle(): int
    {
        $now = now();
        $nudged = 0;

        Workshop::query()
            ->whereNull('onboarding_nudge_sent_at')
            ->where('created_at', '<=', $now->copy()->subDays(self::NUDGE_AFTER_DAYS))
            ->doesntHave('projects')
            ->chunkById(100, function ($workshops) use ($now, &$nudged): void {
                foreach ($workshops as $workshop) {
                    if ($this->nudge($workshop, $now)) {
                        $nudged++;
                    }
                }
            });

        $this->info("Skickade igångkörningsmejl till {$nudged} verkstäder.");

        return Command::SUCCESS;
    }

    private function nudge(Workshop $workshop, Carbon $now): bool
    {
        if ($workshop->accessState() !== Workshop::ACCESS_TRIAL) {
            return false;
        }

        $owners = $workshop->users()->where('role', User::ROLE_OWNER)->get();

        foreach ($owners as $owner) {
            try {
                Mail::to($owner->email)->send(new OnboardingNudge($workshop));
            } catch (Throwable $exception) {
                Log::warning('Onboarding nudge could not be sent.', [
                    'workshop_id' => $workshop->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }

        $workshop->update(['onboarding_nudge_sent_at' => $now]);

        return true;
    }
}
