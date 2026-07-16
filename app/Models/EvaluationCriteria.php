<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriteria extends Model
{
    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'group_id',
        'subgroup_id',
        'name',
        'weight',
        'scale_type',
        'is_active',
        'order',
    ];

    protected $casts = [
        'group_id' => 'integer',
        'subgroup_id' => 'integer',
        'weight' => 'integer',
        'is_active' => 'boolean',
    ];

    public const STANDARD_LABELS = [
        1 => 'Sangat Kurang',
        2 => 'Kurang',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat Baik',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteriaGroup::class, 'group_id');
    }

    public function subgroup(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteriaSubgroup::class, 'subgroup_id');
    }

    public function scaleOptions(): HasMany
    {
        return $this->hasMany(EvaluationCriteriaScaleOptions::class, 'criteria_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'criteria_id');
    }
}
