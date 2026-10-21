Ett nytt verkstadskonto har bekräftat sin e-postadress.

Verkstad: {{ $workshop->name }} (konto {{ $workshop->getKey() }})
Namn: {{ $owner->name }}
E-post: {{ $owner->email }}
Registrerad: {{ $registeredAt ?? '–' }}
@if ($adminUrl)

Se kontot under {{ $adminUrl }}
@endif
