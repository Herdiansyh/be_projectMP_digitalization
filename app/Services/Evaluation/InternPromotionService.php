<?php

namespace App\Services\Evaluation;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\EvaluationApproval;
use App\Models\Intern;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InternPromotionService
{
    public function extend(Evaluation $evaluation, User $user, array $payload): Evaluation
{
    if ($evaluation->current_stage !== 'hr_admin' || empty($evaluation->intern_id)) {
        throw new \RuntimeException('Evaluation is not pending HR Admin decision for an Intern');
    }

    if (empty($payload['extend_months'])) {
        throw new \RuntimeException('Extend duration (months) is required');
    }

    if (empty($payload['start_date'])) {
        throw new \RuntimeException('Start date is required');
    }

    $intern = Intern::findOrFail($evaluation->intern_id);

    $newStartContract = Carbon::parse($payload['start_date']);
    $newEndContract = $newStartContract->copy()->addMonths((int) $payload['extend_months']);

    DB::transaction(function () use ($payload, $evaluation, $intern, $user, $newStartContract, $newEndContract) {
        $employee = Employee::create([
            'npk' => $intern->npk,
            'name' => $intern->name,
            'gender' => $intern->gender,
            'department_id' => $intern->department_id,
            'section_id' => $intern->section_id,
            'role_level' => $intern->role_level,
            'jabatan' => $intern->jabatan,
            'employment_type' => 'contract',
            'join_date' => $intern->join_date,
            'start_contract' => $newStartContract->toDateString(),
            'end_contract' => $newEndContract->toDateString(),
            'area_id' => $intern->area_id,
            'line_id' => $intern->line_id,
            'station_id' => $intern->station_id,
            'no_req' => $intern->no_req,
            'is_active' => true,
            'group' => $intern->group,
        ]);

        $intern->update([
            'outcome_status' => 'converted',
            'converted_employee_id' => $employee->id,
            'outcome_at' => now(),
            'outcome_note' => $payload['notes'] ?? null,
        ]);

        $evaluation->update([
            'status' => 'completed_extended',
            'current_stage' => 'completed',
        ]);

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => 'hr_admin',
            'user_id' => $user->id,
            'action' => 'extend_contract',
            'notes' => $payload['notes'] ?? null,
            'acted_at' => now(),
        ]);
    });

    return $evaluation->fresh();
}

    /**
     * Kontrak/masa magang Intern tidak dilanjutkan. Mirror
     * EmployeeContractService::close(), pakai action & status yang SAMA
     * PERSIS dengan Employee ('close_contract_deactivate' /
     * 'close_contract_delete' / 'completed_not_extended').
     */
  public function close(Evaluation $evaluation, User $user, array $payload): Evaluation
{
    if ($evaluation->current_stage !== 'hr_admin' || empty($evaluation->intern_id)) {
        throw new \RuntimeException('Evaluation is not pending HR Admin decision for an Intern');
    }

    $intern = Intern::findOrFail($evaluation->intern_id);

    DB::transaction(function () use ($payload, $evaluation, $intern, $user) {
        // Catat approval log & update status evaluation TERLEBIH DAHULU,
        // sebelum intern (dan evaluation via cascade) dihapus.
        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => 'hr_admin',
            'user_id' => $user->id,
            'action' => 'close_contract_' . $payload['action'],
            'notes' => $payload['reason'] ?? null,
            'acted_at' => now(),
        ]);

        $evaluation->update([
            'status' => 'completed_not_extended',
            'current_stage' => 'completed',
        ]);

        if ($payload['action'] === 'deactivate') {
            $intern->update([
                'outcome_status' => 'ended',
                'outcome_at' => now(),
                'outcome_note' => $payload['reason'] ?? 'Contract ended, not extended',
            ]);
        } else {
            $intern->delete();
        }
    });

    return $evaluation->fresh();
}
}