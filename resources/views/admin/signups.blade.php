@extends('layouts.app')

@section('title', 'Anmälningar — Bågspår')
@section('meta_description', 'Lista över verkstäder som anmält sig till Bågspår.')
@section('robots', 'noindex, nofollow')

@section('content')
<main id="main" class="privacy-page page-shell admin-signups">
    <p class="eyebrow">ADMIN</p>
    <h1 class="display-heading">Anmälningar</h1>
    <div class="admin-signups__actions">
        <p>{{ $signups->count() === 1 ? '1 anmälan' : $signups->count().' anmälningar' }}</p>
        <a class="button button--small" href="{{ route('admin.signups.export') }}">Ladda ner CSV</a>
    </div>

    @if ($signups->isEmpty())
        <p class="admin-signups__empty">Inga anmälningar ännu.</p>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Företag</th>
                        <th scope="col">E-post</th>
                        <th scope="col">Datum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($signups as $signup)
                        <tr>
                            <td>{{ $signup['company'] }}</td>
                            <td>{{ $signup['email'] }}</td>
                            <td>{{ $signup['submitted_at'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</main>
@endsection
