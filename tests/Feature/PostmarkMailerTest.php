<?php

namespace Tests\Feature;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use ReflectionProperty;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport;
use Tests\TestCase;

class PostmarkMailerTest extends TestCase
{
    public function test_postmark_mailer_posts_to_the_api_with_the_configured_key_and_from_address(): void
    {
        config([
            'mail.mailers.postmark.transport' => 'postmark',
            'services.postmark.key' => 'server-token-test',
            'mail.from.address' => 'noreply@bagspar.se',
            'mail.from.name' => 'Bågspår',
        ]);

        $captured = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = [
                'method' => $method,
                'url' => $url,
                'options' => $options,
            ];

            return new MockResponse((string) json_encode([
                'ErrorCode' => 0,
                'Message' => 'OK',
                'MessageID' => 'message-id',
            ]), [
                'http_code' => 200,
                'response_headers' => ['content-type: application/json'],
            ]);
        });

        $mailer = Mail::mailer('postmark');
        $transport = $mailer->getSymfonyTransport();
        $this->assertInstanceOf(PostmarkApiTransport::class, $transport);

        $clientProperty = new ReflectionProperty($transport, 'client');
        $clientProperty->setValue($transport, $client);

        $mailer->raw('Verkstaden är anmäld.', function (Message $message): void {
            $message->to('verkstad@example.test')->subject('Ny anmälan');
        });

        $this->assertIsArray($captured);
        $this->assertSame('POST', $captured['method']);
        $this->assertSame('https://api.postmarkapp.com/email', $captured['url']);
        $this->assertSame('server-token-test', $this->headerValue($captured['options'], 'x-postmark-server-token'));

        $payload = json_decode((string) ($captured['options']['body'] ?? ''), true);
        $this->assertIsArray($payload);
        $this->assertStringContainsString('noreply@bagspar.se', $payload['From']);
        $this->assertSame('verkstad@example.test', $payload['To']);
        $this->assertSame('Ny anmälan', $payload['Subject']);
        $this->assertSame('Verkstaden är anmäld.', $payload['TextBody']);
        $this->assertSame(1, $client->getRequestsCount());
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function headerValue(array $options, string $name): ?string
    {
        $headers = $options['normalized_headers'][$name] ?? $options['headers'][$name] ?? null;

        if (is_string($headers)) {
            $headers = [$headers];
        }

        if (! is_array($headers)) {
            return null;
        }

        foreach ($headers as $header) {
            if (! is_string($header)) {
                continue;
            }

            if (str_contains($header, ':')) {
                return trim(substr($header, strpos($header, ':') + 1));
            }

            return $header;
        }

        return null;
    }
}
