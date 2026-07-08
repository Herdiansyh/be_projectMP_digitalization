<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyMatrix extends Model
{
    protected $fillable = [
        'station_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'station_id' => 'integer',
        'is_active'  => 'boolean',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * A matrix "has many" categories — satu matrix bisa punya banyak kategori.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(CompetencyCategory::class, 'matrix_id')->orderBy('order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(EmployeeAssessment::class, 'matrix_id');
    }
}