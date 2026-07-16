<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('line_id')
                ->nullable()
                ->constrained('lines')
                ->noActionOnDelete();

            $table->string('name');

            $table->timestamps();

            $table->unique(['line_id', 'name']);
            $table->index('line_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};