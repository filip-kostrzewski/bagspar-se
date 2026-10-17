<?php

namespace App\Mail;

use App\Models\Workshop;
use App\Support\Pricing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionOrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Workshop $workshop,
        public bool $isFirstOrder,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = config('bagspar.contact_email');

        return new Envelope(
            subject: $this->isFirstOrder
                ? 'Tack för din beställning av Bågspår'
                : 'Ditt abonnemang på Bågspår är uppdaterat',
            replyTo: is_string($replyTo) && $replyTo !== '' ? [new Address($replyTo, 'Bågspår')] : [],
        );
    }

    public function content(): Content
    {
        $workshop = $this->workshop;
        $isYearly = $workshop->isOnYearlyPlan();
        $amount = $isYearly
            ? Pricing::kr($workshop->yearlyPriceKr()).' kr/år exkl. moms'
            : Pricing::kr($workshop->monthlyPriceKr()).' kr/mån exkl. moms';
        $period = $isYearly ? 'tolv månader i förskott' : 'en månad i taget i förskott';

        return new Content(
            view: 'mail.auth.notification',
            text: 'mail.auth.notification-text',
            with: [
                'heading' => $this->isFirstOrder ? 'Tack för din beställning' : 'Abonnemanget är uppdaterat',
                'greeting' => 'Hej!',
                'body' => $workshop->name.' har '.($this->isFirstOrder ? 'beställt' : 'bytt till').' '
                    .mb_strtolower($workshop->planLabel() ?? 'abonnemang').' på Bågspår: '.$amount
                    .' för '.$workshop->seatCount().' användare. Vi fakturerar '.$period
                    .' med '.Pricing::invoiceDays().' dagars betalningsvillkor till '.$workshop->billing_email
                    .'. Kontot är öppet under tiden.',
                'actionText' => 'Öppna Bågspår',
                'url' => route('app.dashboard'),
                'fallbackText' => 'Om knappen inte fungerar kan du kopiera länken nedan och klistra in den i webbläsaren:',
                'notice' => 'Ingen bindningstid. Säg upp när som helst genom att skriva till oss, så avslutas abonnemanget vid slutet av den betalda perioden.',
            ],
        );
    }
}
