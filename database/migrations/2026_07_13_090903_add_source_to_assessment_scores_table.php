<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->dropUnique(['assessment_id', 'checkpoint_id']);

            $table->enum('source', ['leader', 'qc'])
                  ->default('leader')
                  ->after('checkpoint_id');

            $table->unique(['assessment_id', 'checkpoint_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->dropUnique(['assessment_id', 'checkpoint_id', 'source']);
            $table->dropColumn('source');
            $table->unique(['assessment_id', 'checkpoint_id']);
        });
    }
};