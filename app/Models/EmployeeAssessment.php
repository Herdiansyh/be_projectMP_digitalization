<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAssessment extends Model
{
  protected $fillable = [
    'employee_id',
    'intern_id',
    'matrix_id',
    'assessed_by',
    'period_label',
    'assessed_at',
    'notes',
    'status',
    'qc_by',
    'qc_at',
];

protected $casts = [
    'employee_id' => 'integer',
    'intern_id'   => 'integer',
    'matrix_id'   => 'integer',
    'assessed_by' => 'integer',
    'qc_by'       => 'integer',
    'assessed_at' => 'datetime',
    'qc_at'       => 'datetime',
];

protected $appends = ['category_scores', 'final_score', 'leader_category_scores'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function matrix(): BelongsTo
    {
        return $this->belongsTo(CompetencyMatrix::class, 'matrix_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class, 'assessment_id');
    }


    public function getSubjectAttribute(): Employee|Intern|null
    {
        return $this->employee ?? $this->intern;
    }

  
//    public function getCategoryScoresAttribute(): array
// {
//     $this->loadMissing('scores.checkpoint.category');

//     $grouped = $this->scores->groupBy(fn ($score) => $score->checkpoint->category_id);

//     $result = [];

//     foreach ($grouped as $categoryId => $scoresInCategory) {
//         $category = $scoresInCategory->first()->checkpoint->category;
//         $totalPoint = $scoresInCategory->sum(
//             fn ($s) => $s->point * $s->checkpoint->weight
//         );
//         $checkpointCount = $scoresInCategory->count();

//         $result[] = [
//             'category_id'      => $categoryId,
//             'category_name'    => $category->name,
//             'total_point'      => $totalPoint,
//             'checkpoint_count' => $checkpointCount,
//             'average'          => $checkpointCount > 0
//                 ? round($totalPoint / $checkpointCount, 2)
//                 : 0,
//         ];
//     }

//     return $result;
// }
public function getCategoryScoresAttribute(): array
{
    return $this->computeCategoryScores('qc');
}

public function getLeaderCategoryScoresAttribute(): array
{
    return $this->computeCategoryScores('leader');
}
   
private function computeCategoryScores(string $source): array
{
    $this->loadMissing('scores.checkpoint.category');

    $scores = $this->scores->where('source', $source);
    $grouped = $scores->groupBy(fn ($score) => $score->checkpoint->category_id);

    $result = [];

    foreach ($grouped as $categoryId => $scoresInCategory) {
        $category = $scoresInCategory->first()->checkpoint->category;
        $totalPoint = $scoresInCategory->sum(
            fn ($s) => $s->point * $s->checkpoint->weight
        );
        $checkpointCount = $scoresInCategory->count();

        $result[] = [
            'category_id'      => $categoryId,
            'category_name'    => $category->name,
            'total_point'      => $totalPoint,
            'checkpoint_count' => $checkpointCount,
            'average'          => $checkpointCount > 0
                ? round($totalPoint / $checkpointCount, 2)
                : 0,
        ];
    }

    return $result;
}

public function getFinalScoreAttribute(): float
{
    $categoryScores = $this->category_scores; // ini sudah source=qc

    if (empty($categoryScores)) {
        return 0;
    }

    $sum = array_sum(array_column($categoryScores, 'average'));

    return round($sum / count($categoryScores), 2);
}


    public function qcReviewer(): BelongsTo
{
    return $this->belongsTo(User::class, 'qc_by');
}


}