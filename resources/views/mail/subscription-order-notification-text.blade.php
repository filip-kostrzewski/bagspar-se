{{ $isFirstOrder ? 'Ny beställning av abonnemang.' : 'Ett abonnemang har ändrats.' }}

Verkstad: {{ $workshop->name }} (konto {{ $workshop->getKey() }})
Organisationsnummer: {{ $workshop->organization_number }}
Plan: {{ $planLabel }}
Användare: {{ $seatCount }} ({{ $includedSeats }} ingår, {{ $extraSeats }} extra)
Belopp: {{ $amountLabel }}
Fakturamejl: {{ $workshop->billing_email }}
Referens: {{ $workshop->billing_reference ?? '–' }}
Adress: {{ $workshop->address ?? '–' }}
Beställd av: {{ $actor?->name ?? '–' }} {{ $actor?->email ? '('.$actor->email.')' : '' }}
Åtkomst till: {{ $workshop->accessEndsAt()?->timezone('Europe/Stockholm')->format('Y-m-d') ?? '–' }}

Skapa fakturan i Fortnox och sätt sedan betalt till-datum under {{ route('admin.workshops') }}
