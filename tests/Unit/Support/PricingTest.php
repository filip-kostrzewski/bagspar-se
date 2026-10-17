<?php

namespace Tests\Unit\Support;

use App\Support\Pricing;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function test_kronor_use_a_space_as_the_thousands_separator(): void
    {
        $this->assertSame('590', Pricing::kr(590));
        $this->assertSame('5 900', Pricing::kr(5900));
        $this->assertSame('149', Pricing::kr(149));
    }

    public function test_the_default_price_list(): void
    {
        $this->assertSame(30, Pricing::trialDays());
        $this->assertSame(590, Pricing::monthlyKr());
        $this->assertSame(5900, Pricing::yearlyKr());
        $this->assertSame(3, Pricing::includedSeats());
        $this->assertSame(149, Pricing::extraSeatKr());
        $this->assertSame(30, Pricing::invoiceDays());
        $this->assertSame('590 kr/mån', Pricing::monthlyLabel());
        $this->assertSame('5 900 kr/år', Pricing::yearlyLabel());
        $this->assertSame('149 kr/mån', Pricing::extraSeatLabel());
    }

    public function test_paying_yearly_is_worth_two_free_months(): void
    {
        $this->assertSame(2, Pricing::yearlyFreeMonths());
    }

    public function test_the_public_sentences(): void
    {
        $this->assertSame('Prova gratis i 30 dagar', Pricing::trialHeadline());
        $this->assertSame('Tre användare ingår, varje ytterligare användare kostar 149 kr/mån.', Pricing::seatsSentence());
        $this->assertSame(
            'Därefter 590 kr/mån exkl. moms per verkstad. Tre användare ingår. Faktura varje månad, ingen bindningstid.',
            Pricing::offerSentence(),
        );
        $this->assertSame(
            'Betala per år: 5 900 kr/år exkl. moms, vilket motsvarar två månader gratis.',
            Pricing::yearlySentence(),
        );
        $this->assertSame(
            'Vi skickar faktura med 30 dagars betalningsvillkor. Inget kort behövs.',
            Pricing::invoiceSentence(),
        );
    }

    public function test_the_sentences_follow_the_configuration(): void
    {
        config([
            'bagspar.trial_days' => 30,
            'bagspar.pricing.monthly_kr' => 990,
            'bagspar.pricing.included_seats' => 2,
            'bagspar.pricing.extra_seat_kr' => 199,
            'bagspar.pricing.invoice_days' => 20,
        ]);

        $this->assertSame('Prova gratis i 30 dagar', Pricing::trialHeadline());
        $this->assertSame('Två användare ingår, varje ytterligare användare kostar 199 kr/mån.', Pricing::seatsSentence());
        $this->assertStringStartsWith('Därefter 990 kr/mån exkl. moms per verkstad. Två användare ingår.', Pricing::offerSentence());
        $this->assertSame('Vi skickar faktura med 20 dagars betalningsvillkor. Inget kort behövs.', Pricing::invoiceSentence());
    }

    public function test_a_yearly_price_without_a_discount_does_not_promise_free_months(): void
    {
        config(['bagspar.pricing.yearly_kr' => 17880]);

        $this->assertSame(0, Pricing::yearlyFreeMonths());
        $this->assertSame('Betala per år: 17 880 kr/år exkl. moms.', Pricing::yearlySentence());
        $this->assertStringNotContainsString('motsvarar', Pricing::yearlySentence());
    }
}
