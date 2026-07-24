<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAssessment;
use App\Models\Intern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManpowerPrintController extends Controller
{
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

    public function printBulk(Request $request)
    {
        // Form-submit POST mengirim 'items' sebagai string JSON tunggal,
        // bukan array biasa — decode dulu sebelum divalidasi.
        $decoded = json_decode($request->input('items'), true);

        $validator = Validator::make(['items' => $decoded], [
            'items'                 => 'required|array|min:1',
            'items.*.subject_type'  => 'required|in:employee,intern',
            'items.*.subject_id'    => 'required|integer',
        ]);

        abort_if($validator->fails(), 422);

        $items = collect($decoded)->map(function ($item) {
            $type = $item['subject_type'];
            $id   = $item['subject_id'];

            $subject = $type === 'employee'
                ? Employee::with(['department', 'section', 'area', 'line', 'station'])->find($id)
                : Intern::with(['department', 'section', 'area', 'line', 'station'])->find($id);

            if (!$subject) {
                return null;
            }

            return [
                'subject'        => $subject,
                'type'           => $type,
                'stationSummary' => $this->buildStationSummary($type, $id),
            ];
        })->filter()->values();

        abort_if($items->isEmpty(), 404, 'No valid manpower found to print.');

        return view('print.manpower-bulk', ['items' => $items]);
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