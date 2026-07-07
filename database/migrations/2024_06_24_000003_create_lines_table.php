<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            // Nama line boleh sama di area berbeda, tapi harus unik dalam satu area
            $table->unique(['area_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lines');
    }
};