<?php

namespace App\Models;

use App\Support\Kronor;
use Database\Factories\SashMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SashMaterial extends Model
{
    /** @use HasFactory<SashMaterialFactory> */
    use HasFactory;

    public const TYPES = [
        'glas',
        'kitt',
        'färg',
        'beslag',
        'övrigt',
    ];

    protected $fillable = [
        'sash_id',
        'type',
        'description',
        'quantity',
        'unit',
        'unit_price_ore',
    ];

    /** @return BelongsTo<WindowSash, $this> */
    public function sash(): BelongsTo
    {
        return $this->belongsTo(WindowSash::class, 'sash_id');
    }

    public static function labelForType(string $type): string
    {
        return match ($type) {
            'glas' => 'Glas',
            'kitt' => 'Kitt',
            'färg' => 'Färg',
            'beslag' => 'Beslag',
            'övrigt' => 'Övrigt',
            default => $type,
        };
    }

    public function historyLabel(): string
    {
        $label = self::labelForType($this->type).', '.$this->description.', '.self::quantityLabel($this->quantity).' '.$this->unit;

        if (is_int($this->unit_price_ore)) {
            $label .= ', à '.Kronor::format($this->unit_price_ore);
        }

        return $label;
    }

    public static function quantityLabel(mixed $quantity): string
    {
        if (! is_numeric($quantity)) {
            return '';
        }

        $normalized = number_format((float) $quantity, 2, '.', '');
        $trimmed = rtrim(rtrim($normalized, '0'), '.');

        return str_replace('.', ',', $trimmed);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price_ore' => 'integer',
        ];
    }
}
