<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intern extends Model
{
    protected $fillable = [
        'npk',
        'name',
        'gender',
        'department_id',
        'section_id',
        'role_level',
        'jabatan',
        'start_contract',
        'end_contract',
        'station',
        'area',
        'line',
    ];

    protected $casts = [
        'start_contract' => 'date',
        'end_contract'   => 'date',
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
    if (!$this->end_contract) return false;
    $daysLeft = Carbon::today()->diffInDays($this->end_contract, false);
    return $daysLeft >= 0 && $daysLeft <= 30;
}

public function getDaysUntilExpiryAttribute(): ?int
{
    if (!$this->end_contract) return null;
    return (int) Carbon::today()->diffInDays($this->end_contract, false);
}
}