<?php

namespace App\Mail;

use App\Models\Workshop;
use App\Support\Pricing;
use App\Support\SwedishDate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessEndingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Workshop $workshop) {}

    public function envelope(): Envelope
    {
        $replyTo = config('bagspar.contact_email');
        $subject = $this->isTrial()
            ? 'Provperioden för '.$this->workshop->name.' tar slut '.$this->endDate()
            : 'Betalperioden för '.$this->workshop->name.' tar slut '.$this->endDate();

        return new Envelope(
            subject: $subject,
            replyTo: is_string($replyTo) && $replyTo !== '' ? [new Address($replyTo, 'Bågspår')] : [],
        );
    }

    public function content(): Content
    {
        $workshop = $this->workshop;
        $date = $this->endDate();

        if ($this->isTrial()) {
            $heading = 'Provperioden tar snart slut';
            $body = 'Provperioden för '.$workshop->name.' tar slut '.$date.'. Beställ abonnemanget under Abonnemang så håller du kontot öppet utan avbrott. '
                .'Det kostar '.Pricing::monthlyLabel().' exkl. moms per verkstad. '.Pricing::invoiceSentence();
            $actionText = 'Beställ abonnemang';
        } else {
            $heading = 'Betalperioden tar snart slut';
            $body = 'Den betalda perioden för '.$workshop->name.' tar slut '.$date.'. Nästa faktura skickas till '.$workshop->billing_email.'.';
            $actionText = 'Visa abonnemang';
        }

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => $heading,
                'greeting' => 'Hej!',
                'body' => $body,
                'actionText' => $actionText,
                'url' => route('app.billing.edit'),
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Du får det här mejlet eftersom du är ägare till kontot. Svara på mejlet om du har frågor.',
            ],
        );
    }

    private function isTrial(): bool
    {
        return $this->workshop->accessState() === Workshop::ACCESS_TRIAL;
    }

    private function endDate(): string
    {
        return SwedishDate::formatLocal($this->workshop->accessEndsAt());
    }
}
