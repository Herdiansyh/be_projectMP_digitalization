<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_assessments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('intern_id')
                ->nullable()
                ->constrained('interns')
                ->cascadeOnDelete();

            $table->foreignId('matrix_id')
                ->constrained('competency_matrices')
                ->cascadeOnDelete();

            // Leader yang melakukan assessment
            $table->foreignId('assessed_by')
                ->constrained('users')
                ->noActionOnDelete();

            // Status Assessment
            $table->enum('status', ['pending_qa', 'approved'])
                ->default('pending_qa');

            // qa Reviewer
            $table->foreignId('qa_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamp('qa_at')->nullable();

            $table->string('period_label');
            $table->timestamp('assessed_at');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('employee_id');
            $table->index('intern_id');
            $table->index('matrix_id');
            $table->index('assessed_by');
            $table->index('qa_by');
            $table->index('status');
            $table->index('period_label');
        });

        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_id')
                ->constrained('employee_assessments')
                ->cascadeOnDelete();

            $table->foreignId('checkpoint_id')
                ->constrained('competency_checkpoints')
                ->noActionOnDelete();

            $table->enum('source', ['leader', 'qa'])
                ->default('leader');

            $table->unsignedTinyInteger('point');

            $table->index('assessment_id');
            $table->index('checkpoint_id');

            $table->unique([
                'assessment_id',
                'checkpoint_id',
                'source'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('employee_assessments');
    }
};