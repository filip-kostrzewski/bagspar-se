{{-- Consent Mode v2 is set before gtag.js. Storage stays denied until the visitor chooses. --}}
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag(){dataLayer.push(arguments);}

    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'analytics_storage': 'denied',
        'wait_for_update': 500
    });

    gtag('set', 'ads_data_redaction', true);

    try {
        var bagsparCookieChoice = window.localStorage.getItem('bagspar-cookie-consent');

        if (bagsparCookieChoice === 'granted' || bagsparCookieChoice === 'denied') {
            document.documentElement.classList.add('cookie-choice-saved');
        }

        if (bagsparCookieChoice === 'granted') {
            gtag('consent', 'update', {
                'ad_storage': 'denied',
                'ad_user_data': 'denied',
                'ad_personalization': 'denied',
                'analytics_storage': 'granted'
            });
        }
    } catch (bagsparCookieError) {}
</script>
