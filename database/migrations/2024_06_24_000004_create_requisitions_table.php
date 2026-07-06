<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->string('no_req')->primary();
            $table->string('requester_name');
            $table->dateTime('request_date');
            $table->string('department')->nullable();
            $table->string('section')->nullable();
            $table->string('position')->nullable();
            $table->string('status')->nullable();
            $table->string('duration')->nullable();
            $table->string('level')->nullable();
            $table->string('cost_employee')->nullable();
            $table->string('fulfilment_time')->nullable();
            $table->string('education')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('min_experience')->nullable();
            $table->text('technical_skill')->nullable();
            $table->text('soft_skill')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('objective')->nullable();
            $table->text('reason')->nullable();
            $table->string('employee_out')->nullable();
            $table->unsignedBigInteger('replacement_employee_id')->nullable();
            $table->boolean('apprenticeship_period')->default(false);
            $table->text('manpower_plan')->nullable();
            $table->text('unplanned_reason')->nullable();

            // ── Approval flow ──
            // Default diselaraskan dengan FptkController::store() (bahasa Inggris)
            $table->string('approval_status')->default('Waiting for Manager Approval');
            $table->string('manager')->nullable();
            $table->string('division')->nullable();
            $table->string('director')->nullable();
            $table->string('supervisor')->nullable();
            $table->boolean('hrd_approved')->default(false);
            $table->text('rejection_reason')->nullable();
            // Menandai siapa & kapan FPTK ini di-reject, independen dari role
            // approver-nya (manager/division/director), karena kolom
            // manager/division/director hanya menyimpan approver chain saat
            // FPTK dibuat — bukan siapa yang benar-benar mengambil aksi.
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamp('division_approved_at')->nullable();
            $table->timestamp('director_approved_at')->nullable();

            // ── HRD processing (dulu migration terpisah) ──
            $table->timestamp('hrd_processed_at')->nullable();
            $table->string('hrd_processed_by')->nullable();

            // ── Manpower assignment oleh HRD (step 3) ──
            $table->string('assigned_npk')->nullable();
            $table->string('assigned_name')->nullable();
            $table->date('assigned_start_contract')->nullable();
            $table->date('assigned_end_contract')->nullable();
            $table->timestamp('hrd_assigned_at')->nullable();
            $table->string('hrd_assigned_by')->nullable();

            // ── Area/line assignment oleh requester (step 5) ──
            $table->string('assigned_area')->nullable();
            $table->string('assigned_line')->nullable();
            $table->string('assigned_station')->nullable();
            $table->timestamp('area_line_filled_at')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('intern_id')->nullable();

            $table->timestamps();

            // ── Indexes ──
            $table->index('requester_name');
            $table->index('approval_status');
            $table->index('request_date');
            $table->index('manager');
            $table->index('division');
            $table->index('director');
            $table->index('supervisor');
            $table->index('rejected_by');
            $table->index('assigned_station');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};