<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class GoogleAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private mixed $measurementId;

    private mixed $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->measurementId = config('services.google_analytics.measurement_id');
        $this->adminToken = config('bagspar.admin_token');
    }

    protected function tearDown(): void
    {
        config([
            'services.google_analytics.measurement_id' => $this->measurementId,
            'bagspar.admin_token' => $this->adminToken,
        ]);

        parent::tearDown();
    }

    public function test_public_pages_deny_consent_without_loading_the_tag_when_the_measurement_id_is_missing(): void
    {
        config(['services.google_analytics.measurement_id' => null]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<html lang="sv">', false);
        $response->assertSeeInOrder([
            'window.dataLayer = window.dataLayer || [];',
            'function gtag(){dataLayer.push(arguments);}',
            "gtag('consent', 'default'",
            "'ad_storage': 'denied'",
            "'ad_user_data': 'denied'",
            "'ad_personalization': 'denied'",
            "'analytics_storage': 'denied'",
            "'wait_for_update': 500",
        ], false);
        $response->assertDontSee('googletagmanager.com', false);
        $response->assertDontSee("gtag('config'", false);
        $response->assertDontSee('cookie-banner', false);
        $response->assertDontSee('Cookie-inställningar', false);
        $response->assertDontSee('Godkänn', false);
        $response->assertDontSee('Neka', false);
    }

    public function test_invalid_measurement_id_does_not_load_the_tag(): void
    {
        config(['services.google_analytics.measurement_id' => 'G-TEST1234<script>alert(1)</script>']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee("gtag('consent', 'default'", false);
        $response->assertDontSee('googletagmanager.com', false);
        $response->assertDontSee("gtag('config'", false);
        $response->assertDontSee('alert(1)', false);
        $response->assertDontSee('cookie-banner', false);
    }

    #[TestWith(['home'])]
    #[TestWith(['demo'])]
    #[TestWith(['privacy'])]
    #[TestWith(['guides.marking'])]
    public function test_public_pages_load_consent_mode_and_equal_cookie_buttons(string $routeName): void
    {
        config(['services.google_analytics.measurement_id' => 'g-test1234']);

        $response = $this->get(route($routeName));

        $response->assertSeeInOrder([
            "gtag('consent', 'default'",
            "'ad_storage': 'denied'",
            "'ad_user_data': 'denied'",
            "'ad_personalization': 'denied'",
            "'analytics_storage': 'denied'",
            "'wait_for_update': 500",
            "gtag('set', 'ads_data_redaction', true)",
            "localStorage.getItem('bagspar-cookie-consent')",
            "gtag('consent', 'update'",
            "'ad_storage': 'denied'",
            "'ad_user_data': 'denied'",
            "'ad_personalization': 'denied'",
            "'analytics_storage': 'granted'",
            'https://www.googletagmanager.com/gtag/js?id=G-TEST1234',
            "gtag('config', 'G-TEST1234')",
        ], false);
        $response->assertSee('<html lang="sv">', false);
        $response->assertSee('Vi mäter besök med Google Analytics 4 bara om du godkänner.', false);
        $response->assertSee('<button type="button" class="cookie-banner__choice" data-cookie-choice="granted">Godkänn</button>', false);
        $response->assertSee('<button type="button" class="cookie-banner__choice" data-cookie-choice="denied">Neka</button>', false);
        $response->assertSee('<button type="button" data-cookie-settings>Cookie-inställningar</button>', false);
        $response->assertDontSee('href="'.route('privacy').'" data-cookie-settings', false);
        $response->assertDontSee('data-cookie-settings>Cookie-inställningar</a>', false);
        $response->assertSee('js/cookies.js?v='.config('bagspar.asset_version'), false);
        $response->assertDontSee('g-test1234', false);
    }

    public function test_admin_page_does_not_load_the_tag_or_the_banner(): void
    {
        config([
            'services.google_analytics.measurement_id' => 'G-TEST1234',
            'bagspar.admin_token' => 'pilot-secret',
        ]);

        $response = $this->withBasicAuth('bagspar', 'pilot-secret')->get(route('admin.signups'));

        $response->assertOk();
        $response->assertDontSee('googletagmanager.com', false);
        $response->assertDontSee('gtag(', false);
        $response->assertDontSee('dataLayer', false);
        $response->assertDontSee('cookie-banner', false);
        $response->assertDontSee('Cookie-inställningar', false);
        $response->assertSee('Anmälningar', false);
    }

    public function test_privacy_policy_describes_cookies_without_analytics_until_the_measurement_id_is_set(): void
    {
        config(['services.google_analytics.measurement_id' => null]);

        $response = $this->get(route('privacy'));

        $response->assertSee('<h2>Cookies</h2>', false);
        $response->assertSee('teknisk sessionscookie', false);
        $response->assertSee('inga analysverktyg och inga spårningscookies', false);
        $response->assertDontSee('Google Analytics 4', false);
        $response->assertDontSee('artikel 6.1 a', false);
    }

    public function test_privacy_policy_explains_google_analytics_and_consent_when_the_measurement_id_is_set(): void
    {
        config(['services.google_analytics.measurement_id' => 'G-TEST1234']);

        $response = $this->get(route('privacy'));

        $response->assertSee('Google Analytics 4', false);
        $response->assertSee('Google Ireland Limited', false);
        $response->assertSee('analytics_storage, ad_storage, ad_user_data och ad_personalization', false);
        $response->assertSee('artikel 6.1 a', false);
        $response->assertSee('ad_storage, ad_user_data och ad_personalization förblir nekade', false);
        $response->assertSee('Cookie-inställningar i sidfoten', false);
        $response->assertSee('_ga', false);
        $response->assertDontSee('inga analysverktyg och inga spårningscookies', false);
    }

    public function test_cookie_script_saves_either_choice_and_updates_consent(): void
    {
        $script = file_get_contents(public_path('js/cookies.js'));
        $stylesheet = file_get_contents(public_path('css/site.css'));
        $worker = file_get_contents(public_path('sw.js'));

        $this->assertIsString($script);
        $this->assertIsString($stylesheet);
        $this->assertIsString($worker);
        $this->assertStringContainsString('localStorage.setItem(storageKey, value)', $script);
        $this->assertStringContainsString("const storageKey = 'bagspar-cookie-consent'", $script);
        $this->assertStringContainsString("window.gtag('consent', 'update', consentUpdate(value === 'granted'))", $script);
        $this->assertStringContainsString("ad_storage: 'denied'", $script);
        $this->assertStringContainsString("ad_user_data: 'denied'", $script);
        $this->assertStringContainsString("ad_personalization: 'denied'", $script);
        $this->assertStringContainsString("analytics_storage: granted ? 'granted' : 'denied'", $script);
        $this->assertStringContainsString("value !== 'granted' && value !== 'denied'", $script);
        $this->assertStringContainsString('data-cookie-settings', $script);
        $this->assertStringContainsString('.cookie-banner__choice {', $stylesheet);
        $this->assertStringContainsString('grid-template-columns: 1fr 1fr;', $stylesheet);
        $this->assertStringNotContainsString('cookie-banner__choice--', $stylesheet);
        $this->assertStringContainsString("'/js/cookies.js'", $worker);
    }
}
