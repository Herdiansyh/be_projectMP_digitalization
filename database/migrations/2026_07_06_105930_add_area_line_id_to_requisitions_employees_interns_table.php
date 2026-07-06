<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('assigned_area');
            $table->foreignId('line_id')->nullable()->after('assigned_line');

            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('no action');

            $table->foreign('line_id')
                ->references('id')
                ->on('lines')
                ->onDelete('no action');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('area');
            $table->foreignId('line_id')->nullable()->after('line');

            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('no action');

            $table->foreign('line_id')
                ->references('id')
                ->on('lines')
                ->onDelete('no action');
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('area');
            $table->foreignId('line_id')->nullable()->after('line');

            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('no action');

            $table->foreign('line_id')
                ->references('id')
                ->on('lines')
                ->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['line_id']);
            $table->dropColumn(['area_id', 'line_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['line_id']);
            $table->dropColumn(['area_id', 'line_id']);
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['line_id']);
            $table->dropColumn(['area_id', 'line_id']);
        });
    }
};