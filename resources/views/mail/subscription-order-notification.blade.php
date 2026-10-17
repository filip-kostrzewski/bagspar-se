<p>{{ $isFirstOrder ? 'Ny beställning av abonnemang.' : 'Ett abonnemang har ändrats.' }}</p>

<table cellpadding="4" cellspacing="0" border="0">
    <tr><td><strong>Verkstad</strong></td><td>{{ $workshop->name }} (konto {{ $workshop->getKey() }})</td></tr>
    <tr><td><strong>Organisationsnummer</strong></td><td>{{ $workshop->organization_number }}</td></tr>
    <tr><td><strong>Plan</strong></td><td>{{ $planLabel }}</td></tr>
    <tr><td><strong>Användare</strong></td><td>{{ $seatCount }} ({{ $includedSeats }} ingår, {{ $extraSeats }} extra)</td></tr>
    <tr><td><strong>Belopp</strong></td><td>{{ $amountLabel }}</td></tr>
    <tr><td><strong>Fakturamejl</strong></td><td>{{ $workshop->billing_email }}</td></tr>
    <tr><td><strong>Referens</strong></td><td>{{ $workshop->billing_reference ?? '–' }}</td></tr>
    <tr><td><strong>Adress</strong></td><td>{{ $workshop->address ?? '–' }}</td></tr>
    <tr><td><strong>Beställd av</strong></td><td>{{ $actor?->name ?? '–' }} {{ $actor?->email ? '('.$actor->email.')' : '' }}</td></tr>
    <tr><td><strong>Åtkomst till</strong></td><td>{{ $workshop->accessEndsAt()?->timezone('Europe/Stockholm')->format('Y-m-d') ?? '–' }}</td></tr>
</table>

<p>Skapa fakturan i Fortnox och sätt sedan betalt till-datum under <a href="{{ route('admin.workshops') }}">{{ route('admin.workshops') }}</a>.</p>
