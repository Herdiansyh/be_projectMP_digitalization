<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requisition extends Model
{
    use HasFactory;

    protected $primaryKey = 'no_req';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_req',
        'requester_name',
        'request_date',
        // 'group',
        'department',
        'section',
        // 'type',
        'position',
        'status',
        'duration',
        'level',
        'cost_employee',
        'fulfilment_time',
        'education',
        'max_age',
        'min_experience',
        'technical_skill',
        'soft_skill',
        // 'description',
        'cost_center',
        'objective',
        'reason',
        'employee_out',
        'manpower_plan',
        'unplanned_reason',
        'approval_status',
        'manager',
        'division',
        'director',
        'supervisor',
        'hrd_approved',
        'rejection_reason',
        'manager_approved_at',
        'division_approved_at',
        'director_approved_at',
        'hrd_processed_at',
        'hrd_processed_by',
        'replacement_employee_id',
        'apprenticeship_period',
    ];

    protected $casts = [
        'request_date'          => 'date',       
        'fulfilment_time'       => 'date',       
        'max_age'               => 'integer',
        'min_experience'        => 'integer',
        'hrd_approved'          => 'boolean',
        'apprenticeship_period' => 'boolean',
        'technical_skill'       => 'array',
        'soft_skill'            => 'array',
        'manager_approved_at'   => 'datetime',
        'division_approved_at'  => 'datetime',
        'director_approved_at'  => 'datetime',
        'hrd_processed_at'      => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeByStatus($query, $status)
    {
        $statuses = explode(',', $status);
        return $query->whereIn('approval_status', $statuses);
    }

    public function scopeByManager($query, $manager)
    {
        return $query->where('manager', $manager);
    }

    public function scopeByDivision($query, $division)
    {
        return $query->where('division', $division);
    }

    public function scopeByDirector($query, $director)
    {
        return $query->where('director', $director);
    }

    public function scopeBySupervisor($query, $supervisor)
    {
        return $query->where('supervisor', $supervisor);
    }

    // ── Status Helpers ───────────────────────────────────────────────────────

    public function isManagerApproved(): bool
    {
        return !is_null($this->manager_approved_at);
    }

    public function isDivisionApproved(): bool
    {
        return !is_null($this->division_approved_at);
    }

    public function isDirectorApproved(): bool
    {
        return !is_null($this->director_approved_at);
    }

    public function isFullyApproved(): bool
    {
        return $this->approval_status === 'Approved';
    }

    public function isProcessedHrd(): bool
    {
        return $this->approval_status === 'Processed HRD';
    }

    public function isRejected(): bool
    {
        return str_contains($this->approval_status, 'Rejected');
    }

    // ── Relasi ───────────────────────────────────────────────────────────────

    public function replacementEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }
}