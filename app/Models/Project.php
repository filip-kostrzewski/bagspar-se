<?php

namespace App\Models;

use App\Support\StoredSashPhoto;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'customer_id',
        'name',
        'customer_name',
        'location',
        'notes',
        'status',
        'notify_customer_when_ready',
        'delivery_date',
    ];

    /** @return BelongsTo<Workshop, $this> */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerLabel(): ?string
    {
        $name = $this->customer?->name ?? $this->customer_name;

        return is_string($name) && trim($name) !== '' ? $name : null;
    }

    /** @return HasMany<WindowSash, $this> */
    public function windowSashes(): HasMany
    {
        return $this->hasMany(WindowSash::class);
    }

    /** @return HasMany<ProjectEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ProjectEvent::class);
    }

    public function recordEvent(User $user, string $kind, ?string $body = null): ProjectEvent
    {
        return $this->events()->create([
            'user_id' => $user->getKey(),
            'user_name' => $user->name,
            'kind' => $kind,
            'body' => $body,
        ]);
    }

    public function deleteStoredPhotoFiles(): void
    {
        $paths = SashPhoto::query()
            ->whereIn('sash_id', $this->windowSashes()->select('window_sashes.id'))
            ->pluck('path');

        foreach ($paths as $path) {
            StoredSashPhoto::delete($path);
        }
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeNotFullyReady(Builder $query): Builder
    {
        return $query->where(function (Builder $projects): void {
            $projects->whereDoesntHave('windowSashes')
                ->orWhereHas('windowSashes', fn (Builder $sashes): Builder => $sashes->where('stage', '!=', 'ready'));
        });
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeDeliveryOverdue(Builder $query, string $today): Builder
    {
        return $query
            ->whereNotNull('delivery_date')
            ->whereDate('delivery_date', '<', $today)
            ->notFullyReady();
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeDeliveryDuring(Builder $query, string $start, string $end): Builder
    {
        return $query
            ->whereNotNull('delivery_date')
            ->whereDate('delivery_date', '>=', $start)
            ->whereDate('delivery_date', '<=', $end);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function clientStatusIsPublic(): bool
    {
        return $this->client_status_enabled
            && is_string($this->client_status_token)
            && $this->client_status_token !== '';
    }

    public function issueClientStatusToken(): void
    {
        $this->client_status_token = Str::random(48);
    }

    protected function casts(): array
    {
        return [
            'client_status_enabled' => 'boolean',
            'archived_at' => 'datetime',
            'notify_customer_when_ready' => 'boolean',
            'customer_ready_notified_at' => 'datetime',
            'delivery_date' => 'date',
        ];
    }
}
