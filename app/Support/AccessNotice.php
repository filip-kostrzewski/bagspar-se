<?php

namespace App\Support;

use App\Models\Workshop;

/**
 * The bar above the workshop pages that says when the account locks.
 */
final class AccessNotice
{
    /**
     * Days before the access ends when the bar starts showing during a trial.
     */
    private const TRIAL_WARNING_DAYS = 7;

    /**
     * @return array{tone: 'warning'|'locked'|'info', text: string, action: string}|null
     */
    public static function for(Workshop $workshop): ?array
    {
        $state = $workshop->accessState();

        if ($state === Workshop::ACCESS_LOCKED) {
            return [
                'tone' => 'locked',
                'text' => 'Kontot är låst. Projekt och bilder finns kvar och öppnas igen när abonnemanget är beställt.',
                'action' => 'Beställ abonnemang',
            ];
        }

        $daysLeft = $workshop->accessDaysLeft();
        $endsAt = $workshop->accessEndsAt();

        if ($state === Workshop::ACCESS_TRIAL) {
            if ($daysLeft > self::TRIAL_WARNING_DAYS) {
                return null;
            }

            return [
                'tone' => 'warning',
                'text' => self::daysSentence('Provperioden tar slut', $daysLeft, $endsAt).' Beställ abonnemanget så fortsätter kontot utan avbrott.',
                'action' => 'Beställ abonnemang',
            ];
        }

        if ($state === Workshop::ACCESS_ACTIVE && $daysLeft <= self::TRIAL_WARNING_DAYS) {
            return [
                'tone' => 'warning',
                'text' => self::daysSentence('Den betalda perioden tar slut', $daysLeft, $endsAt).' Kontot öppnas igen när nästa faktura är betald.',
                'action' => 'Visa abonnemang',
            ];
        }

        return null;
    }

    private static function daysSentence(string $subject, int $daysLeft, mixed $endsAt): string
    {
        $when = match (true) {
            $daysLeft === 0 => 'i dag',
            $daysLeft === 1 => 'i morgon',
            default => 'om '.$daysLeft.' dagar',
        };

        $date = $endsAt !== null ? ' ('.SwedishDate::formatLocal($endsAt).')' : '';

        return $subject.' '.$when.$date.'.';
    }
}
