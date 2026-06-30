<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'npk',
        'name',
        'gender',
        'department_id',
        'section_id',
        'role_level_id',
        'jabatan',
        'employment_type',
        'status',
        'start_contract',
        'end_contract',
        'station',
        'area'
    ];

    protected $casts = [
        'start_contract' => 'date',
        'end_contract'   => 'date',
    ];

    // ── Relasi ──

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function roleLevel(): BelongsTo
    {
        return $this->belongsTo(RoleLevel::class);
    }

    // ── Accessor ──

    /**
     * True jika end_contract <= 30 hari dari sekarang
     */
 public function getIsNearExpiryAttribute(): bool
{
    if (!$this->end_contract) return false;

    $daysLeft = Carbon::today()->diffInDays($this->end_contract, false);

    return $daysLeft >= 0 && $daysLeft <= 30;
}
    /**
     * Jumlah hari tersisa hingga end_contract
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->end_contract) return null;

        return (int) Carbon::today()->diffInDays($this->end_contract, false);
    }
}