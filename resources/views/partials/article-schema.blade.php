@php
    /** @var string $headline */
    /** @var string $description */
    /** @var string $pageUrl */
    /** @var string|null $datePublished */
    $articleDate = $datePublished ?? '2026-10-17';
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $headline,
        'description' => $description,
        'inLanguage' => 'sv',
        'datePublished' => $articleDate,
        'dateModified' => $articleDate,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $pageUrl,
        ],
        'author' => [
            '@type' => 'Organization',
            'name' => 'Prodi Digital AB',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Prodi Digital AB',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => rtrim((string) config('app.url'), '/').'/images/bagspar-mark.svg',
            ],
        ],
    ];
@endphp
@push('scripts')
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) !!}</script>
@endpush
