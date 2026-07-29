<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // evaluation_approvals.action — tambah nilai Intern
        DB::statement("ALTER TABLE evaluation_approvals DROP CONSTRAINT chk_evaluation_approvals_action");
        DB::statement("
            ALTER TABLE evaluation_approvals
            ADD CONSTRAINT chk_evaluation_approvals_action
            CHECK (action IN (
                'submit','approve','reject','revise',
                'forward_to_hr_admin',
                'extend_contract',
                'close_contract_deactivate','close_contract_delete',
                'promote_to_employee',
                'intern_not_extend'
            ))
        ");

        // evaluations.status — tambah 'completed_promoted' untuk intern
        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_status");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_status
            CHECK (status IN (
                'draft','submitted_to_section_head','reviewed_by_section_head',
                'submitted_to_manager','approved','rejected',
                'forwarded_to_hr_admin',
                'completed_extended','completed_not_extended',
                'completed_promoted'
            ))
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Rollback ke state setelah 2026_07_20_..._update_check_constraints_for_hr_admin_flow
        DB::statement("ALTER TABLE evaluation_approvals DROP CONSTRAINT chk_evaluation_approvals_action");
        DB::statement("
            ALTER TABLE evaluation_approvals
            ADD CONSTRAINT chk_evaluation_approvals_action
            CHECK (action IN (
                'submit','approve','reject','revise',
                'forward_to_hr_admin','extend_contract',
                'close_contract_deactivate','close_contract_delete'
            ))
        ");

        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_status");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_status
            CHECK (status IN (
                'draft','submitted_to_section_head','reviewed_by_section_head',
                'submitted_to_manager','approved','rejected',
                'forwarded_to_hr_admin','completed_extended','completed_not_extended'
            ))
        ");
    }
};
