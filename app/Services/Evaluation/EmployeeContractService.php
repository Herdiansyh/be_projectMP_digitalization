<?php

namespace App\Services\Evaluation;

use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\EvaluationApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeContractService
{
    
public function extend(Evaluation $evaluation, User $user, array $payload): Evaluation
{
    if ($evaluation->current_stage !== 'hr_admin' || empty($evaluation->employee_id)) {
        throw new \RuntimeException('Evaluation is not pending HR Admin decision for an Employee');
    }

    if (empty($payload['start_date'])) {
        throw new \RuntimeException('Start date is required');
    }

    $employee = Employee::findOrFail($evaluation->employee_id);

    $newStartContract = \Carbon\Carbon::parse($payload['start_date']);
    $newEndContract = $newStartContract->copy()->addMonths((int) $payload['extend_months']);

    DB::transaction(function () use ($payload, $evaluation, $employee, $user, $newStartContract, $newEndContract) {
        $evaluation->contractExtensions()->create([
            'previous_end_contract' => $employee->end_contract,
            'new_end_contract' => $newEndContract->toDateString(),
            'extend_months' => $payload['extend_months'],
            'notes' => $payload['notes'] ?? null,
            'extended_by' => $user->id,
        ]);

        $employee->update([
            'start_contract' => $newStartContract->toDateString(),
            'end_contract' => $newEndContract->toDateString(),
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

    public function close(Evaluation $evaluation, User $user, array $payload): Evaluation
    {
        if ($evaluation->current_stage !== 'hr_admin' || empty($evaluation->employee_id)) {
            throw new \RuntimeException('Evaluation is not pending HR Admin decision for an Employee');
        }

        $employee = Employee::findOrFail($evaluation->employee_id);

        DB::transaction(function () use ($payload, $evaluation, $employee, $user) {
            if ($payload['action'] === 'deactivate') {
                $employee->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivated_reason' => $payload['reason'] ?? 'Contract ended, not extended',
                ]);
            } else {
                $employee->delete();
            }

            $evaluation->update([
                'status' => 'completed_not_extended',
                'current_stage' => 'completed',
            ]);

            EvaluationApproval::create([
                'evaluation_id' => $evaluation->id,
                'role' => 'hr_admin',
                'user_id' => $user->id,
                'action' => 'close_contract_' . $payload['action'],
                'notes' => $payload['reason'] ?? null,
                'acted_at' => now(),
            ]);
        });

        return $evaluation->fresh();
    }
}