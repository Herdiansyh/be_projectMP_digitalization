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
    Schema::create('evaluation_contract_extensions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
        $table->date('previous_end_contract')->nullable();
        $table->date('new_end_contract');
        $table->string('pkwt_number')->nullable();
        $table->unsignedInteger('extend_months')->nullable();
        $table->text('notes')->nullable();
        $table->foreignId('extended_by')->constrained('users');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('evaluation_contract_extensions');
}
};
