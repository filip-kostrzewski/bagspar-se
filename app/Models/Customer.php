<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'name',
        'contact_name',
        'address',
        'email',
    ];

    /** @return BelongsTo<Workshop, $this> */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public static function findOrCreateForWorkshop(Workshop $workshop, string $name): self
    {
        $name = mb_substr(trim($name), 0, 160);
        $normalized = mb_strtolower($name);
        $existing = $workshop->customers()->get()->first(
            fn (self $customer): bool => mb_strtolower($customer->name) === $normalized,
        );

        if ($existing instanceof self) {
            return $existing;
        }

        return $workshop->customers()->create(['name' => $name]);
    }
}
