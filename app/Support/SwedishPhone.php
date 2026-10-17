<?php

namespace App\Support;

use InvalidArgumentException;

final class SwedishPhone
{
    /**
     * @return non-empty-string|null
     */
    public static function parseOptional(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException('Telefonnumret måste vara text.');
        }

        $text = trim($value);

        if ($text === '') {
            return null;
        }

        $compact = preg_replace('/[\s.\-()]+/u', '', $text);

        if (! is_string($compact) || $compact === '') {
            return null;
        }

        if (str_starts_with($compact, '00')) {
            $compact = '+'.substr($compact, 2);
        }

        if (str_starts_with($compact, '+')) {
            if (! str_starts_with($compact, '+46')) {
                throw new InvalidArgumentException('Telefonnumret måste vara ett svenskt nummer.');
            }

            $nationalSignificant = substr($compact, 3);

            if (str_starts_with($nationalSignificant, '0')) {
                $nationalSignificant = substr($nationalSignificant, 1);
            }
        } elseif (str_starts_with($compact, '0')) {
            $nationalSignificant = substr($compact, 1);
        } else {
            throw new InvalidArgumentException('Telefonnumret måste vara ett svenskt nummer.');
        }

        if (preg_match('/^[1-9]\d{6,11}$/', $nationalSignificant) !== 1) {
            throw new InvalidArgumentException('Telefonnumret måste vara ett svenskt nummer.');
        }

        return '+46'.$nationalSignificant;
    }

    public static function format(string $e164): string
    {
        if (preg_match('/^\+46([1-9]\d{6,11})$/', $e164, $matches) !== 1) {
            return $e164;
        }

        $national = '0'.$matches[1];
        $areaLength = self::areaCodeLength($national);
        $area = substr($national, 0, $areaLength);
        $subscriber = self::groupSubscriber(substr($national, $areaLength));

        return $area.'-'.$subscriber;
    }

    public static function telHref(string $e164): string
    {
        return 'tel:'.$e164;
    }

    private static function areaCodeLength(string $national): int
    {
        if (str_starts_with($national, '07')) {
            return 3;
        }

        if (str_starts_with($national, '08')) {
            return 2;
        }

        $threeDigit = [
            '010', '011', '013', '016', '018', '019', '020', '021', '023', '026',
            '031', '033', '035', '036', '040', '042', '044', '046', '054', '060',
            '063', '077', '090',
        ];

        if (in_array(substr($national, 0, 3), $threeDigit, true)) {
            return 3;
        }

        return 4;
    }

    private static function groupSubscriber(string $subscriber): string
    {
        if ($subscriber === '') {
            return '';
        }

        if (strlen($subscriber) % 2 === 1) {
            $groups = [substr($subscriber, 0, 3)];
            $rest = substr($subscriber, 3);
        } else {
            $groups = [];
            $rest = $subscriber;
        }

        if ($rest !== '') {
            foreach (str_split($rest, 2) as $pair) {
                $groups[] = $pair;
            }
        }

        return implode(' ', $groups);
    }
}
