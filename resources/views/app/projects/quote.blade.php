@extends('layouts.workshop')

@section('title', 'Offert: '.$project->name.' — Bågspår')

@section('content')
    @php
        $locked = $quote->isAccepted();
    @endphp
    <div class="workspace-page report-page quote-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.projects.show', $project) }}">{{ $project->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Offert</span>
        </nav>

        <header class="report-header">
            <div>
                <p class="workshop-eyebrow">TILL KUNDEN</p>
                <h1 class="workshop-heading" id="page-title">Offert</h1>
                <p class="quote-page__state">{{ $project->name }} ·
                    @if ($quote->isAccepted())
                        Godkänd
                    @elseif ($quote->isSent())
                        Skickad
                    @else
                        Utkast
                    @endif
                </p>
            </div>
            <div class="report-header__actions">
                <a class="workshop-button workshop-button--quiet" href="{{ route('app.projects.show', $project) }}">Till projektet</a>
                <a class="workshop-button workshop-button--secondary" href="{{ route('app.projects.quote.pdf', $project) }}">Ladda ner PDF</a>
            </div>
        </header>

        @if ($errors->any())
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                <strong>Kontrollera uppgifterna.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <aside class="basis-notice" role="note">
            <p>Priset gäller före eventuellt ROT-avdrag, som görs på fakturan enligt Skatteverkets regler.</p>
        </aside>

        @if ($locked)
            <section class="quote-accepted" aria-labelledby="quote-accepted-title">
                <h2 class="workshop-section-heading" id="quote-accepted-title">Godkänd av kunden</h2>
                <p>Offerten godkändes {{ \App\Support\SwedishDate::formatDateTime($quote->accepted_at) }} av {{ $quote->accepted_name }}. Den kan inte längre ändras.</p>
            </section>
            @if (is_string($quote->introduction) && $quote->introduction !== '')
                <p class="quote-intro">{{ $quote->introduction }}</p>
            @endif
            @include('partials.workshop.quote-lines', ['quote' => $quote])
            @if ($quote->valid_until)
                <p>Offerten gäller till {{ \App\Support\SwedishDate::format($quote->valid_until) }}.</p>
            @endif
        @else
            @if ($quote->isSent())
                <p class="quote-page__note">Offerten är skickad. Du kan fortfarande ändra den, och länken till kunden är densamma. Skicka igen om kunden ska få ett nytt mejl.</p>
            @endif

            <form class="workshop-form quote-form" id="quote-form" method="POST" action="{{ route('app.projects.quote.update', $project) }}" novalidate>
                @csrf
                @method('PATCH')
                <div class="form-field">
                    <label for="quote-introduction">Inledning (valfri)</label>
                    <textarea id="quote-introduction" name="introduction" rows="4" maxlength="5000" @if ($errors->has('introduction')) aria-invalid="true" @endif>{{ old('introduction', $quote->introduction) }}</textarea>
                </div>
                <div class="form-field">
                    <label for="quote-valid-until">Giltig till (valfri)</label>
                    <input id="quote-valid-until" name="valid_until" type="date" value="{{ old('valid_until', $quote->valid_until?->toDateString()) }}" @if ($errors->has('valid_until')) aria-invalid="true" @endif>
                </div>

                <div class="quote-lines-wrap">
                    <table class="material-sheet quote-lines quote-lines--edit">
                        <caption>Rader</caption>
                        <thead>
                            <tr>
                                <th scope="col">Beskrivning</th>
                                <th scope="col">Antal</th>
                                <th scope="col">À-pris exkl. moms (kr)</th>
                                <th scope="col">Summa exkl. moms</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $row)
                                @php
                                    $number = $index + 1;
                                    $parsed = null;
                                    try {
                                        $price = \App\Support\Kronor::parseOptional($row['unit_price']);
                                        $qty = $row['quantity'] === '' ? 1 : (int) $row['quantity'];
                                        $parsed = $price === null ? null : $qty * $price;
                                    } catch (\InvalidArgumentException) {
                                        $parsed = null;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <label class="visually-hidden" for="quote-line-{{ $index }}-description">Beskrivning, rad {{ $number }}</label>
                                        <input id="quote-line-{{ $index }}-description" name="lines[{{ $index }}][description]" type="text" maxlength="200" placeholder="Beskrivning, till exempel Renovering av båge 2-KÖK-F1-B1" value="{{ $row['description'] }}" @if ($errors->has('lines.'.$index.'.description')) aria-invalid="true" @endif>
                                    </td>
                                    <td>
                                        <label class="visually-hidden" for="quote-line-{{ $index }}-quantity">Antal, rad {{ $number }}</label>
                                        <input id="quote-line-{{ $index }}-quantity" name="lines[{{ $index }}][quantity]" type="text" inputmode="numeric" maxlength="4" placeholder="1" value="{{ $row['quantity'] }}" @if ($errors->has('lines.'.$index.'.quantity')) aria-invalid="true" @endif>
                                    </td>
                                    <td>
                                        <label class="visually-hidden" for="quote-line-{{ $index }}-price">À-pris i kronor, rad {{ $number }}</label>
                                        <input id="quote-line-{{ $index }}-price" name="lines[{{ $index }}][unit_price]" type="text" inputmode="decimal" maxlength="20" value="{{ $row['unit_price'] }}" @if ($errors->has('lines.'.$index.'.unit_price')) aria-invalid="true" @endif>
                                    </td>
                                    <td class="quote-lines__sum">{{ $parsed === null ? '' : \App\Support\Kronor::format($parsed) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="form-field__hint">Tomma rader sparas inte. Skriv priset i kronor exklusive moms, till exempel 4500 eller 4500,50.</p>
            </form>

            @include('partials.workshop.quote-totals', ['quote' => $quote])
            <p class="form-field__hint">Summorna räknas från sparade rader.</p>

            <div class="quote-actions">
                <button class="workshop-button workshop-button--primary" type="submit" form="quote-form">Spara offert</button>
                <form method="POST" action="{{ route('app.projects.quote.fill', $project) }}">
                    @csrf
                    <button class="workshop-button workshop-button--secondary" type="submit">Fyll i en rad per båge</button>
                </form>
                <form method="POST" action="{{ route('app.projects.quote.send', $project) }}">
                    @csrf
                    <button class="workshop-button workshop-button--secondary" type="submit">{{ $quote->isSent() ? 'Skicka igen till kunden' : 'Skicka till kunden' }}</button>
                </form>
            </div>
            <p class="form-field__hint">Spara först om du har ändrat något. Att fylla i rader och skicka använder den sparade offerten.</p>
        @endif
    </div>
@endsection
