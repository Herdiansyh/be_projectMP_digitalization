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
            $table->unsignedBigInteger('approver_manager_id')->nullable();
            $table->unsignedBigInteger('approver_division_id')->nullable();
            $table->unsignedBigInteger('approver_director_id')->nullable();

            $table->foreign('approver_manager_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('approver_division_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('approver_director_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approver_manager_id']);
            $table->dropForeign(['approver_division_id']);
            $table->dropForeign(['approver_director_id']);

            $table->dropColumn([
                'approver_manager_id',
                'approver_division_id',
                'approver_director_id',
            ]);
        });
    }
};