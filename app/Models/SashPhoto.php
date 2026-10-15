<?php

namespace App\Models;

use Database\Factories\SashPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SashPhoto extends Model
{
    /** @use HasFactory<SashPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'sash_id',
        'path',
        'original_filename',
        'mime_type',
        'size',
    ];

    /** @return BelongsTo<WindowSash, $this> */
    public function sash(): BelongsTo
    {
        return $this->belongsTo(WindowSash::class, 'sash_id');
    }
}
