<p>Ett nytt verkstadskonto har bekräftat sin e-postadress.</p>

<table cellpadding="4" cellspacing="0" border="0">
    <tr><td><strong>Verkstad</strong></td><td>{{ $workshop->name }} (konto {{ $workshop->getKey() }})</td></tr>
    <tr><td><strong>Namn</strong></td><td>{{ $owner->name }}</td></tr>
    <tr><td><strong>E-post</strong></td><td><a href="mailto:{{ $owner->email }}">{{ $owner->email }}</a></td></tr>
    <tr><td><strong>Registrerad</strong></td><td>{{ $registeredAt ?? '–' }}</td></tr>
</table>

@if ($adminUrl)
    <p>Se kontot under <a href="{{ $adminUrl }}">{{ $adminUrl }}</a>.</p>
@endif
