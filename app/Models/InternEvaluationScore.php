<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternEvaluationScore extends Model
{
    protected $fillable = [
        'intern_evaluation_id',
        'criteria_id',
        'score',
        'filled_by_role',
        'filled_by_user_id',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function internEvaluation(): BelongsTo
    {
        return $this->belongsTo(InternEvaluation::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }
}