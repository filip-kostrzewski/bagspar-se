<?php

namespace App\Models;

use App\Support\Pricing;
use App\Support\WorkshopLogo;
use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Workshop extends Model
{
    /** @use HasFactory<WorkshopFactory> */
    use HasFactory;

    public const PLAN_MONTHLY = 'monthly';

    public const PLAN_YEARLY = 'yearly';

    public const ACCESS_TRIAL = 'trial';

    public const ACCESS_ORDERED = 'ordered';

    public const ACCESS_ACTIVE = 'active';

    public const ACCESS_LOCKED = 'locked';

    protected $fillable = [
        'name',
        'organization_number',
        'contact_email',
        'phone',
        'address',
        'logo_path',
        'trial_ends_at',
        'subscription_plan',
        'subscription_ordered_at',
        'paid_until',
        'billing_email',
        'billing_reference',
        'access_reminder_sent_at',
        'onboarding_nudge_sent_at',
        'registration_notified_at',
        'hourly_rate_ore',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ordered_at' => 'datetime',
            'paid_until' => 'datetime',
            'access_reminder_sent_at' => 'datetime',
            'onboarding_nudge_sent_at' => 'datetime',
            'registration_notified_at' => 'datetime',
            'hourly_rate_ore' => 'integer',
        ];
    }

    public function hasLogo(): bool
    {
        return WorkshopLogo::visible($this) !== null;
    }

    /**
     * The last instant the workshop can use the app, or null when it never could.
     *
     * The trial, a paid period, and the grace period after an order each keep
     * the account open. The latest of them wins.
     */
    public function accessEndsAt(): ?Carbon
    {
        $candidates = array_filter([
            $this->trial_ends_at,
            $this->paid_until,
            $this->orderGraceEndsAt(),
        ]);

        if ($candidates === []) {
            return null;
        }

        return Carbon::instance(max($candidates))->utc();
    }

    public function hasAccess(): bool
    {
        $endsAt = $this->accessEndsAt();

        return $endsAt instanceof Carbon && $endsAt->isFuture();
    }

    public function hasOrderedSubscription(): bool
    {
        return $this->subscription_ordered_at instanceof Carbon;
    }

    public function hasPaidAccess(): bool
    {
        return $this->paid_until instanceof Carbon && $this->paid_until->isFuture();
    }

    public function isTrialing(): bool
    {
        return $this->accessState() === self::ACCESS_TRIAL;
    }

    public function isLocked(): bool
    {
        return ! $this->hasAccess();
    }

    /**
     * @return self::ACCESS_*
     */
    public function accessState(): string
    {
        if (! $this->hasAccess()) {
            return self::ACCESS_LOCKED;
        }

        if ($this->hasPaidAccess()) {
            return self::ACCESS_ACTIVE;
        }

        if ($this->hasOrderedSubscription()) {
            return self::ACCESS_ORDERED;
        }

        return self::ACCESS_TRIAL;
    }

    /**
     * Whole days left before the account locks. Zero on the last day and when locked.
     */
    public function accessDaysLeft(): int
    {
        $endsAt = $this->accessEndsAt();

        if (! $endsAt instanceof Carbon || $endsAt->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($endsAt, absolute: true);
    }

    public function isOnYearlyPlan(): bool
    {
        return $this->subscription_plan === self::PLAN_YEARLY;
    }

    public function planLabel(): ?string
    {
        return match ($this->subscription_plan) {
            self::PLAN_MONTHLY => 'Månadsabonnemang',
            self::PLAN_YEARLY => 'Årsabonnemang',
            default => null,
        };
    }

    public function seatCount(): int
    {
        return $this->users()->count();
    }

    public function extraSeatCount(): int
    {
        return max(0, $this->seatCount() - Pricing::includedSeats());
    }

    /**
     * Kronor per month excluding VAT for the current number of users.
     */
    public function monthlyPriceKr(): int
    {
        return Pricing::monthlyKr() + $this->extraSeatCount() * Pricing::extraSeatKr();
    }

    /**
     * Kronor per year excluding VAT for the current number of users.
     */
    public function yearlyPriceKr(): int
    {
        return Pricing::yearlyKr() + $this->extraSeatCount() * Pricing::extraSeatKr() * 12;
    }

    private function orderGraceEndsAt(): ?Carbon
    {
        if (! $this->subscription_ordered_at instanceof Carbon) {
            return null;
        }

        $graceDays = max(0, (int) config('bagspar.order_grace_days', 30));

        return $this->subscription_ordered_at->copy()->addDays($graceDays);
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<WorkshopInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(WorkshopInvitation::class);
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
