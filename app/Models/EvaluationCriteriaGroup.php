<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriteriaGroup extends Model
{
    protected $table = 'evaluation_criteria_groups';

    protected $fillable = [
        'name',
        'code',
        'description',
        'order',
    ];

    public function subgroups(): HasMany
    {
        return $this->hasMany(EvaluationCriteriaSubgroup::class, 'group_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriteria::class, 'group_id');
    }
}
