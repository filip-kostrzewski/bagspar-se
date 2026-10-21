<?php

namespace App\Listeners;

use App\Mail\RegistrationVerifiedNotification;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Tells the team about a new workshop account once its owner confirms the email address.
 *
 * Mail goes out synchronously like the rest of the app's mail, so it does not depend
 * on a queue worker. A failure is logged and never interrupts the verification.
 */
class SendRegistrationVerifiedNotification
{
    public function handle(Verified $event): void
    {
        $user = $event->user;
        $recipient = config('bagspar.lead_notification_email');

        if (! $user instanceof User || ! $user->isOwner() || ! is_string($recipient) || trim($recipient) === '') {
            return;
        }

        try {
            $workshop = $user->workshop;

            if (! $workshop instanceof Workshop || ! $this->claimNotification($workshop)) {
                return;
            }

            Mail::to(trim($recipient))->send(new RegistrationVerifiedNotification($workshop, $user));
        } catch (Throwable $exception) {
            Log::warning('Registration notification could not be sent.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }

    /**
     * Mark the workshop as announced, so a later email change and re-verification stays quiet.
     */
    private function claimNotification(Workshop $workshop): bool
    {
        return Workshop::query()
            ->whereKey($workshop->getKey())
            ->whereNull('registration_notified_at')
            ->update(['registration_notified_at' => now()]) === 1;
    }
}
