<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria_subgroups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('evaluation_criteria_groups')
                ->cascadeOnDelete();
            $table->string('name', 150);        // "Kedisiplinan", "Hubungan Manusia", dst
            $table->string('roman_code', 10)->nullable(); // "I", "II", "III", dst
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria_subgroups');
    }
};