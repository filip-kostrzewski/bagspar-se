<?php

namespace App\Mail;

use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingNudge extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Workshop $workshop) {}

    public function envelope(): Envelope
    {
        $replyTo = config('bagspar.contact_email');

        return new Envelope(
            subject: 'Kom igång med Bågspår: lägg in det första projektet',
            replyTo: is_string($replyTo) && $replyTo !== '' ? [new Address($replyTo, 'Bågspår')] : [],
        );
    }

    public function content(): Content
    {
        $workshop = $this->workshop;
        $daysLeft = $workshop->accessDaysLeft();

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => 'Första projektet tar fem minuter',
                'greeting' => 'Hej!',
                'body' => 'Provperioden för '.$workshop->name.' har '.$daysLeft.' '.($daysLeft === 1 ? 'dag' : 'dagar').' kvar och det finns inget projekt ännu. '
                    .'Skapa ett projekt, ge varje båge en kod och ett foto, så gör arbetskortet resten. '
                    .'Guiderna visar hur du märker bågarna så att varje båge hamnar i samma öppning igen.',
                'actionText' => 'Skapa första projektet',
                'url' => route('app.projects.create'),
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Guiderna finns på '.route('guides.index').'. Svara på det här mejlet om du vill ha en genomgång på 15 minuter.',
            ],
        );
    }
}
