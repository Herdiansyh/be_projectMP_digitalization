<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationApproval extends Model
{
    protected $fillable = [
        'evaluation_id',
        'role',
        'user_id',
        'action',
        'notes',
        'acted_at',
    ];

    protected $casts = [
        'evaluation_id' => 'integer',
        'user_id' => 'integer',
        'acted_at' => 'datetime',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
