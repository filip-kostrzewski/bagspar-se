<?php

namespace App\Models;

use Database\Factories\WindowSashFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WindowSash extends Model
{
    /** @use HasFactory<WindowSashFactory> */
    use HasFactory;

    public const STAGES = [
        'received',
        'scraping',
        'repair',
        'glazing',
        'painting',
        'ready',
    ];

    protected $fillable = [
        'project_id',
        'code',
        'room',
        'location',
        'stage',
        'notes',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<SashPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(SashPhoto::class, 'sash_id');
    }
}
