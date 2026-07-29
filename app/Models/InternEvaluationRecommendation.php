<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternEvaluationRecommendation extends Model
{
    protected $fillable = [
        'intern_evaluation_id',
        'recommended_status',
        'notes',
        'created_by',
    ];

    public function internEvaluation(): BelongsTo
    {
        return $this->belongsTo(InternEvaluation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}