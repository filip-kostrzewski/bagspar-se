<?php

namespace App\Mail;

use App\Models\Quote;
use App\Support\Kronor;
use App\Support\SwedishDate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        $project = $this->quote->project;
        $workshop = $this->quote->workshop;
        $replyTo = $workshop?->contact_email;
        if (! is_string($replyTo) || trim($replyTo) === '') {
            $replyTo = config('bagspar.contact_email');
        }
        $replyName = $workshop?->name ?? 'Bågspår';

        return new Envelope(
            subject: 'Offert från '.($workshop?->name ?? 'verkstaden').': '.($project?->name ?? ''),
            replyTo: is_string($replyTo) && trim($replyTo) !== '' ? [new Address($replyTo, $replyName)] : [],
        );
    }

    public function content(): Content
    {
        $project = $this->quote->project;
        $workshopName = $this->quote->workshop?->name ?? 'Verkstaden';
        $contact = $project?->customer?->contact_name;
        $greeting = is_string($contact) && trim($contact) !== '' ? 'Hej '.trim($contact).'!' : 'Hej!';
        $this->quote->loadMissing('lines');

        $body = $workshopName.' har skickat en offert för '.($project?->name ?? 'projektet').'. Summan är '.Kronor::format($this->quote->totalOre()).' inklusive moms. Öppna länken för att se raderna och godkänna offerten med ditt namn.';

        if ($this->quote->valid_until !== null) {
            $body .= ' Offerten gäller till '.SwedishDate::format($this->quote->valid_until).'.';
        }

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => 'Offert från '.$workshopName,
                'greeting' => $greeting,
                'body' => $body,
                'actionText' => 'Öppna offerten',
                'url' => $this->url,
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Priset gäller före eventuellt ROT-avdrag, som görs på fakturan enligt Skatteverkets regler. Svara på mejlet om du har frågor.',
            ],
        );
    }
}
