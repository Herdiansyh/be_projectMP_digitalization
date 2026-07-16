<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);       // "Hasil Kerja", "Proses Kerja"
            $table->string('code', 10);        // "A", "B"
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria_groups');
    }
};