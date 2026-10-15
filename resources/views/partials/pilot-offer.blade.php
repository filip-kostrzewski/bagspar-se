{{-- Pilot offer slot. Headline: config bagspar.pilot_offer (BAGSPAR_PILOT_OFFER when set, otherwise the default). The headline is omitted when that value is empty. Price and start lines stay visible. --}}
<div class="pilot-offer" data-pilot-offer>
    @if (filled(config('bagspar.pilot_offer')))
        <p class="pilot-offer__headline">{{ config('bagspar.pilot_offer') }}</p>
    @endif
    <p class="pilot-offer__price">{{ config('bagspar.pilot_price_line') }}</p>
    <p class="pilot-offer__start">{{ config('bagspar.pilot_start_line') }}</p>
</div>
