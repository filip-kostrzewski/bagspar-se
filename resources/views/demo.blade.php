@extends('layouts.app')

@section('title', 'Exempel på arbetskort — Bågspår')
@section('meta_description', 'Prova Bågspårs exempel med testuppgifter: ett arbetskort med steg, anteckningar, bilder och en illustrativ QR-kod.')

@section('content')
<main id="main" class="demo-page page-shell">
    <div class="demo-page__layout">
        <div class="demo-page__intro-wrap">
            <h1 class="section-heading">Ett arbetskort.</h1>
            <p class="demo-page__intro">Allt här är påhittat. Prova stegen, anteckningarna och bilderna med testuppgifter, även utan täckning. Ditt verkstadskonto kräver internetanslutning och sparar uppgifterna på servern. Där får varje arbetskort en utskrivbar QR-etikett som öppnar det privata kortet efter inloggning.</p>
        </div>

        <article class="job-card job-card--wide" data-demo-card data-sync-url="{{ route('demo.sync.store', absolute: false) }}" aria-labelledby="job-card-title">
            <header class="job-card__header">
                <div>
                    <h2 id="job-card-title">2-KÖK-F1-B2</h2>
                    <p>Exempelverkstaden</p>
                    <p>Villa Ek · Exempelvägen 12</p>
                    <p>Våning 2 · Kök · vänster innerbåge</p>
                    <p>6 rutor · 68 × 124 cm</p>
                </div>
                <a class="job-card__qr" href="{{ route('demo') }}">
                    <img src="{{ asset('images/demo-qr.svg') }}" width="96" height="96" alt="Illustrativ QR-kod för 2-KÖK-F1-B2. Öppnar det här exemplet med testuppgifter.">
                    <span>QR-kod · exempel</span>
                </a>
            </header>

            <div class="job-card__split">
            <div class="job-card__primary">
            <p class="job-card__status" data-status-label>Kittning</p>
            <p class="job-card__sync" data-sync-status role="status" aria-live="polite">Exempel med testuppgifter. Kan öppnas utan täckning.</p>

            <ol class="job-steps">
                <li>
                    <button type="button" data-step="inlämnad" class="is-done">
                        <span class="job-steps__mark" aria-hidden="true"></span>
                        <span>Inlämnad</span>
                    </button>
                </li>
                <li>
                    <button type="button" data-step="skrapning" class="is-done">
                        <span class="job-steps__mark" aria-hidden="true"></span>
                        <span>Skrapning</span>
                    </button>
                </li>
                <li>
                    <button type="button" data-step="kittning" aria-current="step">
                        <span class="job-steps__mark" aria-hidden="true"></span>
                        <span>Kittning</span>
                    </button>
                </li>
                <li>
                    <button type="button" data-step="målning">
                        <span class="job-steps__mark" aria-hidden="true"></span>
                        <span>Målning</span>
                    </button>
                </li>
                <li>
                    <button type="button" data-step="klar">
                        <span class="job-steps__mark" aria-hidden="true"></span>
                        <span>Klar</span>
                    </button>
                </li>
            </ol>
            </div>

            <section class="job-photos" aria-labelledby="job-photos-title">
                <h3 id="job-photos-title">Illustrationer</h3>
                <p class="job-photos__note">Teckningarna är illustrationer, inte fotografier.</p>
                <div class="job-photos__grid">
                    <div class="job-photo" data-photo aria-expanded="false">
                        <button type="button" class="job-photo__open" data-photo-open>
                            <span class="job-photo__badge">Illustration</span>
                            <svg viewBox="0 0 120 180" width="120" height="180" role="img" aria-labelledby="photo-before-title">
                                <title id="photo-before-title">Illustration, före</title>
                                <rect x="6" y="6" width="108" height="168" rx="2" fill="#efe6d4" stroke="#17313b" stroke-width="8"/>
                                <rect x="18" y="18" width="36" height="40" fill="#d5e2e4"/>
                                <rect x="66" y="18" width="36" height="40" fill="#d7e4e3"/>
                                <rect x="18" y="70" width="36" height="40" fill="#c9dadd"/>
                                <rect x="66" y="70" width="36" height="40" fill="#d5e2e4"/>
                                <rect x="18" y="122" width="36" height="40" fill="#d0c2a4"/>
                                <rect x="66" y="122" width="36" height="40" fill="#d5e2e4"/>
                                <path d="M22 148c8 6 18 4 28-2" fill="none" stroke="#8a6a45" stroke-width="2"/>
                            </svg>
                            <span class="job-photo__caption">Illustration</span>
                            <span class="job-photo__label">Före. Inte ett fotografi.</span>
                        </button>
                        <button type="button" class="job-photo__close button button--small" data-photo-close>Stäng</button>
                    </div>
                    <div class="job-photo" data-photo aria-expanded="false">
                        <button type="button" class="job-photo__open" data-photo-open>
                            <span class="job-photo__badge">Illustration</span>
                            <svg viewBox="0 0 120 180" width="120" height="180" role="img" aria-labelledby="photo-scraped-title">
                                <title id="photo-scraped-title">Illustration, efter skrapning</title>
                                <rect x="6" y="6" width="108" height="168" rx="2" fill="#e7d3b0" stroke="#6b4e32" stroke-width="8"/>
                                <rect x="18" y="18" width="36" height="40" fill="#d5e2e4"/>
                                <rect x="66" y="18" width="36" height="40" fill="#d7e4e3"/>
                                <rect x="18" y="70" width="36" height="40" fill="#c9dadd"/>
                                <rect x="66" y="70" width="36" height="40" fill="#d5e2e4"/>
                                <rect x="18" y="122" width="36" height="40" fill="#d5e2e4"/>
                                <rect x="66" y="122" width="36" height="40" fill="#d7e4e3"/>
                                <path d="M16 40c20 8 30-6 48 2" fill="none" stroke="#a98456" stroke-width="3"/>
                            </svg>
                            <span class="job-photo__caption">Illustration</span>
                            <span class="job-photo__label">Efter skrapning. Inte ett fotografi.</span>
                        </button>
                        <button type="button" class="job-photo__close button button--small" data-photo-close>Stäng</button>
                    </div>
                </div>
                <div class="job-photos__added" data-user-photos></div>
                <label class="job-card__file">
                    <input type="file" accept="image/*" capture="environment" data-photo-input>
                    <span class="button button--small">Lägg till bild</span>
                </label>
            </section>
            </div>

            <section class="job-notes-section" aria-labelledby="job-notes-title">
                <h3 id="job-notes-title">Anteckningar</h3>
                <ul class="job-notes" data-notes>
                    <li data-note="ex-1">
                        <p data-note-text>Undre vänstra hörnet är mjukt. Ska lagas innan kittning.</p>
                        <p class="job-notes__meta" data-note-meta>Påhittad · 12 oktober</p>
                    </li>
                    <li data-note="ex-2">
                        <p data-note-text>Linoljefärg, bruten vit, enligt beställningen.</p>
                        <p class="job-notes__meta" data-note-meta>Påhittad · 13 oktober</p>
                    </li>
                </ul>
                <form class="job-note-form" data-note-form>
                    <label for="demo-note">Ny anteckning</label>
                    <textarea id="demo-note" name="note" rows="3" maxlength="280" placeholder="Till exempel ett mått eller en färg."></textarea>
                    <button class="button button--small" type="submit">Spara på kortet</button>
                </form>
            </section>

            <button class="job-card__reset" type="button" data-reset data-reset-confirm="Återställ exemplet? Ändringar du gjort på kortet tas bort.">Återställ exemplet</button>
        </article>
    </div>

    <aside class="demo-next" aria-labelledby="demo-next-title">
        <div>
            <h2 id="demo-next-title">Börja med ett eget konto</h2>
            <p>Kortet ovan är öppet för alla. Starta en kostnadsfri pilot för din verkstad; arbetskort sparas på servern med internetanslutning.</p>
        </div>
        <a class="button" href="{{ route('register') }}">Starta gratis pilot</a>
        <p><a href="{{ route('home') }}#intresse">Vill du stämma av piloten? Kontakta oss</a></p>
    </aside>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/demo.js') }}?v={{ config('bagspar.asset_version') }}" defer></script>
@endpush
