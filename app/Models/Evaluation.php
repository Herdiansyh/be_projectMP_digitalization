<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Evaluation extends Model
{
    protected $fillable = [
        'employee_id',
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
        'pkwt',
        'status',
        'current_stage',
        'total_score',
        'reminder_date',
        'reminder_note',
        'reminder_sent_at',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'department_id' => 'integer',
        'department_head_id' => 'integer',
        'leader_id' => 'integer',
        'section_head_id' => 'integer',
        'manager_id' => 'integer',
        'join_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'reminder_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'total_score' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function departmentHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head_id');
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
        return $this->hasMany(EvaluationScore::class);
    }

    public function recommendation(): HasOne
    {
        return $this->hasOne(EvaluationRecommendation::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(EvaluationApproval::class);
    }

    public function isEditableByRole(string $role): bool
    {
        return match ($role) {
            'leader' => in_array($this->status, ['draft', 'rejected'], true),
            'section_head' => $this->status === 'submitted_to_section_head',
            'manager' => $this->status === 'submitted_to_manager',
            default => false,
        };
    }

    public function isLeaderFieldsLocked(): bool
    {
        return in_array($this->current_stage, ['section_head', 'manager', 'done'], true);
    }

    public function moveToNextStage(): void
    {
        if ($this->current_stage === 'leader') {
            $this->current_stage = 'section_head';
            $this->status = 'submitted_to_section_head';
        } elseif ($this->current_stage === 'section_head') {
            $this->current_stage = 'manager';
            $this->status = 'submitted_to_manager';
        } elseif ($this->current_stage === 'manager') {
            $this->current_stage = 'done';
            $this->status = 'approved';
        }
    }

    public function recalculateTotalScore(): void
{
    $shScores = $this->scores()
        ->where('filled_by_role', 'section_head')
        ->with('criteria')
        ->get();

    if ($shScores->isEmpty()) {
        $this->total_score = null;
        $this->saveQuietly();
        return;
    }

    $totalWeighted = $shScores->sum('score_x_weight');
    $totalWeight = $shScores->sum(fn ($s) => $s->criteria->weight ?? 0);

    $this->total_score = $totalWeight > 0
        ? round($totalWeighted / $totalWeight, 2)
        : null;

    $this->saveQuietly();
}
}
