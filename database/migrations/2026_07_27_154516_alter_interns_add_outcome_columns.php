<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->string('outcome_status', 20)->default('active')->after('group');
            $table->foreignId('converted_employee_id')
                ->nullable()
                ->after('outcome_status')
                ->constrained('employees')
                ->nullOnDelete();
            $table->dateTime('outcome_at')->nullable()->after('converted_employee_id');
            $table->string('outcome_note', 255)->nullable()->after('outcome_at');
        });

        DB::statement("
            ALTER TABLE interns
            ADD CONSTRAINT chk_interns_outcome_status
            CHECK (outcome_status IN ('active','converted','ended'))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE interns DROP CONSTRAINT chk_interns_outcome_status");

        Schema::table('interns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_employee_id');
            $table->dropColumn(['outcome_status', 'outcome_at', 'outcome_note']);
        });
    }
};