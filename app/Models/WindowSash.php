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

    /** @return HasMany<SashEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SashEvent::class, 'sash_id');
    }

    public function stageLabel(): string
    {
        return match ($this->stage) {
            'scraping' => 'Skrapning',
            'repair' => 'Trälagning',
            'glazing' => 'Glasning',
            'painting' => 'Målning',
            'ready' => 'Klar',
            default => 'Mottagen',
        };
    }

    public function recordEvent(User $user, string $kind, ?string $stage = null, ?string $body = null): SashEvent
    {
        $event = $this->events()->create([
            'user_id' => $user->getKey(),
            'user_name' => $user->name,
            'kind' => $kind,
            'stage' => $stage,
            'body' => $body,
        ]);

        return $event;
    }
}
