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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->string('no_req')->primary();
            $table->string('requester_name');
            $table->dateTime('request_date');
            // $table->string('group')->nullable();
            $table->string('department')->nullable();
            $table->string('section')->nullable();
            // $table->string('type')->nullable();
            $table->string('position')->nullable();
            $table->string('status')->nullable();
            $table->string('duration')->nullable();
            $table->string('level')->nullable();
            $table->string('cost_employee')->nullable();
            $table->string('fulfilment_time')->nullable();
            $table->string('education')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('min_experience')->nullable();
            $table->text('technical_skill')->nullable();
            $table->text('soft_skill')->nullable();
            // $table->text('description')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('objective')->nullable();
            $table->text('reason')->nullable();
            $table->string('employee_out')->nullable();
            $table->text('manpower_plan')->nullable();
            $table->text('unplanned_reason')->nullable();
            $table->string('approval_status')->default('Menunggu Approval Manager');
            $table->string('manager')->nullable();
            $table->string('division')->nullable();
            $table->string('director')->nullable();
            $table->string('supervisor')->nullable();
            $table->integer('hrd_approved')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->timestamp('division_approved_at')->nullable();
            $table->timestamp('director_approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('requester_name');
            $table->index('approval_status');
            $table->index('request_date');
            $table->index('manager');
            $table->index('division');
            $table->index('director');
            $table->index('supervisor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
