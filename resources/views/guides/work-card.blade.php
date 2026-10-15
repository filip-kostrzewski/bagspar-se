@extends('layouts.app')

@section('title', 'Arbetskort för fönsterbågar — Bågspår')
@section('meta_description', 'Ett arbetskort per båge, med nummer, plats, QR-kod, arbetssteg, anteckningar och bilder. Kortet öppnas i mobilen.')

@section('content')
<main id="main" class="guide-page page-shell">
    <a class="text-link guide-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">ARBETSKORT</p>
    <h1 class="display-heading">Ett kort per båge</h1>
    <div class="guide-page__body body-copy">
        <p>Arbetskortet är bågens egen lapp, i mobilen. Den som tar emot bågen, den som skrapar och den som sätter tillbaka den läser samma kort. Inget flyttas över till en annan lista i slutet av dagen.</p>

        <h2>Det som ligger på kortet</h2>
        <ul>
            <li>nummer och plats, till exempel B-04, våning 2, kök, södra</li>
            <li>en QR-kod som öppnar just det kortet</li>
            <li>stegen inlämnad, skrapning, kittning, målning och klar</li>
            <li>anteckningar, till exempel ”glaset tas ur” eller ”gångjärn saknas”</li>
            <li>bilder från öppningen och från bänken</li>
        </ul>

        <h2>Så används det i verkstaden</h2>
        <p>Den som tar emot bågen skapar kortet och fäster QR-koden på märkningen. Nästa person skannar, ser vad som är gjort och flyttar steget. Anteckningen skrivs där arbetet sker, inte i en pärm på kontoret.</p>
        <p>När steget står på kittning vet den som målar att kittet är lagt, och kan läsa om något glas skulle sparas. När steget står på klar är bågen redo att paras ihop med öppningen som står på kortet.</p>
        <p>Plats, skick och bilder hör till <a href="{{ route('guides.documentation') }}">dokumentationen av fönsterrenoveringen</a>. Numret på kortet ska vara samma som på <a href="{{ route('guides.marking') }}">märkningen av bågen</a>.</p>

        <h2>När täckningen är svag</h2>
        <p>Bågspår är byggt offline-först. Arbetskortet går att öppna och uppdatera när signalen är svag. Ändringen synkas när anslutningen kommer tillbaka, så steget och anteckningen inte blir kvar bara i en telefon.</p>

        @include('partials.guide-cta')
    </div>
</main>
@endsection
