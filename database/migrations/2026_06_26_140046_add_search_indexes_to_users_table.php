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
        $table->index('name');
        $table->index('email');
        $table->index('npk');
        $table->index('username');
        $table->index('department_id');
        $table->index('section_id');
        $table->index('role_level_id');
        $table->index('is_admin');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropIndex(['name']);
        $table->dropIndex(['email']);
        $table->dropIndex(['npk']);
        $table->dropIndex(['username']);
        $table->dropIndex(['department_id']);
        $table->dropIndex(['section_id']);
        $table->dropIndex(['role_level_id']);
        $table->dropIndex(['is_admin']);
    });
}
};
