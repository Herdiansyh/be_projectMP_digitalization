<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'group',
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
        'description',
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
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'max_age' => 'integer',
        'min_experience' => 'integer',
        'hrd_approved' => 'boolean',
        'manager_approved_at' => 'datetime',
        'division_approved_at' => 'datetime',
        'director_approved_at' => 'datetime',
        'technical_skill' => 'array',
        'soft_skill' => 'array',
    ];

    /**
     * Scope to filter by approval status.
     */
    public function scopeByStatus($query, $status)
    {
        $statuses = explode(',', $status);
        return $query->whereIn('approval_status', $statuses);
    }

    /**
     * Scope to filter by manager.
     */
    public function scopeByManager($query, $manager)
    {
        return $query->where('manager', $manager);
    }

    /**
     * Scope to filter by division.
     */
    public function scopeByDivision($query, $division)
    {
        return $query->where('division', $division);
    }

    /**
     * Scope to filter by director.
     */
    public function scopeByDirector($query, $director)
    {
        return $query->where('director', $director);
    }

    /**
     * Scope to filter by supervisor.
     */
    public function scopeBySupervisor($query, $supervisor)
    {
        return $query->where('supervisor', $supervisor);
    }

    /**
     * Check if requisition is approved by manager.
     */
    public function isManagerApproved(): bool
    {
        return !is_null($this->manager_approved_at);
    }

    /**
     * Check if requisition is approved by division.
     */
    public function isDivisionApproved(): bool
    {
        return !is_null($this->division_approved_at);
    }

    /**
     * Check if requisition is approved by director.
     */
    public function isDirectorApproved(): bool
    {
        return !is_null($this->director_approved_at);
    }

    /**
     * Check if requisition is fully approved.
     */
    public function isFullyApproved(): bool
    {
        return $this->isManagerApproved() 
            && $this->isDivisionApproved() 
            && $this->isDirectorApproved();
    }

    /**
     * Check if requisition is rejected.
     */
    public function isRejected(): bool
    {
        return str_contains($this->approval_status, 'Rejected');
    }
}
