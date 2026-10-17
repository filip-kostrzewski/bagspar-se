@extends('layouts.app')

@section('title', 'Verkstäder — Bågspår')
@section('meta_description', 'Lista över verkstadskonton och abonnemang i Bågspår.')
@section('robots', 'noindex, nofollow')

@php
    use App\Models\Workshop;
    use App\Support\Pricing;
    use App\Support\SwedishDate;
@endphp

@section('content')
<main id="main" class="privacy-page page-shell admin-signups">
    <p class="eyebrow">ADMIN</p>
    <h1 class="display-heading">Verkstäder</h1>
    <div class="admin-signups__actions">
        <p>{{ $workshops->count() === 1 ? '1 verkstad' : $workshops->count().' verkstäder' }} · <a href="{{ route('admin.signups') }}">Anmälningar</a></p>
    </div>

    @if (session('status'))
        <p class="form-result" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="form-result form-result--info" role="alert">
            <ul>
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($workshops->isEmpty())
        <p class="admin-signups__empty">Inga verkstäder ännu.</p>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Verkstad</th>
                        <th scope="col">Status</th>
                        <th scope="col">Användare</th>
                        <th scope="col">Beställning</th>
                        <th scope="col">Fakturering</th>
                        <th scope="col">Betalt till</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($workshops as $workshop)
                        @php
                            $state = $workshop->accessState();
                            $extraSeats = max(0, $workshop->users_count - Pricing::includedSeats());
                            $monthly = Pricing::monthlyKr() + $extraSeats * Pricing::extraSeatKr();
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $workshop->name }}</strong><br>
                                <small>#{{ $workshop->getKey() }} · {{ $workshop->organization_number ?? 'org.nr saknas' }} · skapad {{ SwedishDate::formatLocal($workshop->created_at) }}</small>
                            </td>
                            <td>
                                <span class="admin-status admin-status--{{ $state }}">
                                    @switch($state)
                                        @case(Workshop::ACCESS_LOCKED) Låst @break
                                        @case(Workshop::ACCESS_TRIAL) Prov @break
                                        @case(Workshop::ACCESS_ORDERED) Beställt @break
                                        @default Aktiv
                                    @endswitch
                                </span><br>
                                <small>till {{ SwedishDate::formatLocal($workshop->accessEndsAt()) ?: '–' }}</small>
                            </td>
                            <td>{{ $workshop->users_count }}<br><small>{{ Pricing::kr($monthly) }} kr/mån</small></td>
                            <td>
                                @if ($workshop->subscription_ordered_at !== null)
                                    {{ $workshop->planLabel() ?? '–' }}<br>
                                    <small>{{ SwedishDate::formatDateTime($workshop->subscription_ordered_at) }}</small>
                                @else
                                    –
                                @endif
                            </td>
                            <td>
                                {{ $workshop->billing_email ?? '–' }}<br>
                                <small>{{ $workshop->billing_reference ?? '' }}</small>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.workshops.update', $workshop) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="visually-hidden" for="plan-{{ $workshop->getKey() }}">Plan</label>
                                    <select id="plan-{{ $workshop->getKey() }}" name="subscription_plan">
                                        <option value="" @selected($workshop->subscription_plan === null)>Ingen plan</option>
                                        <option value="{{ Workshop::PLAN_MONTHLY }}" @selected($workshop->subscription_plan === Workshop::PLAN_MONTHLY)>Månad</option>
                                        <option value="{{ Workshop::PLAN_YEARLY }}" @selected($workshop->subscription_plan === Workshop::PLAN_YEARLY)>År</option>
                                    </select>
                                    <label class="visually-hidden" for="paid-until-{{ $workshop->getKey() }}">Betalt till</label>
                                    <input id="paid-until-{{ $workshop->getKey() }}" name="paid_until" type="date" value="{{ $workshop->paid_until?->timezone('Europe/Stockholm')->format('Y-m-d') }}">
                                    <button class="button button--small" type="submit">Spara</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</main>
@endsection
