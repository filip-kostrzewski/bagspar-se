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
        return self::labelForStage($this->stage);
    }

    public static function labelForStage(?string $stage): string
    {
        return match ($stage) {
            'scraping' => 'Skrapning',
            'repair' => 'Trälagning',
            'glazing' => 'Glasning',
            'painting' => 'Målning',
            'ready' => 'Klar',
            default => 'Mottagen',
        };
    }

    public static function stageSlug(?string $stage): string
    {
        return match ($stage) {
            'received' => 'mottagen',
            'scraping' => 'skrapning',
            'repair' => 'tralagning',
            'glazing' => 'glasning',
            'painting' => 'malning',
            'ready' => 'klar',
            default => '',
        };
    }

    public static function stageFromFilter(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            'mottagen', 'received' => 'received',
            'skrapning', 'scraping' => 'scraping',
            'tralagning', 'trälagning', 'repair' => 'repair',
            'glasning', 'glazing' => 'glazing',
            'malning', 'målning', 'painting' => 'painting',
            'klar', 'ready' => 'ready',
            default => '',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function filterStages(): array
    {
        $stages = [];

        foreach (self::STAGES as $stage) {
            $stages[self::stageSlug($stage)] = self::labelForStage($stage);
        }

        return $stages;
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
