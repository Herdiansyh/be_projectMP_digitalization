<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriteriaSubgroup extends Model
{
    protected $table = 'evaluation_criteria_subgroups';

    protected $fillable = [
        'group_id',
        'name',
        'roman_code',
        'description',
        'order',
    ];

    protected $casts = [
        'group_id' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteriaGroup::class, 'group_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriteria::class, 'subgroup_id');
    }
}
