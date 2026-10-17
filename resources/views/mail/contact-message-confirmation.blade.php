<p>Hej {{ $contactMessage->name }},</p>

<p>Vi har tagit emot ditt meddelande och återkommer så snart vi kan.</p>

@if (filled(config('bagspar.contact_email')))
    <p>Vill du att vi raderar uppgifterna, skriv till <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>.</p>
@endif

<p>Bågspår<br>Prodi Digital AB</p>
