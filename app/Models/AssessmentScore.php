<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScore extends Model
{
    public $timestamps = false; // tabel ini tidak punya created_at/updated_at

    protected $fillable = [
        'assessment_id',
        'checkpoint_id',
        'point',
    ];

    protected $casts = [
        'assessment_id' => 'integer',
        'checkpoint_id' => 'integer',
        'point'         => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(EmployeeAssessment::class, 'assessment_id');
    }

    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(CompetencyCheckpoint::class, 'checkpoint_id');
    }
}