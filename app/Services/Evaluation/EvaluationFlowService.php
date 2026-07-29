<?php

namespace App\Services\Evaluation;

use App\Models\Evaluation;
use App\Models\EvaluationApproval;
use App\Models\User;

class EvaluationFlowService
{
    /**
     * Submit evaluasi draft dari Leader ke Section Head.
     * Validasi SAMA persis untuk Employee maupun Intern (termasuk PKWT wajib —
     * disamakan sesuai keputusan, tidak dibedakan per subjek).
     */
    public function submit(Evaluation $evaluation, User $user): Evaluation
    {
        if ($evaluation->leader_id !== $user->id) {
            throw new \RuntimeException('Unauthorized to submit evaluation');
        }

        if ($evaluation->current_stage !== 'leader') {
            throw new \RuntimeException('Evaluation cannot be submitted from this stage');
        }

        if (empty($evaluation->pkwt)) {
            throw new \RuntimeException('PKWT is required before submitting');
        }

        if (empty($evaluation->section_head_id)) {
            throw new \RuntimeException(
                'You do not have an Approver Section Head assigned. Please contact Admin to set this up before submitting.'
            );
        }

        $evaluation->load('recommendation');
        if (!$evaluation->recommendation || empty($evaluation->recommendation->employee_status)) {
            throw new \RuntimeException('Recommendation is required before submitting');
        }

        $evaluation->status = 'submitted_to_section_head';
        $evaluation->current_stage = 'section_head';
        $evaluation->save();

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => 'leader',
            'user_id' => $user->id,
            'action' => 'submit',
            'notes' => null,
            'acted_at' => now(),
        ]);

        return $evaluation;
    }

    /**
     * Approve evaluasi di stage Section Head atau Manager.
     * Logic SAMA persis untuk Employee maupun Intern.
     */
    public function approve(Evaluation $evaluation, User $user, ?string $notes = null): Evaluation
    {
        $roleName = $user->roleLevel?->name;

        $allowed = match ($evaluation->current_stage) {
            'section_head' => $roleName === 'Section Head' && $evaluation->section_head_id === $user->id,
            'manager' => $roleName === 'Manager' && $evaluation->manager_id === $user->id,
            default => false,
        };

        if (!$allowed) {
            throw new \RuntimeException('Unauthorized to approve this evaluation');
        }

        if ($evaluation->current_stage === 'section_head') {
            // Manager tujuan forward ditentukan di sini, saat Section Head approve —
            // diambil dari approver_manager_id milik Section Head yang bertindak.
            $user->loadMissing('approverManager');

            if (!$user->approverManager) {
                throw new \RuntimeException(
                    'You do not have an Approver Manager assigned. Please contact Admin to set this up before approving.'
                );
            }

            $evaluation->manager_id = $user->approverManager->id;
            $evaluation->status = 'reviewed_by_section_head';
            $evaluation->current_stage = 'manager';
        } elseif ($evaluation->current_stage === 'manager') {
            $evaluation->status = 'approved';
            $evaluation->current_stage = 'done';
        }
        $evaluation->save();

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => $evaluation->current_stage === 'done' ? 'manager' : 'section_head',
            'user_id' => $user->id,
            'action' => 'approve',
            'notes' => $notes,
            'acted_at' => now(),
        ]);

        return $evaluation;
    }

    /**
     * Reject evaluasi — kembali ke Leader untuk direvisi.
     */
    public function reject(Evaluation $evaluation, User $user, string $notes): Evaluation
    {
        $roleName = $user->roleLevel?->name;

        $allowed = match ($evaluation->current_stage) {
            'section_head' => $roleName === 'Section Head' && $evaluation->section_head_id === $user->id,
            'manager' => $roleName === 'Manager' && $evaluation->manager_id === $user->id,
            default => false,
        };

        if (!$allowed) {
            throw new \RuntimeException('Unauthorized to reject this evaluation');
        }

        $rejectedFromStage = $evaluation->current_stage;

        $evaluation->status = 'rejected';
        $evaluation->current_stage = 'leader';
        $evaluation->save();

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => $rejectedFromStage === 'manager' ? 'manager' : 'section_head',
            'user_id' => $user->id,
            'action' => 'reject',
            'notes' => $notes,
            'acted_at' => now(),
        ]);

        return $evaluation;
    }

    /**
     * Section Head forward evaluasi approved ke HR Admin.
     */
    public function forwardToHrAdmin(Evaluation $evaluation, User $user, ?string $notes = null): Evaluation
    {
        if ($evaluation->section_head_id !== $user->id) {
            throw new \RuntimeException('Unauthorized to forward this evaluation');
        }

        if ($evaluation->status !== 'approved' || $evaluation->current_stage !== 'done') {
            throw new \RuntimeException('Evaluation must be fully approved before forwarding to HR Admin');
        }

        $evaluation->status = 'forwarded_to_hr_admin';
        $evaluation->current_stage = 'hr_admin';
        $evaluation->save();

        EvaluationApproval::create([
            'evaluation_id' => $evaluation->id,
            'role' => 'section_head',
            'user_id' => $user->id,
            'action' => 'forward_to_hr_admin',
            'notes' => $notes,
            'acted_at' => now(),
        ]);

        return $evaluation;
    }
}