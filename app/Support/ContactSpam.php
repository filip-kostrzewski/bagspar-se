<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class ContactSpam
{
    public const REASON_HONEYPOT = 'honeypot';

    public const REASON_TURNSTILE = 'turnstile';

    public const REASON_TOO_FAST = 'too_fast';

    public const REASON_GIBBERISH = 'gibberish';

    public const REASON_BLOCKED_EMAIL_DOMAIN = 'blocked_email_domain';

    private const MINIMUM_FILL_SECONDS = 3;

    private const BLOCKED_EMAIL_DOMAIN_SUFFIX = 'lettersmail.com';

    /**
     * Vowels include y and Swedish å/ä/ö so names such as Bergström are kept.
     * A consonant run of eight stays above those names (their longest run is five).
     */
    private const VOWELS = 'aeiouyåäöéèáàüæø';

    public static function isTurnstileEnabled(): bool
    {
        return self::configuredKey('services.turnstile.site_key') !== null
            && self::configuredKey('services.turnstile.secret') !== null;
    }

    public static function startedAtToken(): string
    {
        return Crypt::encryptString((string) now()->getTimestamp());
    }

    public static function rejectionReason(Request $request): ?string
    {
        if (self::honeypotFilled($request)) {
            return self::REASON_HONEYPOT;
        }

        if (self::isTurnstileEnabled() && ! self::turnstilePasses($request)) {
            return self::REASON_TURNSTILE;
        }

        if (self::filledTooFast($request)) {
            return self::REASON_TOO_FAST;
        }

        $email = $request->input('contact_email');

        if (is_string($email) && self::emailDomainIsBlocked($email)) {
            return self::REASON_BLOCKED_EMAIL_DOMAIN;
        }

        $name = $request->input('contact_name');
        $message = $request->input('contact_message');

        if ((is_string($name) && self::isGibberish($name)) || (is_string($message) && self::isGibberish($message))) {
            return self::REASON_GIBBERISH;
        }

        return null;
    }

    public static function clientIp(Request $request): ?string
    {
        $ip = $request->ip();

        if (! is_string($ip) || $ip === '' || strlen($ip) > 45) {
            return null;
        }

        return $ip;
    }

    public static function userAgent(Request $request): ?string
    {
        $userAgent = $request->userAgent();

        if (! is_string($userAgent) || $userAgent === '') {
            return null;
        }

        return mb_substr($userAgent, 0, 2000);
    }

    /**
     * @return array{
     *     contact_name: ?string,
     *     contact_company: ?string,
     *     contact_email: ?string,
     *     contact_phone: ?string,
     *     contact_message: ?string,
     *     source: ?string,
     *     website: ?string
     * }
     */
    public static function payload(Request $request): array
    {
        return [
            'contact_name' => self::payloadString($request->input('contact_name')),
            'contact_company' => self::payloadString($request->input('contact_company')),
            'contact_email' => self::payloadString($request->input('contact_email')),
            'contact_phone' => self::payloadString($request->input('contact_phone')),
            'contact_message' => self::payloadString($request->input('contact_message')),
            'source' => self::payloadString($request->input('source')),
            'website' => self::payloadString($request->input('website')),
        ];
    }

    private static function honeypotFilled(Request $request): bool
    {
        $website = $request->input('website');

        if (is_string($website)) {
            return trim($website) !== '';
        }

        return is_array($website) ? $website !== [] : $website !== null;
    }

    private static function turnstilePasses(Request $request): bool
    {
        $token = $request->input('cf-turnstile-response');

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $secret = self::configuredKey('services.turnstile.secret');

        if ($secret === null) {
            return false;
        }

        $fields = [
            'secret' => $secret,
            'response' => trim($token),
        ];
        $ip = $request->ip();

        if (is_string($ip) && $ip !== '') {
            $fields['remoteip'] = $ip;
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', $fields);
        } catch (ConnectionException) {
            return false;
        }

        return $response->successful() && $response->json('success') === true;
    }

    private static function filledTooFast(Request $request): bool
    {
        $token = $request->input('form_started_at');

        if (! is_string($token) || $token === '') {
            return true;
        }

        try {
            $startedAt = Crypt::decryptString($token);
        } catch (DecryptException) {
            return true;
        }

        if (! ctype_digit($startedAt)) {
            return true;
        }

        return now()->getTimestamp() - (int) $startedAt < self::MINIMUM_FILL_SECONDS;
    }

    private static function emailDomainIsBlocked(string $email): bool
    {
        $domain = rtrim(Str::lower(Str::after($email, '@')), '.');

        return $domain !== '' && Str::endsWith($domain, self::BLOCKED_EMAIL_DOMAIN_SUFFIX);
    }

    private static function isGibberish(string $value): bool
    {
        $words = preg_split('/[^\p{L}]+/u', $value, -1, PREG_SPLIT_NO_EMPTY);

        if (is_array($words)) {
            foreach ($words as $word) {
                if (is_string($word) && self::wordIsGibberish($word)) {
                    return true;
                }
            }
        }

        $letters = self::letters($value);

        return (mb_strlen($letters) >= 10 && self::hasNoVowels($letters))
            || self::hasLongConsonantRun($letters);
    }

    private static function wordIsGibberish(string $word): bool
    {
        $letters = self::letters($word);

        if (mb_strlen($letters) >= 6 && self::hasNoVowels($letters)) {
            return true;
        }

        if (self::hasLongConsonantRun($letters)) {
            return true;
        }

        return self::hasChaoticCase($letters);
    }

    private static function hasNoVowels(string $letters): bool
    {
        if ($letters === '') {
            return false;
        }

        return preg_match('/['.self::VOWELS.']/u', mb_strtolower($letters)) === 0;
    }

    private static function hasLongConsonantRun(string $letters): bool
    {
        if ($letters === '') {
            return false;
        }

        return preg_match('/[^'.self::VOWELS.']{8,}/u', mb_strtolower($letters)) === 1;
    }

    private static function hasChaoticCase(string $letters): bool
    {
        if (mb_strlen($letters) < 6) {
            return false;
        }

        $transitions = 0;
        $previousCase = null;

        foreach (mb_str_split($letters) as $character) {
            $case = self::letterCase($character);

            if ($case === null) {
                continue;
            }

            if ($previousCase !== null && $case !== $previousCase) {
                $transitions++;
            }

            $previousCase = $case;
        }

        return $transitions >= 4;
    }

    private static function letterCase(string $character): ?string
    {
        $lower = mb_strtolower($character);
        $upper = mb_strtoupper($character);

        if ($lower === $upper) {
            return null;
        }

        return $character === $lower ? 'lower' : 'upper';
    }

    private static function letters(string $value): string
    {
        $letters = preg_replace('/[^\p{L}]+/u', '', $value);

        return is_string($letters) ? $letters : '';
    }

    private static function payloadString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return mb_substr(trim($value), 0, 2000);
    }

    private static function configuredKey(string $key): ?string
    {
        $value = config($key);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
