<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. "fptk.create"
            $table->string('group');          // e.g. "FPTK" — untuk kelompokkan di UI matrix
            $table->string('label');          // e.g. "Create FPTK Request"
            $table->timestamps();
        });

        Schema::create('role_level_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_level_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_level_permissions');
        Schema::dropIfExists('permissions');
    }
};