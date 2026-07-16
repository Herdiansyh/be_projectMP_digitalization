<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationRecommendation extends Model
{
    protected $fillable = [
        'evaluation_id',
        'employee_status',
        'extend_pkwt',
        'pkwt_number',
        'extend_months',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'evaluation_id' => 'integer',
        'extend_pkwt' => 'boolean',
        'created_by' => 'integer',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
