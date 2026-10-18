<?php

namespace App\Models;

use Database\Factories\SpamSubmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class SpamSubmission extends Model
{
    /** @use HasFactory<SpamSubmissionFactory> */
    use HasFactory, MassPrunable;

    protected $fillable = [
        'reason',
        'ip',
        'user_agent',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<=', now()->subDays(90));
    }
}
