<p>Hej,</p>

<p>Vi har tagit emot anmälan för {{ $lead->company }}.</p>

<p>Anmälan reserverar en pilotplats. {{ config('bagspar.pilot_follow_up') }}</p>

<p>Exempelarbetskortet är öppet redan nu: <a href="{{ route('demo') }}">{{ route('demo') }}</a>. Ett eget konto ingår i piloten.</p>

<p>{{ config('bagspar.pilot_offer') }}. Därefter {{ config('bagspar.pilot_price_line') }} Anmälan innebär ingen betalning.</p>

@if (filled(config('bagspar.contact_email')))
    <p>Vill du att vi raderar uppgifterna, skriv till <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>.</p>
@endif

<p>Bågspår<br>Prodi Digital AB</p>
