<?php

namespace App\Console\Commands;

use App\Mail\AccessEndingReminder;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

#[Signature('bagspar:send-access-reminders')]
#[Description('Mail the owners of workshops whose trial or paid period ends within three days.')]
class SendAccessReminders extends Command
{
    private const REMINDER_DAYS = 3;

    public function handle(): int
    {
        $now = now();
        $horizon = $now->copy()->addDays(self::REMINDER_DAYS);
        $reminded = 0;

        Workshop::query()
            ->whereNull('access_reminder_sent_at')
            ->where(function ($query) use ($horizon): void {
                $query->where('trial_ends_at', '<=', $horizon)
                    ->orWhere('paid_until', '<=', $horizon)
                    ->orWhereNotNull('subscription_ordered_at');
            })
            ->chunkById(100, function ($workshops) use ($now, $horizon, &$reminded): void {
                foreach ($workshops as $workshop) {
                    if ($this->remind($workshop, $now, $horizon)) {
                        $reminded++;
                    }
                }
            });

        $this->info("Skickade påminnelser till {$reminded} verkstäder.");

        return Command::SUCCESS;
    }

    private function remind(Workshop $workshop, Carbon $now, Carbon $horizon): bool
    {
        $endsAt = $workshop->accessEndsAt();

        if (! $endsAt instanceof Carbon || $endsAt->lt($now) || $endsAt->gt($horizon)) {
            return false;
        }

        if (! in_array($workshop->accessState(), [Workshop::ACCESS_TRIAL, Workshop::ACCESS_ACTIVE], true)) {
            return false;
        }

        $owners = $workshop->users()->where('role', User::ROLE_OWNER)->get();

        foreach ($owners as $owner) {
            try {
                Mail::to($owner->email)->send(new AccessEndingReminder($workshop));
            } catch (Throwable $exception) {
                Log::warning('Access ending reminder could not be sent.', [
                    'workshop_id' => $workshop->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }

        $workshop->update(['access_reminder_sent_at' => $now]);

        return true;
    }
}
