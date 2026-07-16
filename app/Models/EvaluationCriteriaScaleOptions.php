<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationCriteriaScaleOptions extends Model
{
    protected $table = 'evaluation_criteria_scale_options';

    protected $fillable = [
        'criteria_id',
        'score',
        'description',
    ];

    protected $casts = [
        'criteria_id' => 'integer',
        'score' => 'integer',
    ];

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }
}
