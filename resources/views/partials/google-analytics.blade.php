<script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
<script>
    gtag('js', new Date());
    gtag('config', {{ \Illuminate\Support\Js::from($googleAnalyticsId) }});
</script>
