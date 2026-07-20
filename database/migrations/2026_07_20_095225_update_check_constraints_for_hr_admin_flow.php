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

        // evaluations.status
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

        // evaluations.current_stage
        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_current_stage");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_current_stage
            CHECK (current_stage IN ('leader','section_head','manager','done','hr_admin','completed'))
        ");

        // evaluation_approvals.role
        DB::statement("ALTER TABLE evaluation_approvals DROP CONSTRAINT chk_evaluation_approvals_role");
        DB::statement("
            ALTER TABLE evaluation_approvals
            ADD CONSTRAINT chk_evaluation_approvals_role
            CHECK (role IN ('leader','section_head','manager','hr_admin'))
        ");

        // evaluation_approvals.action
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
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_status");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_status
            CHECK (status IN ('draft','submitted_to_section_head','reviewed_by_section_head',
                               'submitted_to_manager','approved','rejected'))
        ");

        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_current_stage");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_current_stage
            CHECK (current_stage IN ('leader','section_head','manager','done'))
        ");

        DB::statement("ALTER TABLE evaluation_approvals DROP CONSTRAINT chk_evaluation_approvals_role");
        DB::statement("
            ALTER TABLE evaluation_approvals
            ADD CONSTRAINT chk_evaluation_approvals_role
            CHECK (role IN ('leader','section_head','manager'))
        ");

        DB::statement("ALTER TABLE evaluation_approvals DROP CONSTRAINT chk_evaluation_approvals_action");
        DB::statement("
            ALTER TABLE evaluation_approvals
            ADD CONSTRAINT chk_evaluation_approvals_action
            CHECK (action IN ('submit','approve','reject','revise'))
        ");
    }
};