<?php

namespace App\Models;

use Database\Factories\SashEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SashEvent extends Model
{
    public const KIND_STAGE = 'stage';

    public const KIND_NOTE = 'note';

    public const KIND_PHOTO = 'photo';

    public const UPDATED_AT = null;

    /** @use HasFactory<SashEventFactory> */
    use HasFactory;

    protected $fillable = [
        'sash_id',
        'user_id',
        'user_name',
        'kind',
        'stage',
        'body',
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
}
