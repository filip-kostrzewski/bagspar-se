<?php

namespace App\Models;

use Database\Factories\SashTimeEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SashTimeEntry extends Model
{
    /** @use HasFactory<SashTimeEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'sash_id',
        'user_id',
        'user_name',
        'worked_on',
        'minutes',
        'note',
    ];

    /** @return BelongsTo<WindowSash, $this> */
    public function sash(): BelongsTo
    {
        return $this->belongsTo(WindowSash::class, 'sash_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'worked_on' => 'date',
            'minutes' => 'integer',
        ];
    }
}
