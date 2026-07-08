<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyCheckpoint extends Model
{
    protected $fillable = [
        'category_id',
        'description',
        'sequence',
        'main_process',
        'weight',
        'order',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'weight'      => 'integer',
        'order'       => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetencyCategory::class, 'category_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class, 'checkpoint_id');
    }
}