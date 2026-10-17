<?php

namespace App\Mail;

use App\Models\WorkshopInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkshopInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkshopInvitation $invitation,
        public string $url,
        public string $inviterName,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = config('bagspar.contact_email');
        $workshopName = $this->invitation->workshop?->name ?? 'verkstaden';

        return new Envelope(
            subject: 'Inbjudan till '.$workshopName.' i Bågspår',
            replyTo: is_string($replyTo) && $replyTo !== '' ? [new Address($replyTo, 'Bågspår')] : [],
        );
    }

    public function content(): Content
    {
        $workshopName = $this->invitation->workshop?->name ?? 'verkstaden';

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => 'Du är inbjuden som medarbetare',
                'greeting' => 'Hej!',
                'body' => $this->inviterName.' har bjudit in dig som medarbetare i '.$workshopName.'. Välj ett lösenord för att öppna verkstadens arbetskort. Du kan inte ändra verkstadens uppgifter eller ta bort projekt.',
                'actionText' => 'Välj lösenord',
                'url' => $this->url,
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Länken gäller i 7 dagar. Om du inte väntade dig inbjudan kan du bortse från mejlet.',
            ],
        );
    }
}
