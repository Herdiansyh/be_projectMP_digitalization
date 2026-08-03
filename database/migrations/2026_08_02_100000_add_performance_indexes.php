<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->index('replacement_employee_id');
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->index('outcome_status');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->index(['current_stage', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropIndex(['replacement_employee_id']);
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->dropIndex(['outcome_status']);
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex(['current_stage', 'status']);
        });
    }
};