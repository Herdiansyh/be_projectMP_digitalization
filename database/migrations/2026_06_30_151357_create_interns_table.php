<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interns', function (Blueprint $table) {
            $table->id();
            $table->string('npk')->unique(); // unique constraint otomatis sudah jadi index
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('role_level_id')->nullable()->constrained('role_levels')->nullOnDelete();
            $table->string('area')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('station')->nullable();
            $table->date('start_contract');
            $table->date('end_contract')->nullable();
            $table->timestamps();

            // Index untuk kolom yang dipakai filter di index() — cukup ditambahkan
            // di sini, tidak mengubah cara controller/query bekerja sama sekali.
            $table->index('department_id');
            $table->index('section_id');
            $table->index('end_contract');
            $table->index('name'); // mempercepat ORDER BY name (dipakai di setiap request index)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interns');
    }
};