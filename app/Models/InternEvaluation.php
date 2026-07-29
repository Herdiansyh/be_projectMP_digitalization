<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InternEvaluation extends Model
{
    protected $fillable = [
        'intern_id',
        'department_id',
        'department_head_id',
        'leader_id',
        'section_head_id',
        'manager_id',
        'npk',
        'jabatan',
        'join_date',
        'start_date',
        'end_date',
        'status',
        'current_stage',
        'total_score',
    ];

    protected $casts = [
        'join_date'   => 'date',
        'start_date'  => 'date',
        'end_date'    => 'date',
        'total_score' => 'decimal:2',
    ];

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function sectionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'section_head_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(InternEvaluationScore::class);
    }

    public function recommendation(): HasOne
    {
        return $this->hasOne(InternEvaluationRecommendation::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(InternEvaluationApproval::class);
    }
}