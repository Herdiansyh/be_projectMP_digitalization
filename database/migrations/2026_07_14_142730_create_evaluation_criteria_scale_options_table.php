<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria_scale_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')
                ->constrained('evaluation_criteria')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1-5
            $table->text('description');
            $table->timestamps();

            // 1 criteria hanya boleh punya 1 baris per score
            $table->unique(['criteria_id', 'score']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE evaluation_criteria_scale_options
                ADD CONSTRAINT chk_evaluation_scale_options_score
                CHECK (score BETWEEN 1 AND 5)
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria_scale_options');
    }
};