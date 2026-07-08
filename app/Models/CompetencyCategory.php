<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyCategory extends Model
{
    protected $fillable = [
        'matrix_id',
        'name',
        'order',
    ];

    protected $casts = [
        'matrix_id' => 'integer',
        'order'     => 'integer',
    ];

    public function matrix(): BelongsTo
    {
        return $this->belongsTo(CompetencyMatrix::class, 'matrix_id');
    }

    /**
     * "Parent" kategori punya banyak "child" checkpoint.
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(CompetencyCheckpoint::class, 'category_id')->orderBy('order');
    }
}