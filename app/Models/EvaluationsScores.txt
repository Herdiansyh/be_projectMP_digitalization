<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationsScores extends Model
{
    protected $table = 'evaluation_scores';

    protected $fillable = [
        'evaluation_id',
        'criteria_id',
        'score',
        'score_x_weight',
        'filled_by_role',
        'filled_by_user_id',
    ];

    protected $casts = [
        'evaluation_id' => 'integer',
        'criteria_id' => 'integer',
        'score' => 'integer',
        'score_x_weight' => 'decimal:2',
        'filled_by_user_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->score !== null && $model->criteria) {
                $model->score_x_weight = $model->score * $model->criteria->weight;
            }
        });

        static::saved(function (self $model) {
            if ($model->evaluation) {
                $model->evaluation->recalculateTotalScore();
            }
        });
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }

    public function filledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by_user_id');
    }
}
