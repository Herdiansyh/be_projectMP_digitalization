<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_assessments', function (Blueprint $table) {
            $table->enum('status', ['pending_qc', 'approved'])
                  ->default('pending_qc')
                  ->after('notes');

            $table->foreignId('qc_by')->nullable()
                  ->constrained('users')->noActionOnDelete()
                  ->after('status');

            $table->timestamp('qc_at')->nullable()->after('qc_by');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('employee_assessments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('qc_by');
            $table->dropColumn(['status', 'qc_at']);
        });
    }
};