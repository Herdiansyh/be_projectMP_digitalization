<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            // NOTE: sesuaikan nama tabel/relasi berikut dengan tabel existing di project Anda
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('department_head_id')->nullable();
            $table->foreign('department_head_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->unsignedBigInteger('leader_id');
            $table->foreign('leader_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->unsignedBigInteger('section_head_id')->nullable();
            $table->foreign('section_head_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->string('npk', 50)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->date('join_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('pkwt', 10)->nullable(); // "1"/"2"/"3"/"4"

            $table->string('status', 30)->default('draft');
            // draft | submitted_to_section_head | reviewed_by_section_head
            // | submitted_to_manager | approved | rejected

            $table->string('current_stage', 20)->default('leader');
            // leader | section_head | manager | done

            $table->decimal('total_score', 6, 2)->nullable();

            $table->date('reminder_date')->nullable();
            $table->string('reminder_note', 255)->nullable();
            $table->dateTime('reminder_sent_at')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['reminder_date', 'reminder_sent_at']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE evaluations
                ADD CONSTRAINT chk_evaluations_status
                CHECK (status IN ('draft','submitted_to_section_head','reviewed_by_section_head',
                                   'submitted_to_manager','approved','rejected'))
            ");
            DB::statement("
                ALTER TABLE evaluations
                ADD CONSTRAINT chk_evaluations_current_stage
                CHECK (current_stage IN ('leader','section_head','manager','done'))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};