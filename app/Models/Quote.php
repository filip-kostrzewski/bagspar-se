<?php

namespace App\Models;

use App\Support\RotUnderlag;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_ACCEPTED = 'accepted';

    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'workshop_id',
        'status',
        'token',
        'introduction',
        'valid_until',
        'sent_at',
        'accepted_at',
        'accepted_name',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Workshop, $this> */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /** @return HasMany<QuoteLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Quote>  $query
     * @return Builder<Quote>
     */
    public function scopeSentOrAccepted(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_ACCEPTED]);
    }

    public function subtotalOre(): int
    {
        return (int) $this->lines->sum(fn (QuoteLine $line): int => $line->lineTotalOre());
    }

    public function vatOre(): int
    {
        return RotUnderlag::vatOre($this->subtotalOre());
    }

    public function totalOre(): int
    {
        return $this->subtotalOre() + $this->vatOre();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function issueToken(): void
    {
        $this->token = Str::random(48);
    }

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }
}
