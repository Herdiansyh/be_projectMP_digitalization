<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('npk')->unique();
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();

            // Menggantikan role_level_id (FK ke role_levels, sudah dihapus)
            $table->string('role_level')->nullable();

            $table->string('jabatan')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'apprentice']);

            $table->date('start_contract');
            $table->date('end_contract')->nullable();

            // Menggantikan kolom string area/line/station (sudah dihapus)
            $table->foreignId('area_id')->nullable();
            $table->foreignId('line_id')->nullable();
            $table->foreignId('station_id')->nullable();

            // Menghubungkan employee ke FPTK asalnya — satu no_req bisa
            // menghasilkan banyak employee (lihat Requisition::employees()).
            $table->string('no_req')->nullable();

            $table->timestamps();

            // ── Indexes ──
            $table->index('department_id');
            $table->index('employment_type');
            $table->index('end_contract');

            // ── Foreign keys ──
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('no action');
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('no action');
            $table->foreign('station_id')->references('id')->on('stations')->nullOnDelete();
            $table->foreign('no_req')->references('no_req')->on('requisitions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};