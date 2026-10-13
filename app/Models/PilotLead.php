<?php

namespace App\Models;

use Database\Factories\PilotLeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotLead extends Model
{
    /** @use HasFactory<PilotLeadFactory> */
    use HasFactory;

    protected $fillable = [
        'company',
        'email',
        'message',
        'consented_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'consented_at' => 'immutable_datetime',
        ];
    }
}
