@extends('layouts.app')

@section('title', 'Integritet — Bågspår')
@section('meta_description', 'Så hanterar Bågspår uppgifter som lämnas i intresseanmälan för pilotprojektet.')

@section('content')
<main id="main" class="privacy-page page-shell">
    <a class="text-link privacy-page__back" href="{{ route('home') }}">← Tillbaka till Bågspår</a>
    <p class="eyebrow">INTEGRITET</p>
    <h1 class="display-heading">Så hanterar vi din intresseanmälan.</h1>
    <div class="privacy-page__body body-copy">
        <p>Bågspår är under utveckling. Om du anmäler intresse samlar vi in företagets eller verkstadens namn, din e-postadress, det meddelande du väljer att skriva, ditt godkännande till kontakt och tidpunkten då anmälan skickades.</p>

        <h2>Varför vi använder uppgifterna</h2>
        <p>Uppgifterna används för att hantera din intresseanmälan, förstå verkstädernas arbetssätt och kontakta dig om ett möjligt pilotprojekt. Vi säljer inte uppgifterna.</p>

        <h2>Var uppgifterna hanteras</h2>
        <p>Uppgifterna sparas så att vi kan följa upp din intresseanmälan. Vi använder också uppgifterna i en intern notis när en ny anmälan kommer in.</p>

        <h2>Radera eller fråga om dina uppgifter</h2>
        <p>Kontakta oss om du vill att vi tar bort din intresseanmälan eller har en fråga om uppgifterna. Skriv till <a href="mailto:{{ config('bagspar.lead_notification_email') }}">{{ config('bagspar.lead_notification_email') }}</a> så hanterar vi din begäran.</p>

        <h2>Cookies och analys</h2>
        <p>Webbplatsen använder inga analysverktyg eller spårningscookies i nuläget.</p>

        <p class="privacy-page__updated">Senast uppdaterad 7 oktober 2026.</p>
    </div>
</main>
@endsection
