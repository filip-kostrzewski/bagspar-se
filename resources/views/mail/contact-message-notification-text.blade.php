Ett nytt meddelande har sparats på Bågspår.

Namn: {{ $contactMessage->name }}
@if (filled($contactMessage->company))
Företag: {{ $contactMessage->company }}
@endif
E-post: {{ $contactMessage->email }}
@if (filled($contactMessage->phone))
Telefon: {{ $contactMessage->phone }}
@endif
Meddelande:
{{ $contactMessage->message }}

Skickat: {{ $contactMessage->created_at?->timezone('Europe/Stockholm')->format('Y-m-d H:i:s') }}
