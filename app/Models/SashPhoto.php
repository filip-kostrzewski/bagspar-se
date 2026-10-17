<?php

namespace App\Models;

use Database\Factories\SashPhotoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SashPhoto extends Model
{
    /** @use HasFactory<SashPhotoFactory> */
    use HasFactory;

    public const MOMENTS = [
        'before',
        'after',
    ];

    protected $fillable = [
        'sash_id',
        'moment',
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

    /**
     * @param  Builder<SashPhoto>  $query
     * @return Builder<SashPhoto>
     */
    public function scopeInDocumentOrder(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN moment = 'after' THEN 1 ELSE 0 END")
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function documentMomentLabel(): ?string
    {
        return match ($this->moment) {
            'after' => 'Efter',
            'before', null => 'Före',
            default => null,
        };
    }

    public function documentCaption(): string
    {
        $date = $this->created_at?->timezone('Europe/Stockholm')->format('Y-m-d');

        return collect([$this->documentMomentLabel(), $date])
            ->filter(fn (?string $part): bool => is_string($part) && $part !== '')
            ->implode(' · ');
    }
}
