<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = config('bagspar.contact_email');

        return new Envelope(
            subject: 'Vi har tagit emot ditt meddelande',
            replyTo: is_string($replyTo) && $replyTo !== '' ? [new Address($replyTo, 'Bågspår')] : [],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-message-confirmation',
            text: 'mail.contact-message-confirmation-text',
        );
    }
}
