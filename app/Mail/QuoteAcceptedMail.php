<?php

namespace App\Mail;

use App\Models\Quote;
use App\Support\SwedishDate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        $replyTo = config('bagspar.contact_email');

        return new Envelope(
            subject: 'Offerten är godkänd: '.($this->quote->project?->name ?? ''),
            replyTo: is_string($replyTo) && $replyTo !== '' ? [new Address($replyTo, 'Bågspår')] : [],
        );
    }

    public function content(): Content
    {
        $project = $this->quote->project;
        $name = is_string($this->quote->accepted_name) && $this->quote->accepted_name !== '' ? $this->quote->accepted_name : 'Kunden';

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => 'Offerten är godkänd',
                'greeting' => 'Hej!',
                'body' => $name.' godkände offerten för '.($project?->name ?? 'projektet').' '.SwedishDate::formatDateTime($this->quote->accepted_at).'. Namnet och tidpunkten är sparade på offerten.',
                'actionText' => 'Öppna projektet',
                'url' => $project !== null ? route('app.projects.show', $project) : route('app.dashboard'),
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Du får det här mejlet eftersom du är ägare till verkstadens konto.',
            ],
        );
    }
}
