<p>Ett nytt meddelande har sparats på Bågspår.</p>

<p><strong>Namn:</strong> {{ $contactMessage->name }}</p>
@if (filled($contactMessage->company))
    <p><strong>Företag:</strong> {{ $contactMessage->company }}</p>
@endif
<p><strong>E-post:</strong> <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
@if (filled($contactMessage->phone))
    <p><strong>Telefon:</strong> {{ $contactMessage->phone }}</p>
@endif
<p><strong>Meddelande:</strong><br>{{ $contactMessage->message }}</p>
<p>Skickat: {{ $contactMessage->created_at?->timezone('Europe/Stockholm')->format('Y-m-d H:i:s') }}</p>
