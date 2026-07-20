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
    Schema::table('employees', function (Blueprint $table) {
        $table->boolean('is_active')->default(true)->after('employment_type');
        $table->timestamp('deactivated_at')->nullable();
        $table->string('deactivated_reason')->nullable();
    });
}

public function down(): void
{
    Schema::table('employees', function (Blueprint $table) {
        $table->dropColumn(['is_active', 'deactivated_at', 'deactivated_reason']);
    });
}
};
