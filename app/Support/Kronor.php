<?php

namespace App\Support;

use InvalidArgumentException;

final class Kronor
{
    public const MAX_ORE = 100_000_000;

    public static function parseOptional(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value) && ! is_int($value)) {
            throw new InvalidArgumentException('Beloppet måste vara text.');
        }

        $text = str_replace(["\u{00A0}", "\u{202F}"], ' ', trim((string) $value));

        if ($text === '') {
            return null;
        }

        $text = str_replace(' ', '', $text);

        if (str_contains($text, ',') && str_contains($text, '.')) {
            throw new InvalidArgumentException('Beloppet kan inte innehålla både komma och punkt.');
        }

        $text = str_replace(',', '.', $text);

        if (preg_match('/^\d+(\.\d{1,2})?$/', $text) !== 1) {
            throw new InvalidArgumentException('Beloppet måste vara kronor med högst två decimaler.');
        }

        [$kronor, $fraction] = array_pad(explode('.', $text, 2), 2, '');
        $ore = ((int) $kronor) * 100 + (int) str_pad($fraction, 2, '0');

        if ($ore > self::MAX_ORE) {
            throw new InvalidArgumentException('Beloppet får vara högst 1 000 000 kr.');
        }

        return $ore;
    }

    public static function format(int $ore): string
    {
        return self::amount($ore).' kr';
    }

    public static function amount(int $ore): string
    {
        $kronor = intdiv($ore, 100);
        $cents = $ore % 100;
        $grouped = number_format($kronor, 0, ',', ' ');

        if ($cents === 0) {
            return $grouped;
        }

        return $grouped.','.str_pad((string) $cents, 2, '0', STR_PAD_LEFT);
    }

    public static function input(int $ore): string
    {
        return self::amount($ore);
    }
}
