@php
    /** @var list<array{question: string, answer: string}> $faqs */
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(
            fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ],
            $faqs,
        ),
    ];
@endphp
<h2>Frågor och svar</h2>
@foreach ($faqs as $faq)
    <h3>{{ $faq['question'] }}</h3>
    <p>{{ $faq['answer'] }}</p>
@endforeach
@push('scripts')
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) !!}</script>
@endpush
