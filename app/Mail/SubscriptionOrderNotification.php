<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Workshop;
use App\Support\Pricing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Internal mail with everything needed to issue the invoice.
 */
class SubscriptionOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Workshop $workshop,
        public ?User $actor,
        public bool $isFirstOrder,
    ) {}

    public function envelope(): Envelope
    {
        $kind = $this->isFirstOrder ? 'Ny beställning' : 'Ändrat abonnemang';

        return new Envelope(
            subject: $kind.': '.$this->workshop->name.' ('.($this->workshop->planLabel() ?? 'okänd plan').')',
        );
    }

    public function content(): Content
    {
        $workshop = $this->workshop;
        $isYearly = $workshop->isOnYearlyPlan();

        return new Content(
            view: 'mail.subscription-order-notification',
            text: 'mail.subscription-order-notification-text',
            with: [
                'workshop' => $workshop,
                'actor' => $this->actor,
                'isFirstOrder' => $this->isFirstOrder,
                'planLabel' => $workshop->planLabel() ?? 'okänd plan',
                'seatCount' => $workshop->seatCount(),
                'includedSeats' => Pricing::includedSeats(),
                'extraSeats' => $workshop->extraSeatCount(),
                'amountLabel' => $isYearly
                    ? Pricing::kr($workshop->yearlyPriceKr()).' kr/år exkl. moms'
                    : Pricing::kr($workshop->monthlyPriceKr()).' kr/mån exkl. moms',
            ],
        );
    }
}
