@php
    $cookieBannerText = [
        'sv' => [
            'title' => 'Cookies',
            'body' => 'Vi mäter besök med Google Analytics 4 bara om du godkänner.',
            'policy' => 'Integritetspolicy',
            'accept' => 'Godkänn',
            'deny' => 'Neka',
            'group' => 'Välj om mätning får användas',
        ],
    ];
    $cookieBanner = $cookieBannerText[$pageLanguage] ?? $cookieBannerText['sv'];
@endphp
<div id="cookie-installningar" class="cookie-banner" role="region" aria-labelledby="cookie-banner-title" tabindex="-1" data-cookie-banner>
    <div class="cookie-banner__inner page-shell">
        <div class="cookie-banner__copy">
            <p id="cookie-banner-title" class="cookie-banner__title">{{ $cookieBanner['title'] }}</p>
            <p class="cookie-banner__text">{{ $cookieBanner['body'] }}</p>
            <a class="cookie-banner__policy" href="{{ route('privacy') }}">{{ $cookieBanner['policy'] }}</a>
        </div>
        <div class="cookie-banner__choices" role="group" aria-label="{{ $cookieBanner['group'] }}">
            <button type="button" class="cookie-banner__choice" data-cookie-choice="granted">{{ $cookieBanner['accept'] }}</button>
            <button type="button" class="cookie-banner__choice" data-cookie-choice="denied">{{ $cookieBanner['deny'] }}</button>
        </div>
    </div>
</div>
<script src="{{ asset('js/cookies.js') }}?v={{ config('bagspar.asset_version') }}" defer></script>
