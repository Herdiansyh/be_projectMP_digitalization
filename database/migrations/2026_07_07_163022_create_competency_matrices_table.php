<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {  Schema::create('competency_matrices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $table->string('name'); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('station_id');
            $table->index('is_active');
        });

        Schema::create('competency_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matrix_id')->constrained('competency_matrices')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('matrix_id');
        });

        // ── Checkpoint/kriteria penilaian dalam satu kategori ──
        Schema::create('competency_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('competency_categories')->cascadeOnDelete();
            $table->text('description');
            $table->unsignedTinyInteger('weight')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_checkpoints');
        Schema::dropIfExists('competency_categories');
        Schema::dropIfExists('competency_matrices');
    }
};