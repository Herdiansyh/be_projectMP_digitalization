<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAssessment;
use App\Models\Evaluation;
use App\Models\Intern;
use App\Models\Requisition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Ringkasan dashboard, dibangun per-widget berdasarkan hak akses user.
     * Pola ini meniru EmployeeAssessmentController::monitoring(): tiap widget
     * di-guard sendiri, hanya data yang authorized yang dikirim.
     *
     * Scope data mengikuti rule masing-masing modul:
     * - FPTK        : Admin/HR Admin = semua; lainnya = FPTK yang terkait
     *                 nama/department user (sama seperti FptkController::index()).
     * - Manpower    : global — can_view_manpower dianggap akses penuh.
     * - Competency  : Admin/QA = semua; lainnya = assessment di area user
     *                 (sama seperti EmployeeAssessmentController::assessableEmployees()).
     * - Evaluations : Admin/HR Admin = semua; Leader/Section Head/Manager =
     *                 evaluasi yang melibatkan mereka (sama seperti EvaluationController::index()).
     *
     * Selain ringkasan angka, dikirim juga data chart (trend bulanan, distribusi,
     * manpower per departemen) yang di-guard dengan permission yang sama.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $widgets = [];

        if ($user->hasPermission('fptk.view_list')) {
            $widgets['fptk_summary']   = $this->fptkSummary();
            $widgets['fptk_trend']     = $this->fptkTrend();
        }

        if ($user->can_view_manpower) {
            $widgets['manpower_summary']         = $this->manpowerSummary();
            $widgets['manpower_by_department']   = $this->manpowerByDepartment();
        }

        if ($user->hasPermission('competency.monitor')) {
            $widgets['competency_summary'] = $this->competencySummary();
            $widgets['competency_trend']   = $this->competencyTrend();
        }

        if ($user->hasPermission('evaluations.view')) {
            $widgets['evaluations_summary'] = $this->evaluationsSummary();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'widgets'         => $widgets,
                'permission_keys' => $user->permissionKeys(),
            ],
        ]);
    }

    private function fptkSummary(): array
    {
        $query = Requisition::query();

        $this->scopeFptkToUser($query);

        // Hitung per status langsung di SQL — hindari load seluruh baris.
        $byStatus = $query
            ->selectRaw('approval_status, COUNT(*) as total')
            ->groupBy('approval_status')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'approval_status');

        return [
            'total'     => $byStatus->sum(),
            'by_status' => $byStatus,
        ];
    }

    /**
     * Tren jumlah FPTK per bulan (6 bulan terakhir, termasuk bulan berjalan).
     * Perhitungan grouped di SQL per bulan — hanya satu aggregate query.
     * SQL Server: YEAR() + MONTH() untuk group, label pakai Carbon (PHP).
     */
    private function fptkTrend(): array
    {
        $query = Requisition::query();

        $this->scopeFptkToUser($query);

        $start = now()->subMonths(5)->startOfMonth();

        $rows = (clone $query)
            ->selectRaw("CONVERT(varchar(7), request_date, 120) as month, COUNT(*) as total")
            ->where('request_date', '>=', $start)
            ->groupByRaw("CONVERT(varchar(7), request_date, 120)")
            ->get()
            ->keyBy('month');

        return collect(range(5, 0))->map(function (int $i) use ($rows) {
            $month = now()->subMonths($i)->format('Y-m');

            return [
                'month' => $month,
                'label' => now()->subMonths($i)->format('M'),
                'total' => (int) ($rows[$month]->total ?? 0),
            ];
        })->values()->all();
    }

    private function manpowerSummary(): array
    {
        return [
            'employees'          => Employee::count(),
            'active_employees'   => Employee::where('is_active', true)->count(),
            'interns'            => Intern::count(),
            'active_interns'     => Intern::where('outcome_status', 'active')->count(),
        ];
    }

    /**
     * Jumlah manpower (employee + intern) per departemen, 8 teratas.
     * Dua query aggregate terpisah (employee & intern) lalu digabung di PHP.
     */
    private function manpowerByDepartment(): array
    {
        $employees = Employee::query()
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->selectRaw('departments.name, COUNT(*) as total')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->pluck('total', 'name');

        $interns = Intern::query()
            ->join('departments', 'departments.id', '=', 'interns.department_id')
            ->selectRaw('departments.name, COUNT(*) as total')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->pluck('total', 'name');

        $names = $employees->keys()->merge($interns->keys())->unique();

        return $names->map(fn (string $name) => [
            'name'      => $name,
            'employees' => (int) ($employees[$name] ?? 0),
            'interns'   => (int) ($interns[$name] ?? 0),
        ])->values()->all();
    }

    private function competencySummary(): array
    {
        $query = EmployeeAssessment::query();

        $this->scopeCompetencyToUser($query);

        return [
            'total_approved' => (clone $query)->where('status', 'approved')->count(),
            'pending_qa'     => (clone $query)->where('status', 'pending_QA')->count(),
        ];
    }

    /**
     * Rata-rata skor akhir assessment approved per bulan (6 bulan terakhir).
     * final_score tidak tersimpan sebagai kolom, jadi dihitung di PHP dari
     * skor yang sudah di-eager-load — dibatasi rentang 6 bulan supaya ringan.
     */
    private function competencyTrend(): array
    {
        $query = EmployeeAssessment::query();

        $this->scopeCompetencyToUser($query);

        $start = now()->subMonths(5)->startOfMonth();

        $assessments = (clone $query)
            ->with('scores.checkpoint.category')
            ->where('status', 'approved')
            ->where('assessed_at', '>=', $start)
            ->get()
            ->groupBy(fn (EmployeeAssessment $assessment) => $assessment->assessed_at?->format('Y-m'));

        return collect(range(5, 0))->map(function (int $i) use ($assessments) {
            $month = now()->subMonths($i)->format('Y-m');
            $items = $assessments->get($month, collect());

            return [
                'month'     => $month,
                'label'     => now()->subMonths($i)->format('M'),
                'avg_score' => $items->isNotEmpty()
                    ? round($items->avg(fn (EmployeeAssessment $a) => (float) $a->final_score), 2)
                    : 0,
            ];
        })->values()->all();
    }

    private function evaluationsSummary(): array
    {
        $query = Evaluation::query();

        $this->scopeEvaluationsToUser($query);

        return [
            'total'       => (clone $query)->count(),
            'in_progress' => (clone $query)->whereIn('status', [
                'draft',
                'submitted_to_section_head',
                'reviewed_by_section_head',
                'submitted_to_manager',
            ])->count(),
            'pending_hr'  => (clone $query)->where('status', 'forwarded_to_hr_admin')->count(),
            'approved'    => (clone $query)->where('status', 'approved')->count(),
            'rejected'    => (clone $query)->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Scope FPTK mengikuti FptkController::index().
     */
    private function scopeFptkToUser($query): void
    {
        $user = Auth::user();

        if (in_array($user->roleLevel?->name, ['Admin', 'HR Admin'], true)) {
            return;
        }

        $query->where(function ($q) use ($user) {
            $q->where('requester_name', $user->name)
              ->orWhere('manager', $user->name)
              ->orWhere('division', $user->name)
              ->orWhere('director', $user->name)
              ->orWhere('supervisor', $user->name);

            if ($user->department?->name) {
                $q->orWhere('department', $user->department->name);
            }
        });
    }

    /**
     * Scope assessment mengikuti EmployeeAssessmentController::assessableEmployees():
     * Admin/QA melihat semua; lainnya hanya assessment di area miliknya.
     */
    private function scopeCompetencyToUser($query): void
    {
        $user = Auth::user();

        if (
            $user->is_admin
            || $user->roleLevel?->name === 'Admin'
            || $user->roleLevel?->name === 'Quality Assurance'
        ) {
            return;
        }

        if (is_null($user->area_id)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($q) use ($user) {
            $q->whereHas('employee', fn ($sub) => $sub->where('area_id', $user->area_id))
              ->orWhereHas('intern', fn ($sub) => $sub->where('area_id', $user->area_id));
        });
    }

    /**
     * Scope evaluasi mengikuti EvaluationController::index().
     */
    private function scopeEvaluationsToUser($query): void
    {
        $user = Auth::user();
        $roleName = $user->roleLevel?->name;

        switch ($roleName) {
            case 'Admin':
            case 'HR Admin':
                return;

            case 'Leader':
                $query->where('leader_id', $user->id);

                return;

            case 'Section Head':
                $query->where('section_head_id', $user->id);

                return;

            case 'Manager':
                $query->where('manager_id', $user->id);

                return;

            default:
                $query->whereRaw('1 = 0');
        }
    }
}
