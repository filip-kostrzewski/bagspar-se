<?php

namespace App\Support;

use App\Models\SashMaterial;
use App\Models\SashTimeEntry;
use Illuminate\Support\Collection;

final class RotUnderlag
{
    public const VAT_PERCENT = 25;

    public const HEADING = 'Underlag, inte skatterådgivning';

    public const BODY = 'Beloppen kommer från verkstadens egen tid och egna materialpriser. Bågspår räknar inte rotavdrag, skatt eller vad som får dras av. Kontrollera timpris, priser och regler själv innan underlaget används. Det här är inte en offert och inte en faktura.';

    /**
     * @param  Collection<int, SashTimeEntry>  $entries
     * @param  Collection<int, SashMaterial>  $materials
     * @return array{
     *     hourly_rate_ore: ?int,
     *     labor_minutes: int,
     *     labor_ore: ?int,
     *     people: list<array{name: string, minutes: int, ore: ?int}>,
     *     labor_rows: list<array{date: mixed, sash: string, person: string, minutes: int, note: string, ore: ?int}>,
     *     material_rows: list<array{sash: string, label: string, description: string, quantity: string, unit: string, unit_price_ore: ?int, ore: ?int}>,
     *     material_ore: int,
     *     unpriced_material_count: int,
     *     labor_amounts: array{net: ?int, vat: ?int, gross: ?int},
     *     material_amounts: array{net: int, vat: int, gross: int},
     *     total_amounts: array{net: ?int, vat: ?int, gross: ?int}
     * }
     */
    public static function make(Collection $entries, Collection $materials, ?int $hourlyRateOre): array
    {
        $laborRows = [];
        $people = [];

        foreach ($entries as $entry) {
            $ore = $hourlyRateOre === null ? null : self::laborOre($entry->minutes, $hourlyRateOre);
            $name = $entry->user_name;
            $laborRows[] = [
                'date' => $entry->worked_on,
                'sash' => $entry->sash?->code ?? '',
                'person' => $name,
                'minutes' => $entry->minutes,
                'note' => is_string($entry->note) ? $entry->note : '',
                'ore' => $ore,
            ];

            if (! isset($people[$name])) {
                $people[$name] = ['name' => $name, 'minutes' => 0, 'ore' => $hourlyRateOre === null ? null : 0];
            }

            $people[$name]['minutes'] += $entry->minutes;

            if ($ore !== null && is_int($people[$name]['ore'])) {
                $people[$name]['ore'] += $ore;
            }
        }

        ksort($people, SORT_STRING);

        $materialRows = [];
        $materialOre = 0;
        $unpriced = 0;

        foreach ($materials as $material) {
            $unitPrice = $material->unit_price_ore;
            $lineOre = is_int($unitPrice) ? self::materialOre((string) $material->quantity, $unitPrice) : null;

            if ($lineOre === null) {
                $unpriced++;
            } else {
                $materialOre += $lineOre;
            }

            $materialRows[] = [
                'sash' => $material->sash?->code ?? '',
                'label' => SashMaterial::labelForType($material->type),
                'description' => $material->description,
                'quantity' => SashMaterial::quantityLabel($material->quantity),
                'unit' => $material->unit,
                'unit_price_ore' => $unitPrice,
                'ore' => $lineOre,
            ];
        }

        $laborOre = null;

        if ($hourlyRateOre !== null) {
            $laborOre = 0;

            foreach ($laborRows as $row) {
                $laborOre += $row['ore'] ?? 0;
            }
        }

        $laborAmounts = self::amounts($laborOre);
        $materialAmounts = self::amounts($materialOre);
        $totalAmounts = self::amounts($laborOre === null ? null : $laborOre + $materialOre);

        return [
            'hourly_rate_ore' => $hourlyRateOre,
            'labor_minutes' => (int) $entries->sum('minutes'),
            'labor_ore' => $laborOre,
            'people' => array_values($people),
            'labor_rows' => $laborRows,
            'material_rows' => $materialRows,
            'material_ore' => $materialOre,
            'unpriced_material_count' => $unpriced,
            'labor_amounts' => $laborAmounts,
            'material_amounts' => $materialAmounts,
            'total_amounts' => $totalAmounts,
        ];
    }

    /**
     * @return array{net: ?int, vat: ?int, gross: ?int}
     */
    public static function amounts(?int $netOre): array
    {
        if ($netOre === null) {
            return ['net' => null, 'vat' => null, 'gross' => null];
        }

        $vat = self::vatOre($netOre);

        return [
            'net' => $netOre,
            'vat' => $vat,
            'gross' => $netOre + $vat,
        ];
    }

    public static function vatOre(int $netOre): int
    {
        return (int) round($netOre * self::VAT_PERCENT / 100);
    }

    public static function laborOre(int $minutes, int $hourlyRateOre): int
    {
        return (int) round($minutes * $hourlyRateOre / 60);
    }

    public static function materialOre(string $quantity, int $unitPriceOre): int
    {
        $hundredths = (int) round(((float) $quantity) * 100);

        return intdiv($hundredths * $unitPriceOre, 100);
    }
}
