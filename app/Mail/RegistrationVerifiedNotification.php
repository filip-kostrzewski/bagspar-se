<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Internal mail when a new workshop owner has confirmed the email address.
 */
class RegistrationVerifiedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Workshop $workshop,
        public User $owner,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nytt verkstadskonto: '.$this->workshop->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.registration-verified-notification',
            text: 'mail.registration-verified-notification-text',
            with: [
                'workshop' => $this->workshop,
                'owner' => $this->owner,
                'registeredAt' => $this->owner->created_at?->timezone('Europe/Stockholm')->format('Y-m-d H:i'),
                'adminUrl' => filled(config('bagspar.admin_token')) ? route('admin.workshops') : null,
            ],
        );
    }
}
