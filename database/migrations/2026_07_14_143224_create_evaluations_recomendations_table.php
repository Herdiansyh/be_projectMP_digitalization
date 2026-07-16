<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                ->unique()
                ->constrained('evaluations')
                ->cascadeOnDelete();

            $table->string('employee_status', 20)->nullable(); // permanen | kontrak_berakhir
            $table->boolean('extend_pkwt')->default(false);
            $table->string('pkwt_number', 10)->nullable();     // 1/2/3/4, aktif jika extend_pkwt true
            $table->integer('extend_months')->nullable();
            $table->text('notes')->nullable();                  // Saran / Hasil Konseling

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE evaluation_recommendations
                ADD CONSTRAINT chk_evaluation_recommendations_employee_status
                CHECK (employee_status IS NULL OR employee_status IN ('permanen','kontrak_berakhir'))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_recommendations');
    }
};