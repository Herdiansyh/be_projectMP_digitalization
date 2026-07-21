<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Employee extends Model
{
    protected $fillable = [
        'npk',
        'name',
        'gender',
        'department_id',
        'section_id',
        'role_level',
        'jabatan',
        'employment_type',
        'join_date',
        'start_contract',
        'end_contract',
       'area_id',
        'line_id',
        'station_id',
        'no_req',
        'is_active',
        'deactivated_at',
        'deactivated_reason',
        ];

protected $casts = [
    'join_date' => 'date',
    'start_contract' => 'date',
    'end_contract'   => 'date',
    'department_id'  => 'integer',
    'section_id'     => 'integer',
    'station_id'     => 'integer',
    'line_id'        => 'integer',
    'area_id'        => 'integer',
    'is_active'      => 'boolean',
    'deactivated_at' => 'datetime',
];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

  public function getIsNearExpiryAttribute(): bool
{
    if (!$this->end_contract || !$this->is_active) return false;
    $daysLeft = Carbon::today()->diffInDays($this->end_contract, false);
    return $daysLeft >= 0 && $daysLeft <= 30;
}

public function replacementRequisition(): HasOne
{
    return $this->hasOne(Requisition::class, 'replacement_employee_id', 'id');
}
    public function contractExtensions()
{
    return $this->hasMany(EvaluationContractExtension::class);
}
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->end_contract) return null;
        return (int) Carbon::today()->diffInDays($this->end_contract, false);
    }

    public function station()
{
    return $this->belongsTo(Station::class);
}

public function area()
{
    return $this->belongsTo(Area::class);
}

public function line()
{
    return $this->belongsTo(Line::class);
}

public function requisition()
{
    return $this->belongsTo(Requisition::class, 'no_req', 'no_req');
}

public function evaluations()
{
    return $this->hasMany(Evaluation::class);
}

public function latestAssessment(): HasOne
{
    return $this->hasOne(EmployeeAssessment::class, 'employee_id')->latestOfMany('assessed_at');
}
}