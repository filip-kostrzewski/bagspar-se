<?php

$contactEmail = env('BAGSPAR_CONTACT_EMAIL');
$pilotOffer = env('BAGSPAR_PILOT_OFFER');
$adminToken = env('BAGSPAR_ADMIN_TOKEN');

return [
    'lead_notification_email' => env('LEAD_NOTIFICATION_EMAIL'),

    /*
     * Public address on the privacy policy and in the footer.
     * Hidden when BAGSPAR_CONTACT_EMAIL is empty.
     */
    'contact_email' => is_string($contactEmail) && trim($contactEmail) !== ''
        ? trim($contactEmail)
        : null,

    /*
     * Pilot offer headline. BAGSPAR_PILOT_OFFER replaces the default.
     * A blank value keeps the default so the offer stays visible.
     */
    'pilot_offer' => is_string($pilotOffer) && trim($pilotOffer) !== ''
        ? trim($pilotOffer)
        : 'Gratis pilot i 3 månader för de första 10 verkstäderna',

    'pilot_price_line' => '790 kr/mån exkl. moms efter piloten. Ingen bindningstid.',

    'pilot_start_line' => 'Piloten startar när tre verkstäder anmält sig – du får tillgång till prototypen direkt.',

    'demo_sync_days' => 14,

    /*
     * Password for /admin/anmalningar. HTTP basic auth accepts any username.
     * The same value is accepted as an Authorization Bearer token.
     * The route responds with 404 when BAGSPAR_ADMIN_TOKEN is empty.
     */
    'admin_token' => is_string($adminToken) && trim($adminToken) !== ''
        ? trim($adminToken)
        : null,
];
