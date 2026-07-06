<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->dropColumn(['area', 'line', 'station']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['area', 'line', 'station']);
        });

        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropColumn(['assigned_area', 'assigned_line']);
        });
    }

    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->string('area')->nullable();
            $table->string('line')->nullable();
            $table->string('station')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('area')->nullable();
            $table->string('line')->nullable();
            $table->string('station')->nullable();
        });

        Schema::table('requisitions', function (Blueprint $table) {
            $table->string('assigned_area')->nullable();
            $table->string('assigned_line')->nullable();
        });
    }
};