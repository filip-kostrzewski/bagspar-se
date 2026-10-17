@extends('layouts.workshop')

@section('title', 'Abonnemang — Bågspår')

@php
    use App\Models\Workshop;
    use App\Support\Pricing;
    use App\Support\SwedishDate;

    $state = $workshop->accessState();
    $endsAt = $workshop->accessEndsAt();
    $daysLeft = $workshop->accessDaysLeft();
    $extraSeats = $workshop->extraSeatCount();
    $selectedPlan = is_string(old('plan')) ? old('plan') : ($workshop->subscription_plan ?? Workshop::PLAN_MONTHLY);
@endphp

@section('content')
    <div class="workspace-page billing-page">
        <nav class="workshop-breadcrumbs" aria-label="Brödsmulor">
            <a href="{{ route('app.dashboard') }}">Projekt</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Abonnemang</span>
        </nav>

        <section class="settings-intro" aria-labelledby="page-title">
            <p class="workshop-eyebrow">{{ $workshop->name }}</p>
            <h1 class="workshop-heading" id="page-title">Abonnemang</h1>
            @if ($state === Workshop::ACCESS_LOCKED)
                <p class="workspace-lede">Provperioden är slut och kontot är låst. Projekt, bågar och bilder finns kvar och öppnas igen så snart abonnemanget är beställt.</p>
            @elseif ($state === Workshop::ACCESS_TRIAL)
                <p class="workspace-lede">Provperioden gäller till {{ SwedishDate::formatLocal($endsAt) }}{{ $daysLeft > 0 ? ' – '.$daysLeft.' '.($daysLeft === 1 ? 'dag' : 'dagar').' kvar' : '' }}. Beställ abonnemanget när du vill, så fortsätter kontot utan avbrott.</p>
            @elseif ($state === Workshop::ACCESS_ORDERED)
                <p class="workspace-lede">{{ $workshop->planLabel() }} är beställt. Fakturan går till {{ $workshop->billing_email }}, och kontot är öppet under tiden.</p>
            @else
                <p class="workspace-lede">{{ $workshop->planLabel() }} betalt till {{ SwedishDate::formatLocal($workshop->paid_until) }}. Nästa faktura kommer till {{ $workshop->billing_email }}.</p>
            @endif
        </section>

        <dl class="billing-summary" aria-label="Kontots status">
            <div>
                <dt>Status</dt>
                <dd>
                    @switch($state)
                        @case(Workshop::ACCESS_LOCKED) Låst @break
                        @case(Workshop::ACCESS_TRIAL) Provperiod @break
                        @case(Workshop::ACCESS_ORDERED) Beställt, väntar på faktura @break
                        @default Aktivt
                    @endswitch
                </dd>
            </div>
            <div>
                <dt>Åtkomst till</dt>
                <dd>{{ $endsAt !== null ? SwedishDate::formatLocal($endsAt) : '–' }}</dd>
            </div>
            <div>
                <dt>Användare</dt>
                <dd>{{ $seatCount }} av {{ Pricing::includedSeats() }} som ingår{{ $extraSeats > 0 ? ' · '.$extraSeats.' extra' : '' }}{{ $pendingInvitations > 0 ? ' · '.$pendingInvitations.' inbjudna' : '' }}</dd>
            </div>
            <div>
                <dt>Pris för er</dt>
                <dd>{{ Pricing::kr($workshop->monthlyPriceKr()) }} kr/mån eller {{ Pricing::kr($workshop->yearlyPriceKr()) }} kr/år, exkl. moms</dd>
            </div>
        </dl>

        @if ($errors->any())
            <div class="workshop-error-summary" role="alert" tabindex="-1" data-workshop-focus>
                <strong>Kontrollera de markerade uppgifterna.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($canManageBilling)
            <section class="billing-order" aria-labelledby="order-title">
                <p class="workshop-eyebrow">{{ $workshop->hasOrderedSubscription() ? 'ÄNDRA' : 'BESTÄLL' }}</p>
                <h2 class="workshop-section-heading" id="order-title">{{ $workshop->hasOrderedSubscription() ? 'Ändra abonnemanget' : 'Beställ abonnemang' }}</h2>
                <p class="staff-panel__intro">{{ Pricing::monthlyLabel() }} exkl. moms per verkstad. {{ Pricing::seatsSentence() }} {{ Pricing::invoiceSentence() }}</p>

                <form class="workshop-form settings-form" method="POST" action="{{ route('app.billing.store') }}">
                    @csrf

                    <fieldset class="billing-plans">
                        <legend>Betalningsperiod</legend>
                        <label class="billing-plan @if ($selectedPlan === Workshop::PLAN_MONTHLY) is-selected @endif" for="plan-monthly">
                            <input id="plan-monthly" name="plan" type="radio" value="{{ Workshop::PLAN_MONTHLY }}" @checked($selectedPlan === Workshop::PLAN_MONTHLY) required>
                            <span>
                                <strong>Per månad · {{ Pricing::kr($workshop->monthlyPriceKr()) }} kr/mån</strong>
                                <small>Faktureras månadsvis i förskott. Ingen bindningstid.</small>
                            </span>
                        </label>
                        <label class="billing-plan @if ($selectedPlan === Workshop::PLAN_YEARLY) is-selected @endif" for="plan-yearly">
                            <input id="plan-yearly" name="plan" type="radio" value="{{ Workshop::PLAN_YEARLY }}" @checked($selectedPlan === Workshop::PLAN_YEARLY)>
                            <span>
                                <strong>Per år · {{ Pricing::kr($workshop->yearlyPriceKr()) }} kr/år</strong>
                                <small>Faktureras för tolv månader i förskott. {{ Pricing::yearlyFreeMonths() > 0 ? 'Motsvarar '.Pricing::yearlyFreeMonths().' månader gratis.' : '' }}</small>
                            </span>
                        </label>
                        @error('plan')
                            <p class="form-field__error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="form-field">
                        <label for="organization_number">Organisationsnummer</label>
                        <input id="organization_number" name="organization_number" type="text" value="{{ is_string(old('organization_number')) ? old('organization_number') : ($workshop->organization_number ?? '') }}" inputmode="numeric" maxlength="11" placeholder="559214-9370" required @if ($errors->has('organization_number')) aria-invalid="true" aria-describedby="organization-number-error" @endif>
                        @error('organization_number')
                            <p class="form-field__error" id="organization-number-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="billing_email">Fakturan skickas till</label>
                        <input id="billing_email" name="billing_email" type="email" value="{{ is_string(old('billing_email')) ? old('billing_email') : ($workshop->billing_email ?? $workshop->contact_email ?? $owner?->email ?? '') }}" maxlength="254" inputmode="email" autocomplete="email" required @if ($errors->has('billing_email')) aria-invalid="true" aria-describedby="billing-email-error" @endif>
                        @error('billing_email')
                            <p class="form-field__error" id="billing-email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="billing_reference">Er referens <span class="form-optional">Valfritt</span></label>
                        <input id="billing_reference" name="billing_reference" type="text" value="{{ is_string(old('billing_reference')) ? old('billing_reference') : ($workshop->billing_reference ?? '') }}" maxlength="80" @if ($errors->has('billing_reference')) aria-invalid="true" aria-describedby="billing-reference-error" @endif>
                        <p class="form-field__hint">Visas på fakturan, till exempel ett namn eller ett projektnummer.</p>
                        @error('billing_reference')
                            <p class="form-field__error" id="billing-reference-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="workshop-checkbox" for="terms">
                        <input id="terms" name="terms" type="checkbox" value="1" required @checked(old('terms')) @if ($errors->has('terms')) aria-invalid="true" aria-describedby="terms-error" @endif>
                        <span>Jag godkänner <a class="workshop-text-link" href="{{ route('terms') }}" target="_blank" rel="noopener">villkoren</a> och beställer för verkstadens räkning.</span>
                    </label>
                    @error('terms')
                        <p class="form-field__error" id="terms-error">{{ $message }}</p>
                    @enderror

                    <button class="workshop-button workshop-button--primary" type="submit">{{ $workshop->hasOrderedSubscription() ? 'Spara ändringen' : 'Beställ abonnemang' }}</button>
                </form>

                <p class="billing-note">Uppsägning: skriv till <a class="workshop-text-link" href="mailto:{{ config('bagspar.contact_email') }}">{{ config('bagspar.contact_email') }}</a>, så avslutas abonnemanget vid slutet av den betalda perioden. Du kan alltid <a class="workshop-text-link" href="{{ route('app.export') }}">ladda ner verkstadens data som CSV</a>.</p>
            </section>
        @else
            <section class="billing-order" aria-labelledby="member-title">
                <h2 class="workshop-section-heading" id="member-title">Bara ägaren kan beställa</h2>
                <p class="staff-panel__intro">Be {{ $owner?->name ?? 'verkstadens ägare' }} att logga in och beställa abonnemanget under Abonnemang. Projekt och bilder finns kvar.</p>
            </section>
        @endif
    </div>
@endsection
