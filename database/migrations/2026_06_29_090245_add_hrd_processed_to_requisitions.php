<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            // Timestamp kapan HR memproses FPTK
            $table->timestamp('hrd_processed_at')->nullable()->after('director_approved_at');
            // Siapa HR yang memproses
            $table->string('hrd_processed_by')->nullable()->after('hrd_processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropColumn(['hrd_processed_at', 'hrd_processed_by']);
        });
    }
};