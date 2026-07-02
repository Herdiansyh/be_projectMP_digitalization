<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['role_level_id']);

            // Baru hapus kolom
            $table->dropColumn('role_level_id');

            // Tambahkan kolom baru
            $table->string('role_level')->nullable()->after('section_id');
        });

        Schema::table('interns', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['role_level_id']);

            // Baru hapus kolom
            $table->dropColumn('role_level_id');

            // Tambahkan kolom baru
            $table->string('role_level')->nullable()->after('section_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('role_level');

            $table->foreignId('role_level_id')
                ->nullable()
                ->after('section_id')
                ->constrained('role_levels')
                ->nullOnDelete();
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->dropColumn('role_level');

            $table->foreignId('role_level_id')
                ->nullable()
                ->after('section_id')
                ->constrained('role_levels')
                ->nullOnDelete();
        });
    }
};