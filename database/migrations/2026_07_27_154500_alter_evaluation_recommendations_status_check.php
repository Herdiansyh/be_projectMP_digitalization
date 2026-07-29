<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE evaluation_recommendations DROP CONSTRAINT chk_evaluation_recommendations_employee_status");
        DB::statement("
            ALTER TABLE evaluation_recommendations
            ADD CONSTRAINT chk_evaluation_recommendations_employee_status
            CHECK (employee_status IS NULL OR employee_status IN (
                'permanen','kontrak_berakhir','perpanjang_kontrak',
                'promoted','not_extended'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE evaluation_recommendations DROP CONSTRAINT chk_evaluation_recommendations_employee_status");
        DB::statement("
            ALTER TABLE evaluation_recommendations
            ADD CONSTRAINT chk_evaluation_recommendations_employee_status
            CHECK (employee_status IS NULL OR employee_status IN ('permanen','kontrak_berakhir','perpanjang_kontrak'))
        ");
    }
};