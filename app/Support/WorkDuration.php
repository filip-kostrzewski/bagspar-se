<?php

namespace App\Support;

final class WorkDuration
{
    public static function label(int $minutes): string
    {
        $hours = intdiv(max(0, $minutes), 60);
        $rest = max(0, $minutes) % 60;

        if ($hours === 0) {
            return $rest.' min';
        }

        if ($rest === 0) {
            return $hours.' tim';
        }

        return $hours.' tim '.$rest.' min';
    }
}
