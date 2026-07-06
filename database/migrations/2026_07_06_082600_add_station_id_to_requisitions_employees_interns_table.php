<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->after('assigned_station')
                ->constrained('stations')->nullOnDelete();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->after('station')
                ->constrained('stations')->nullOnDelete();
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->after('station')
                ->constrained('stations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });
    }
};