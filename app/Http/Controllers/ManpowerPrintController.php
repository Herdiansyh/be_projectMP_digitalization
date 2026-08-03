<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAssessment;
use App\Models\Intern;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManpowerPrintController extends Controller
{
    // Level target tetap untuk baris STD (sementara, sesuai kesepakatan).
    private const STD_TARGET_LEVEL = 3;

    public function printSingle(string $type, int $id)
    {
        abort_unless(in_array($type, ['employee', 'intern']), 404);

        $subject = $type === 'employee'
            ? Employee::with(['department', 'section', 'area', 'line', 'station'])->findOrFail($id)
            : Intern::with(['department', 'section', 'area', 'line', 'station'])->findOrFail($id);

        return view('print.manpower-single', [
            'subject'        => $subject,
            'type'           => $type,
            'stationSummary' => $this->buildStationSummary($type, $subject->id),
        ]);
    }

    /**
     * Sebelumnya: cetak kartu manpower per-orang (manpower-bulk.blade.php).
     * Sekarang : cetak Competence Matrix, dikelompokkan per Line, mengikuti
     *            layout dokumen "08 - PROD - 001".
     */
    public function printBulk(Request $request)
    {
        $decoded = json_decode($request->input('items'), true);

        $validator = $this->makeValidatorForItems($decoded);

        abort_if($validator->fails(), 422);

        $items = collect($decoded);

        // Dukung dua tipe sekaligus: employee & intern. InternList mengirim
        // subject_type=intern; EmployeeList mengirim subject_type=employee.
        $subjects = $items->map(function ($item) {
            return $this->resolveSubject($item['subject_type'], $item['subject_id']);
        })->filter();

        abort_if($subjects->isEmpty(), 404, 'No valid manpower found to print.');

        // Grup 1 tabel per Line.
        $lineGroups = $subjects
            ->filter(fn ($s) => $s->line_id !== null)
            ->groupBy('line_id')
            ->map(function ($subjectsInLine) {
                $first = $subjectsInLine->first();
                $line  = $first->line;
                $lineAbbr = $this->abbreviateLineName($line->name ?? '');

                $stations = Station::where('line_id', $line->id)
                    ->orderBy('id')
                    ->get();

                $rows = $subjectsInLine->values()->map(function ($subject) use ($stations, $lineAbbr) {
                    $cells = $stations->map(function ($station) use ($subject) {
                        $fkNama = $subject instanceof Intern ? 'intern_id' : 'employee_id';

                        $level = $this->latestAssessmentLevel($fkNama, $subject->id, $station->id);

                        return [
                            'station_id' => $station->id,
                            'filled'     => $level, // 0..4 → dipetakan ke icon donut
                        ];
                    });

                    return [
                        'subject'   => $subject,
                        'line_abbr' => $lineAbbr,
                        'cells'     => $cells,
                    ];
                });

                return [
                    'line'         => $line,
                    'area'         => $first->area,
                    'department'   => $first->department,
                    'section'      => $first->section,
                    'stations'     => $stations,
                    'std_level'    => self::STD_TARGET_LEVEL,
                    'rows'         => $rows,
                ];
            })
            ->values();

        abort_if($lineGroups->isEmpty(), 404, 'None of the selected manpower are assigned to a line yet.');

        return view('print.competence-matrix', [
            'lineGroups'  => $lineGroups,
            'evaluatedAt' => now(),
        ]);
    }

    /**
     * Ambil level (0-4) dari assessment approved terbaru untuk kombinasi
     * subjek (employee/intern) + station tertentu. Mengikuti pembulatan yang
     * sama dengan partial manpower-content (min(4, max(0, round(final_score)))).
     */
    private function latestAssessmentLevel(string $fkColumn, int $subjectId, int $stationId): ?int
    {
        $latest = EmployeeAssessment::with('matrix')
            ->where($fkColumn, $subjectId)
            ->where('status', 'approved')
            ->whereHas('matrix', fn ($q) => $q->where('station_id', $stationId))
            ->orderByDesc('assessed_at')
            ->first();

        if (!$latest) {
            return null; // belum pernah dinilai di station ini → icon kosong ("Operator baru")
        }

        return min(4, max(0, (int) round($latest->final_score)));
    }

    /**
     * Singkatan nama Line: huruf pertama kata pertama + huruf pertama kata kedua.
     * Contoh: "Main Assy K1ZA" -> "MA", "LINE 1" -> "L1".
     */
    private function abbreviateLineName(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];

        $first  = mb_substr($words[0] ?? '', 0, 1);
        $second = mb_substr($words[1] ?? '', 0, 1);

        $abbr = mb_strtoupper($first . $second);

        return $abbr !== '' ? $abbr : '-';
    }

    /**
     * Bangun validator valid untuk array $decoded dari input 'items'.
     */
    private function makeValidatorForItems(array $decoded)
    {
        return Validator::make(['items' => $decoded], [
            'items'                 => 'required|array|min:1',
            'items.*.subject_type'  => 'required|in:employee,intern',
            'items.*.subject_id'    => 'required|integer',
        ]);
    }

    /**
     * Load entity employee/intern berdasarkan tipe + id, dengan relasi yang
     * dibutuhkan view competence-matrix. Mengembalikan null bila tidak ketemu.
     */
    private function resolveSubject(string $type, int $id)
    {
        $query = $type === 'intern'
            ? Intern::query()
            : Employee::query();

        return $query
            ->with(['department', 'section', 'area', 'line', 'station'])
            ->find($id);
    }

    private function buildStationSummary(string $type, int $subjectId): array
    {
        $fk = $type === 'employee' ? 'employee_id' : 'intern_id';

        $assessments = EmployeeAssessment::with(['matrix.station', 'scores.checkpoint.category'])
            ->where($fk, $subjectId)
            ->where('status', 'approved')
            ->orderByDesc('assessed_at')
            ->get()
            ->filter(fn ($a) => $a->matrix?->station);

        return $assessments
            ->groupBy(fn ($a) => $a->matrix->station_id)
            ->map(function ($group) {
                $latest = $group->first();
                return [
                    'station_name' => $latest->matrix->station->name,
                    'final_score'  => $latest->final_score,
                    'period_label' => $latest->period_label,
                    'assessed_at'  => $latest->assessed_at,
                ];
            })
            ->values()
            ->all();
    }
}