<?php

namespace App\Mail;

class OutboundMail
{
    /**
     * Whether the default mailer can deliver a message to a person.
     *
     * Log and array mailers only record the message. Postmark also needs an API key,
     * otherwise the send fails and the visitor never gets the confirmation.
     */
    public static function isConfigured(): bool
    {
        $mailer = config('mail.default');

        if (! is_string($mailer) || in_array($mailer, ['log', 'array'], true)) {
            return false;
        }

        if ($mailer === 'postmark') {
            $key = config('services.postmark.key');

            return is_string($key) && trim($key) !== '';
        }

        return true;
    }
}
