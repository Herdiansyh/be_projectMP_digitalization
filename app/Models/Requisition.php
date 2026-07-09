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
        'type',
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

        'assigned_npk',
        'assigned_name',
        'assigned_start_contract',
        'assigned_end_contract',
        'hrd_assigned_at',
        'hrd_assigned_by',
        'assigned_area',
        'assigned_line',
        'assigned_station',
        'area_line_filled_at',
        'employee_id',
        'intern_id',
        'rejected_by',
        'rejected_at', 
        'area_id',
        'line_id',
        'station_id', 
        'pending_candidates',
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

        // ── Tambahan ──
        'assigned_start_contract' => 'date',
        'assigned_end_contract'   => 'date',
        'hrd_assigned_at'         => 'datetime',
        'area_line_filled_at'     => 'datetime',
        'replacement_employee_id' => 'integer',
        'employee_id'             => 'integer',
        'intern_id'                => 'integer',
        'station_id'               => 'integer',
        'area_id'    => 'integer',
        'line_id'    => 'integer',
        'pending_candidates' => 'array',

        ];
    protected $appends = ['needs_area_line'];
 
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
public function station()
{
    return $this->belongsTo(Station::class);
}
    public function scopeByDirector($query, $director)
    {
        return $query->where('director', $director);
    }

    public function scopeBySupervisor($query, $supervisor)
    {
        return $query->where('supervisor', $supervisor);
    }


public function scopeNeedsAreaLine($query)
{
    return $query->whereNotNull('hrd_assigned_at')
        ->whereNull('area_line_filled_at');
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

    /**
     * Department yang mewajibkan pengisian Line (bukan hanya Area).
     */
    public function requiresLine(): bool
    {
        return strcasecmp((string) $this->department, 'Manufacturing') === 0;
    }

  
public function getNeedsAreaLineAttribute(): bool
{
    return !is_null($this->hrd_assigned_at)
        && is_null($this->area_line_filled_at);
}
    // ── Relasi ───────────────────────────────────────────────────────────────

    public function replacementEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assignedIntern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    public function area()
{
    return $this->belongsTo(Area::class);
}

public function line()
{
    return $this->belongsTo(Line::class);
}

public function employees()
{
    return $this->hasMany(Employee::class, 'no_req', 'no_req');
}

public function interns()
{
    return $this->hasMany(Intern::class, 'no_req', 'no_req');
}
}