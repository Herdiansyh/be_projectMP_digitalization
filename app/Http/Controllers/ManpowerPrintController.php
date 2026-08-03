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

        $validator = Validator::make(['items' => $decoded], [
            'items'                 => 'required|array|min:1',
            'items.*.subject_type'  => 'required|in:employee,intern',
            'items.*.subject_id'    => 'required|integer',
        ]);

        abort_if($validator->fails(), 422);

        // Untuk saat ini competence matrix hanya berlaku untuk Employee
        // (Intern belum masuk skema station/line assessment yang sama).
        $employeeIds = collect($decoded)
            ->where('subject_type', 'employee')
            ->pluck('subject_id')
            ->unique()
            ->values();

        abort_if($employeeIds->isEmpty(), 404, 'No valid employees to print.');

        $employees = Employee::with(['department', 'section', 'area', 'line', 'station'])
            ->whereIn('id', $employeeIds)
            ->get();

        abort_if($employees->isEmpty(), 404, 'No valid employees found to print.');

        // Grup 1 tabel per Line, seperti contoh dokumen (LINE : Main Assy K1ZA).
        $lineGroups = $employees
            ->filter(fn ($e) => $e->line_id !== null)
            ->groupBy('line_id')
            ->map(function ($employeesInLine) {
                $first = $employeesInLine->first();
                $line  = $first->line;

                // Semua station yang termasuk Line ini.
                // Tidak ada kolom urutan di tabel stations, jadi urut berdasarkan id.
                $stations = Station::where('line_id', $line->id)
                    ->orderBy('id')
                    ->get();

                $rows = $employeesInLine->values()->map(function ($employee) use ($stations) {
                    $cells = $stations->map(function ($station) use ($employee) {
                        $level = $this->latestApprovedLevel($employee->id, $station->id);

                        return [
                            'station_id' => $station->id,
                            'filled'     => $level, // 0..4 → dipetakan ke icon donut
                        ];
                    });

                    return [
                        'employee' => $employee,
                        'cells'    => $cells,
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

        abort_if($lineGroups->isEmpty(), 404, 'None of the selected employees are assigned to a line yet.');

        return view('print.competence-matrix', [
            'lineGroups'  => $lineGroups,
            'evaluatedAt' => now(),
        ]);
    }

    /**
     * Ambil level (0-4) dari assessment approved terbaru untuk kombinasi
     * employee + station tertentu. Mengikuti pembulatan yang sama dengan
     * partial manpower-content (min(4, max(0, round(final_score)))).
     */
    private function latestApprovedLevel(int $employeeId, int $stationId): ?int
    {
        $latest = EmployeeAssessment::with('matrix')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereHas('matrix', fn ($q) => $q->where('station_id', $stationId))
            ->orderByDesc('assessed_at')
            ->first();

        if (!$latest) {
            return null; // belum pernah dinilai di station ini → icon kosong ("Operator baru")
        }

        return min(4, max(0, (int) round($latest->final_score)));
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