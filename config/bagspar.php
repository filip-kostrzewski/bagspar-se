<?php

$contactEmail = env('BAGSPAR_CONTACT_EMAIL');
$pilotOffer = env('BAGSPAR_PILOT_OFFER');
$adminToken = env('BAGSPAR_ADMIN_TOKEN');

/*
 * Query-string version for the public CSS, scripts, and service worker.
 * Cloudflare caches those files for hours. The hash changes when the file
 * bytes change, so the next HTML response asks for a URL the edge has not stored.
 */
$assetVersion = substr(sha1(implode('|', array_map(
    static function (string $relative): string {
        $path = public_path($relative);
        $contents = is_file($path) ? file_get_contents($path) : false;

        return is_string($contents) ? hash('sha256', $contents) : 'missing';
    },
    [
        'css/site.css',
        'js/site.js',
        'js/cookies.js',
        'js/demo.js',
        'sw.js',
    ],
))), 0, 12);

/*
 * Public address in the footer and on the privacy policy.
 * .env.example sends mail from noreply@bagspar.se. That mailbox is not where
 * someone asks for deletion, so the public address is kontakt@bagspar.se.
 * Set BAGSPAR_CONTACT_EMAIL when a different bagspar.se mailbox should be shown.
 */
$defaultContactEmail = 'kontakt@bagspar.se';

return [
    'lead_notification_email' => env('LEAD_NOTIFICATION_EMAIL'),

    'contact_email' => is_string($contactEmail) && trim($contactEmail) !== ''
        ? trim($contactEmail)
        : $defaultContactEmail,

    /*
     * Pilot offer headline. BAGSPAR_PILOT_OFFER replaces the default.
     * A blank value keeps the default so the offer stays visible.
     */
    'pilot_offer' => is_string($pilotOffer) && trim($pilotOffer) !== ''
        ? trim($pilotOffer)
        : 'Gratis pilot i 3 månader',

    'pilot_price_line' => '790 kr/mån exkl. moms efter piloten. Ingen bindningstid.',

    'pilot_follow_up' => 'Vi hör av oss inom några vardagar.',

    'demo_sync_days' => 14,

    'asset_version' => $assetVersion,

    /*
     * Password for /admin/anmalningar. HTTP basic auth accepts any username.
     * The same value is accepted as an Authorization Bearer token.
     * The route responds with 404 when BAGSPAR_ADMIN_TOKEN is empty.
     */
    'admin_token' => is_string($adminToken) && trim($adminToken) !== ''
        ? trim($adminToken)
        : null,
];
