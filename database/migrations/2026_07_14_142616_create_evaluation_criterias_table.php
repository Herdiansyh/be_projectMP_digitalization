<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')
                ->references('id')
                ->on('evaluation_criteria_groups')
                ->onDelete('no action')
                ->onUpdate('no action');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->foreign('subgroup_id')
                ->references('id')
                ->on('evaluation_criteria_subgroups')
                ->onDelete('set null')
                ->onUpdate('no action');
            $table->string('name', 150)->nullable(); // nullable: label kadang cukup diwakili subgroup
            $table->decimal('weight', 5, 2);
            $table->string('scale_type', 20); // 'custom_text' | 'standard'
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE evaluation_criteria
                ADD CONSTRAINT chk_evaluation_criteria_scale_type
                CHECK (scale_type IN ('custom_text','standard'))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria');
    }
};