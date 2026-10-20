<?php

$contactEmail = env('BAGSPAR_CONTACT_EMAIL');
$adminToken = env('BAGSPAR_ADMIN_TOKEN');
$videoId = env('BAGSPAR_VIDEO_ID', '459e5746-38a0-4b21-ac4d-c6ad46731cb0');

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
     * Free trial for a new workshop account, in days from registration.
     */
    'trial_days' => 30,

    /*
     * Days a workshop keeps its access after ordering a subscription, so the
     * invoice can be issued and paid without the account locking meanwhile.
     */
    'order_grace_days' => 30,

    /*
     * Price list in kronor excluding VAT. App\Support\Pricing turns these
     * into the sentences shown on the public pages and in the app.
     */
    'pricing' => [
        'monthly_kr' => 590,
        'yearly_kr' => 5900,
        'included_seats' => 3,
        'extra_seat_kr' => 149,
        'invoice_days' => 30,
    ],

    /*
     * Mail a workshop may send to its customers per day: status links,
     * quotes and ready notices together.
     */
    'customer_mail_daily_limit' => 50,

    'demo_sync_days' => 14,

    'asset_version' => $assetVersion,

    /*
     * Promo video on the home page, hosted on Bunny Stream in the Bagspar library (same player setup as klartfoto.se).
     * Defaults to the uploaded Bunny GUID; BAGSPAR_VIDEO_ID overrides it and an
     * empty value hides the section.
     */
    'video' => [
        'library_id' => env('BAGSPAR_VIDEO_LIBRARY_ID', '785183'),
        'id' => is_string($videoId) && trim($videoId) !== '' ? trim($videoId) : null,
        'upload_date' => env('BAGSPAR_VIDEO_UPLOAD_DATE', '2026-10-20T14:00:00+02:00'),
        'duration' => 'PT44S',
    ],

    /*
     * Password for /admin/anmalningar. HTTP basic auth accepts any username.
     * The same value is accepted as an Authorization Bearer token.
     * The route responds with 404 when BAGSPAR_ADMIN_TOKEN is empty.
     */
    'admin_token' => is_string($adminToken) && trim($adminToken) !== ''
        ? trim($adminToken)
        : null,
];
