<?php

namespace App\Models;

use Database\Factories\QuoteLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    /** @use HasFactory<QuoteLineFactory> */
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'description',
        'quantity',
        'unit_price_ore',
        'sort_order',
    ];

    /** @return BelongsTo<Quote, $this> */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function lineTotalOre(): int
    {
        return (int) $this->quantity * (int) $this->unit_price_ore;
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_ore' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
