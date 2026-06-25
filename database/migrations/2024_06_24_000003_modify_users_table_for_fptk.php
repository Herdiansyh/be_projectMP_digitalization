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
        Schema::table('users', function (Blueprint $table) {
            // Remove role_id foreign key if exists
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }   
            
            $table->string('npk')->unique()->after('id');
            $table->foreignId('department_id')->nullable()->after('email')->constrained('departments')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->after('department_id')->constrained('sections')->onDelete('set null');
            $table->foreignId('role_level_id')->nullable()->after('section_id')->constrained('role_levels')->onDelete('set null');
            $table->string('username')->unique()->after('role_level_id');
            $table->string('photo')->nullable()->after('username');
            $table->foreignId('director_id')->nullable()->after('photo')->constrained('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['role_level_id']);
            $table->dropForeign(['director_id']);
            $table->dropColumn(['npk', 'department_id', 'section_id', 'role_level_id', 'username', 'photo', 'director_id']);
        });
    }
};
