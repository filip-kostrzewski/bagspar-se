<?php

namespace App\Models;

use Database\Factories\ProjectEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEvent extends Model
{
    public const KIND_SASH_REMOVED = 'sash-removed';

    public const KIND_QUOTE_SENT = 'quote-sent';

    public const KIND_QUOTE_ACCEPTED = 'quote-accepted';

    public const KIND_STATUS_LINK_SENT = 'status-link-sent';

    public const UPDATED_AT = null;

    /** @use HasFactory<ProjectEventFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'user_name',
        'kind',
        'body',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
