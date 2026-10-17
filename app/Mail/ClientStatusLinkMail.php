<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientStatusLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->project->workshop?->contact_email;
        if (! is_string($replyTo) || trim($replyTo) === '') {
            $replyTo = config('bagspar.contact_email');
        }
        $replyName = $this->project->workshop?->name ?? 'Bågspår';

        return new Envelope(
            subject: 'Följ arbetet med '.$this->project->name,
            replyTo: is_string($replyTo) && trim($replyTo) !== '' ? [new Address($replyTo, $replyName)] : [],
        );
    }

    public function content(): Content
    {
        $workshopName = $this->project->workshop?->name ?? 'Verkstaden';
        $contact = $this->project->customer?->contact_name;
        $greeting = is_string($contact) && trim($contact) !== '' ? 'Hej '.trim($contact).'!' : 'Hej!';

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => 'Följ arbetet med '.$this->project->name,
                'greeting' => $greeting,
                'body' => $workshopName.' har gjort en statussida för '.$this->project->name.'. Där ser du vilken etapp varje båge är i och bilderna före och efter. Du behöver inte logga in.',
                'actionText' => 'Öppna statussidan',
                'url' => $this->url,
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Du får mejlet för att verkstaden har skickat länken till dig. Arbetsanteckningar följer inte med. Svara på mejlet om du har frågor.',
            ],
        );
    }
}
