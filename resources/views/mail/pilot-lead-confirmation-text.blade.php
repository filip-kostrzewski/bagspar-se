Hej,

Vi har tagit emot anmälan för {{ $lead->company }}.

Anmälan reserverar en pilotplats. {{ config('bagspar.pilot_follow_up') }}

Exempelarbetskortet är öppet redan nu: {{ route('demo') }}
Ett eget konto ingår i piloten.

{{ config('bagspar.pilot_offer') }}. Därefter {{ config('bagspar.pilot_price_line') }} Anmälan innebär ingen betalning.

@if (filled(config('bagspar.contact_email')))
Vill du att vi raderar uppgifterna, skriv till {{ config('bagspar.contact_email') }}.

@endif
Bågspår
Prodi Digital AB
