@extends('layouts.app')

@section('title', 'Guider för fönsterverkstaden — Bågspår')
@section('meta_description', 'Praktiska guider om märkning, arbetskort, bilder före och efter och projektrapport för fönsterverkstäder.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">GUIDER</p>
    <h1 class="display-heading">Guider för verkstaden</h1>
    <div class="guide-page__body body-copy">
        <p>Guiderna beskriver hur en båge märks, hur arbetskortet följer den genom verkstaden och hur bilder och steg kan samlas i en projektrapport. De är praktiska rutiner. De lovar inte hur ett arbete blir.</p>

        <h2>Dokumentera en fönsterrenovering</h2>
        <p>Märkning, bilder före och efter per båge, arbetsstegen och projektrapporten till kunden eller föreningen, i en och samma genomgång.</p>
        <p><a href="{{ route('guides.document-renovation') }}">Läs guiden om att dokumentera en fönsterrenovering</a></p>

        <h2>Dokumentation vid öppningen</h2>
        <p>Vilka uppgifter som ska skrivas innan bågen lossas: plats, skick, glas, material, bilder och det första steget.</p>
        <p><a href="{{ route('guides.documentation') }}">Läs vad som ska följa med bågen</a></p>

        <h2>Arbetskort</h2>
        <p>Ett kort per båge, med plats, anteckningar, bilder, QR-etikett och stegen från mottagen till klar.</p>
        <p><a href="{{ route('guides.work-card') }}">Läs om arbetskort för fönsterbågar</a></p>

        <h2>Märkning</h2>
        <p>Hur numret skrivs så att samma båge kommer tillbaka till samma öppning, och hur QR-etiketten förhåller sig till lappen.</p>
        <p><a href="{{ route('guides.marking') }}">Läs om märkning av fönsterbågar</a></p>
    </div>
</main>
@endsection

@include('partials.article-schema', [
    'headline' => 'Guider för verkstaden',
    'description' => 'Praktiska guider om märkning, arbetskort, bilder före och efter och projektrapport för fönsterverkstäder.',
    'pageUrl' => route('guides.index'),
])
