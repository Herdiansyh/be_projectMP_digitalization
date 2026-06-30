<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('npk')->unique();
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('role_level_id')->nullable()->constrained('role_levels')->nullOnDelete();
            $table->string('area')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('station')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'apprentice']);
            $table->enum('status', ['active', 'nonactive', 'resigned'])->default('active');
            $table->date('start_contract');
            $table->date('end_contract')->nullable();
            $table->timestamps();

            // Index untuk kolom yang sering difilter/query
            $table->index('department_id');
            $table->index('employment_type');
            $table->index('status');
            $table->index('end_contract');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};