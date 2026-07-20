<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationContractExtension extends Model
{
    protected $fillable = [
        'evaluation_id',
        'previous_end_contract',
        'new_end_contract',
        'pkwt_number',
        'extend_months',
        'notes',
        'extended_by',
    ];

    protected $casts = [
        'previous_end_contract' => 'date',
        'new_end_contract' => 'date',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function extendedBy()
    {
        return $this->belongsTo(User::class, 'extended_by');
    }
}