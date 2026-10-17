@extends('errors.standalone')

@section('title', 'Något gick fel hos oss — Bågspår')

@section('content')
    <p class="eyebrow">500 · FELET LIGGER HOS OSS</p>
    <h1>Något gick fel hos oss</h1>
    <p>Det du gjorde var inte fel. Försök igen om en liten stund. Om det händer igen, mejla oss på <a href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a> och berätta vad du försökte göra.</p>
    <p><a class="button" href="/">Tillbaka till Bågspår</a></p>
@endsection
