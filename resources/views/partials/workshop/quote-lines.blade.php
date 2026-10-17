<table class="material-sheet quote-lines">
    <caption>Rader</caption>
    <thead>
        <tr>
            <th scope="col">Beskrivning</th>
            <th scope="col" class="quote-lines__num">Antal</th>
            <th scope="col" class="quote-lines__num">À-pris</th>
            <th scope="col" class="quote-lines__num">Summa</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($quote->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="quote-lines__num">{{ $line->quantity }}</td>
                <td class="quote-lines__num">{{ \App\Support\Kronor::format($line->unit_price_ore) }}</td>
                <td class="quote-lines__num">{{ \App\Support\Kronor::format($line->lineTotalOre()) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('partials.workshop.quote-totals', ['quote' => $quote])
