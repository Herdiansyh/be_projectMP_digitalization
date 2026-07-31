<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Add 'completed_permanent' to the evaluations.status check constraint
        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_status");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_status
            CHECK (status IN (
                'draft','submitted_to_section_head','reviewed_by_section_head',
                'submitted_to_manager','approved','rejected',
                'forwarded_to_hr_admin','completed_extended','completed_not_extended','completed_permanent'
            ))
        ");

        // Add 'convert_to_permanent' to the evaluation_approvals.action check constraint
        DB::statement("ALTER TABLE evaluation_approvals DROP CONSTRAINT chk_evaluation_approvals_action");
        DB::statement("
            ALTER TABLE evaluation_approvals
            ADD CONSTRAINT chk_evaluation_approvals_action
            CHECK (action IN (
                'submit','approve','reject','revise',
                'forward_to_hr_admin','extend_contract',
                'close_contract_deactivate','close_contract_delete','convert_to_permanent'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Remove 'completed_permanent' from the evaluations.status check constraint
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

        // Remove 'convert_to_permanent' from the evaluation_approvals.action check constraint
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
};
