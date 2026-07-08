<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competency_checkpoints', function (Blueprint $table) {
            $table->integer('sequence')->nullable()->after('description');
            $table->string('main_process')->nullable()->after('sequence');
        });
    }

    public function down(): void
    {
        Schema::table('competency_checkpoints', function (Blueprint $table) {
            $table->dropColumn(['sequence', 'main_process']);
        });
    }
};