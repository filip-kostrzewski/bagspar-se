<?php

namespace App\Support;

/**
 * The published price list. Amounts are kronor excluding VAT.
 *
 * Every public sentence about money is built here so the homepage, the
 * registration page, the terms, the mails, and the billing page agree.
 */
final class Pricing
{
    public static function trialDays(): int
    {
        return max(1, (int) config('bagspar.trial_days', 14));
    }

    public static function monthlyKr(): int
    {
        return (int) config('bagspar.pricing.monthly_kr', 1490);
    }

    public static function yearlyKr(): int
    {
        return (int) config('bagspar.pricing.yearly_kr', 14900);
    }

    public static function includedSeats(): int
    {
        return max(1, (int) config('bagspar.pricing.included_seats', 3));
    }

    public static function extraSeatKr(): int
    {
        return (int) config('bagspar.pricing.extra_seat_kr', 349);
    }

    public static function invoiceDays(): int
    {
        return max(1, (int) config('bagspar.pricing.invoice_days', 30));
    }

    /**
     * Months a workshop gets for free by paying the year in advance.
     */
    public static function yearlyFreeMonths(): int
    {
        $monthly = self::monthlyKr();

        if ($monthly <= 0) {
            return 0;
        }

        return max(0, 12 - (int) ceil(self::yearlyKr() / $monthly));
    }

    /**
     * Kronor with a space as thousands separator: 1 490.
     */
    public static function kr(int $amount): string
    {
        return number_format($amount, 0, ',', ' ');
    }

    public static function monthlyLabel(): string
    {
        return self::kr(self::monthlyKr()).' kr/mån';
    }

    public static function yearlyLabel(): string
    {
        return self::kr(self::yearlyKr()).' kr/år';
    }

    public static function extraSeatLabel(): string
    {
        return self::kr(self::extraSeatKr()).' kr/mån';
    }

    public static function trialHeadline(): string
    {
        return 'Prova gratis i '.self::trialDays().' dagar';
    }

    public static function seatsSentence(): string
    {
        return self::includedSeatsWords().' användare ingår, varje ytterligare användare kostar '.self::extraSeatLabel().'.';
    }

    /**
     * One sentence for the hero, the registration page, and the mails.
     */
    public static function offerSentence(): string
    {
        return 'Därefter '.self::monthlyLabel().' exkl. moms per verkstad. '
            .self::includedSeatsWords().' användare ingår. Faktura varje månad, ingen bindningstid.';
    }

    public static function yearlySentence(): string
    {
        $freeMonths = self::yearlyFreeMonths();
        $sentence = 'Betala per år: '.self::yearlyLabel().' exkl. moms';

        if ($freeMonths > 0) {
            $sentence .= ', vilket motsvarar '.self::monthWords($freeMonths).' gratis';
        }

        return $sentence.'.';
    }

    public static function invoiceSentence(): string
    {
        return 'Vi skickar faktura med '.self::invoiceDays().' dagars betalningsvillkor. Inget kort behövs.';
    }

    private static function includedSeatsWords(): string
    {
        return match (self::includedSeats()) {
            1 => 'En',
            2 => 'Två',
            3 => 'Tre',
            4 => 'Fyra',
            5 => 'Fem',
            default => (string) self::includedSeats(),
        };
    }

    private static function monthWords(int $months): string
    {
        return match ($months) {
            1 => 'en månad',
            2 => 'två månader',
            3 => 'tre månader',
            default => $months.' månader',
        };
    }
}
