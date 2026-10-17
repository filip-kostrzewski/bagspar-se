<?php

namespace App\Models;

use Database\Factories\WorkshopInvitationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkshopInvitation extends Model
{
    public const UPDATED_AT = null;

    /** @use HasFactory<WorkshopInvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'invited_by_user_id',
        'email',
        'token_hash',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Workshop, $this> */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /** @return BelongsTo<User, $this> */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * @param  Builder<WorkshopInvitation>  $query
     * @return Builder<WorkshopInvitation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function freshToken(): string
    {
        return Str::random(64);
    }

    public static function findPendingByToken(string $token): ?self
    {
        $invitation = self::query()->where('token_hash', self::hashToken($token))->first();

        if (! $invitation instanceof self || $invitation->accepted_at !== null || $invitation->expires_at?->isPast()) {
            return null;
        }

        return $invitation;
    }
}
