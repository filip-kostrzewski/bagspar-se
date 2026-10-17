Hej {{ $contactMessage->name }},

Vi har tagit emot ditt meddelande och återkommer så snart vi kan.

@if (filled(config('bagspar.contact_email')))
Vill du att vi raderar uppgifterna, skriv till {{ config('bagspar.contact_email') }}.
@endif

Bågspår
Prodi Digital AB
