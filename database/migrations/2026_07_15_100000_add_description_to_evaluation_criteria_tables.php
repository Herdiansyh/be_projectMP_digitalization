<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_criteria_groups', function (Blueprint $table) {
            $table->text('description')->nullable()->after('code');
        });

        Schema::table('evaluation_criteria_subgroups', function (Blueprint $table) {
            $table->text('description')->nullable()->after('roman_code');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_criteria_groups', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('evaluation_criteria_subgroups', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
