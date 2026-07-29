<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE evaluations ALTER COLUMN employee_id BIGINT NULL");

        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('intern_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('interns')
                ->cascadeOnDelete();

            $table->index(['intern_id', 'status']);
        });

        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_subject_exclusive
            CHECK (
                (employee_id IS NOT NULL AND intern_id IS NULL)
                OR (employee_id IS NULL AND intern_id IS NOT NULL)
            )
        ");

        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_status");
        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT chk_evaluations_status
            CHECK (status IN (
                'draft','submitted_to_section_head','reviewed_by_section_head',
                'submitted_to_manager','approved','rejected',
                'forwarded_to_hr_admin','completed_extended','completed_not_extended',
                'completed_promoted'
            ))
        ");
    }

    public function down(): void
    {
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
        DB::statement("ALTER TABLE evaluations DROP CONSTRAINT chk_evaluations_subject_exclusive");

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('intern_id');
        });

        DB::statement("ALTER TABLE evaluations ALTER COLUMN employee_id BIGINT NOT NULL");
    }
};