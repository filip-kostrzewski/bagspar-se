<p>Hej,</p>

<p>Vi har tagit emot din förfrågan för {{ $lead->company }}.</p>

<p>Det här formuläret skapar inget konto. Frågor? Kontakta oss på <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>.</p>

<p>Se ett exempel med testuppgifter: <a href="{{ route('demo') }}">{{ route('demo') }}</a>.</p>

<p>{{ config('bagspar.pilot_offer') }}. Därefter {{ config('bagspar.pilot_price_line') }}</p>

@if (filled(config('bagspar.contact_email')))
    <p>Vill du att vi raderar uppgifterna, skriv till <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>.</p>
@endif

<p>Bågspår<br>Prodi Digital AB</p>
