<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    public const ANONYMIZED_NAME = 'Borttagen kund';

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'name',
        'contact_name',
        'phone',
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

    public function phoneHref(): ?string
    {
        if (! is_string($this->phone) || trim($this->phone) === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $this->phone);

        if (! is_string($digits) || $digits === '' || $digits === '+') {
            return null;
        }

        return 'tel:'.$digits;
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
