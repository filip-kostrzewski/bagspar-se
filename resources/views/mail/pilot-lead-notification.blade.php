<p>En ny intresseanmälan har sparats på Bågspår.</p>

<p><strong>Företag / verkstad:</strong> {{ $lead->company }}</p>
<p><strong>E-post:</strong> <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>

@if ($lead->message)
    <p><strong>Meddelande:</strong><br>{{ $lead->message }}</p>
@endif

<p>Samtycke registrerat: {{ $lead->consented_at->format('Y-m-d H:i:s') }}</p>
