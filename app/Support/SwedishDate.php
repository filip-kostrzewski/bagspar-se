<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class SwedishDate
{
    public static function format(mixed $date): string
    {
        if (! $date instanceof Carbon) {
            return '';
        }

        $months = [
            1 => 'jan',
            2 => 'feb',
            3 => 'mar',
            4 => 'apr',
            5 => 'maj',
            6 => 'jun',
            7 => 'jul',
            8 => 'aug',
            9 => 'sep',
            10 => 'okt',
            11 => 'nov',
            12 => 'dec',
        ];

        return $date->format('j').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }

    public static function formatDateTime(mixed $date): string
    {
        if (! $date instanceof Carbon) {
            return '';
        }

        $local = $date->copy()->timezone('Europe/Stockholm');

        return self::format($local).' '.$local->format('H:i');
    }
}
