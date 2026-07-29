<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternEvaluationApproval extends Model
{
    protected $fillable = [
        'intern_evaluation_id',
        'role',
        'user_id',
        'action',
        'notes',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function internEvaluation(): BelongsTo
    {
        return $this->belongsTo(InternEvaluation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}