<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectReadyMail extends Mailable
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
            subject: 'Alla bågar är klara — '.$this->project->name,
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
                'heading' => 'Alla bågar är klara',
                'greeting' => $greeting,
                'body' => $workshopName.' har markerat alla bågar i '.$this->project->name.' som klara. Här kan du se status och bilderna före och efter.',
                'actionText' => 'Öppna status',
                'url' => $this->url,
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Du får mejlet för att verkstaden har valt att meddela dig när arbetet är klart. Arbetsanteckningar följer inte med.',
            ],
        );
    }
}
