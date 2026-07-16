<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_id')
                ->constrained('evaluations')
                ->cascadeOnDelete();

            $table->foreignId('criteria_id')
                ->constrained('evaluation_criteria')
                ->cascadeOnDelete();

            // Nilai yang diberikan (1-5)
            $table->unsignedTinyInteger('score')->nullable();

            // Hasil score × weight
            $table->decimal('score_x_weight', 6, 2)->nullable();

            // Diisi oleh siapa
            $table->string('filled_by_role', 20)->nullable(); // leader | section_head | manager

            $table->foreignId('filled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Satu evaluator hanya boleh mengisi satu kali untuk setiap kriteria
            $table->unique(
                ['evaluation_id', 'criteria_id', 'filled_by_role'],
                'eval_score_unique_per_role'
            );
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE evaluation_scores
                ADD CONSTRAINT chk_evaluation_scores_score
                CHECK (score IS NULL OR score BETWEEN 1 AND 5)
            ");

            DB::statement("
                ALTER TABLE evaluation_scores
                ADD CONSTRAINT chk_evaluation_scores_filled_by_role
                CHECK (
                    filled_by_role IS NULL
                    OR filled_by_role IN ('leader','section_head','manager')
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
    }
};