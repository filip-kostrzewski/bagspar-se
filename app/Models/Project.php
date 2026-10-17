<?php

namespace App\Models;

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
        ];
    }
}
