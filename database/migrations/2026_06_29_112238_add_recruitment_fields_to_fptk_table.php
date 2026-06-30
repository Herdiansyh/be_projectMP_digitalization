<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) { // ← sesuaikan nama tabel
            $table->foreignId('replacement_employee_id')
                  ->nullable()
                  ->after('employee_out')
                  ->constrained('employees')
                  ->nullOnDelete();
            $table->boolean('apprenticeship_period')
                  ->default(false)
                  ->after('replacement_employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropForeign(['replacement_employee_id']);
            $table->dropColumn(['replacement_employee_id', 'apprenticeship_period']);
        });
    }
};