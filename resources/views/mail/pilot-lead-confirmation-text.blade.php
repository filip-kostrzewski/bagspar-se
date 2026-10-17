Hej,

Vi har tagit emot din förfrågan för {{ $lead->company }}.

Det här formuläret skapar inget konto. Frågor? Kontakta oss på {{ config('bagspar.contact_email') }}.

Se ett exempel med testuppgifter: {{ route('demo') }}

{{ \App\Support\Pricing::trialHeadline() }}. {{ \App\Support\Pricing::offerSentence() }} {{ \App\Support\Pricing::yearlySentence() }}

@if (filled(config('bagspar.contact_email')))
Vill du att vi raderar uppgifterna, skriv till {{ config('bagspar.contact_email') }}.

@endif
Bågspår
Prodi Digital AB
