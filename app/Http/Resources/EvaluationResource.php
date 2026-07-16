<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        $roleName = $authUser?->roleLevel?->name;

        $isLocked = false;
        if ($roleName === 'Admin' || $roleName === 'HR Admin') {
            $isLocked = false;
        } elseif ($this->current_stage === 'leader') {
            $isLocked = $roleName !== 'Leader';
        } elseif ($this->current_stage === 'section_head') {
            $isLocked = $roleName !== 'Section Head';
        } elseif ($this->current_stage === 'manager') {
            $isLocked = $roleName !== 'Manager';
        }

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'department_id' => $this->department_id,
            'department_head_id' => $this->department_head_id,
            'leader_id' => $this->leader_id,
            'section_head_id' => $this->section_head_id,
            'manager_id' => $this->manager_id,
            'npk' => $this->npk,
            'jabatan' => $this->jabatan,
            'join_date' => $this->join_date?->format('Y-m-d'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'pkwt' => $this->pkwt,
            'status' => $this->status,
            'current_stage' => $this->current_stage,
            'total_score' => $this->total_score,
            'reminder_date' => $this->reminder_date?->format('Y-m-d'),
            'reminder_note' => $this->reminder_note,
            'reminder_sent_at' => $this->reminder_sent_at?->format('Y-m-d H:i:s'),
            'is_locked_for_current_user' => $isLocked,
            'is_leader_fields_locked' => $this->isLeaderFieldsLocked(),
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'npk' => $this->employee->npk,
                'name' => $this->employee->name,
                'jabatan' => $this->employee->jabatan,
                'department_id' => $this->employee->department_id,
                'section_id' => $this->employee->section_id,
                'join_date' => $this->employee->join_date?->format('Y-m-d'),
                'start_contract' => $this->employee->start_contract?->format('Y-m-d'),
                'end_contract' => $this->employee->end_contract?->format('Y-m-d'),
                'employment_type' => $this->employee->employment_type,
            ]),
            'scores' => $this->whenLoaded('scores', fn() => $this->scores->map(function ($score) {
                return [
                    'id' => $score->id,
                    'criteria_id' => $score->criteria_id,
                    'score' => $score->score,
                    'score_x_weight' => $score->score_x_weight,
                    'filled_by_role' => $score->filled_by_role,
                    'filled_by_user_id' => $score->filled_by_user_id,
                    'criteria' => [
                        'id' => $score->criteria?->id,
                        'name' => $score->criteria?->name,
                        'subgroup_id' => $score->criteria?->subgroup_id,
                        'weight' => $score->criteria?->weight,
                        'scale_type' => $score->criteria?->scale_type,
                    ],
                ];
            })),
            'recommendation' => $this->whenLoaded('recommendation', fn() => [
                'employee_status' => $this->recommendation->employee_status,
                'extend_pkwt' => (bool) $this->recommendation->extend_pkwt,
                'pkwt_number' => $this->recommendation->pkwt_number,
                'extend_months' => $this->recommendation->extend_months,
                'notes' => $this->recommendation->notes,
                'created_by' => $this->recommendation->created_by,
            ]),
            'approvals' => $this->whenLoaded('approvals', fn() => $this->approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'role' => $approval->role,
                    'user_id' => $approval->user_id,
                    'action' => $approval->action,
                    'notes' => $approval->notes,
                    'acted_at' => $approval->acted_at?->format('Y-m-d H:i:s'),
                ];
            })),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
