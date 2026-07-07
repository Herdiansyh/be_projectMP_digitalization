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
            $table->string('approval_status')->default('Waiting for Manager Approval');
            $table->string('manager')->nullable();
            $table->string('division')->nullable();
            $table->string('director')->nullable();
            $table->string('supervisor')->nullable();
            $table->boolean('hrd_approved')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamp('division_approved_at')->nullable();
            $table->timestamp('director_approved_at')->nullable();

            // ── HRD processing ──
            $table->timestamp('hrd_processed_at')->nullable();
            $table->string('hrd_processed_by')->nullable();

            // ── Manpower assignment oleh HRD (step 3) ──
            $table->string('assigned_npk')->nullable();
            $table->string('assigned_name')->nullable();
            $table->date('assigned_start_contract')->nullable();
            $table->date('assigned_end_contract')->nullable();
            $table->timestamp('hrd_assigned_at')->nullable();
            $table->string('hrd_assigned_by')->nullable();

            // Menampung array kandidat (npk, name, start_contract, end_contract)
            // sementara, di antara tahap assignManpower dan assignAreaLine
            // (mendukung banyak kandidat per FPTK — lihat FptkController).
            $table->json('pending_candidates')->nullable();

            // ── Area/line/station assignment oleh requester (step 5) ──
            $table->string('assigned_station')->nullable();
            $table->timestamp('area_line_filled_at')->nullable();

            // Kolom lama, dipertahankan untuk kompatibilitas relasi
            // assignedEmployee()/assignedIntern() — tidak lagi diisi
            // di flow multi-kandidat (lihat Requisition::employees()/interns()
            // yang menghubungkan lewat employees.no_req / interns.no_req).
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('intern_id')->nullable();

            $table->foreignId('area_id')->nullable();
            $table->foreignId('line_id')->nullable();
            $table->foreignId('station_id')->nullable();

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

            // ── Foreign keys ──
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('no action');
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('no action');
            $table->foreign('station_id')->references('id')->on('stations')->nullOnDelete();
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