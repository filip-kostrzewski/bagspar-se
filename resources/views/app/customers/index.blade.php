@extends('layouts.workshop')

@section('title', 'Kunder — Bågspår')

@section('content')
    <div class="workspace-page">
        <div class="workspace-heading-row">
            <div>
                <p class="workshop-eyebrow">{{ $workshop->name }}</p>
                <h1 class="workshop-heading" id="page-title">Kunder</h1>
                <p class="workspace-lede">En kund eller BRF kan höra till flera projekt.</p>
            </div>
            <a class="workshop-button workshop-button--primary" href="{{ route('app.customers.create') }}">Ny kund</a>
        </div>

        @if ($customers->isEmpty())
            <p>Inga kunder är inlagda ännu. En kund skapas också när du skriver ett kundnamn på ett projekt.</p>
        @else
            <ul class="staff-list">
                @foreach ($customers as $customer)
                    <li class="staff-list__item">
                        <div>
                            <a class="workshop-text-link" href="{{ route('app.customers.edit', $customer) }}">{{ $customer->name }}</a>
                            @php
                                $customerDetails = collect([$customer->contact_name, $customer->email, $customer->address])->filter();
                            @endphp
                            <p class="staff-list__meta">
                                @if ($customerDetails->isEmpty() && ! $customer->phone)
                                    Inga kontaktuppgifter
                                @else
                                    {{ $customerDetails->join(' · ') }}
                                    @if ($customer->phone)
                                        @if ($customerDetails->isNotEmpty()) · @endif
                                        @include('partials.workshop.phone-link', ['phone' => $customer->phone, 'href' => $customer->phoneHref()])
                                    @endif
                                @endif
                                · {{ $customer->projects_count }} projekt
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
