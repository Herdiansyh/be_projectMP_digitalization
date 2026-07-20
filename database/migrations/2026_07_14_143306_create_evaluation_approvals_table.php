<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                ->constrained('evaluations')
                ->cascadeOnDelete();

            $table->string('role', 30);   // leader | section_head | manager | hr_admin
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 40); // submit | approve | reject | revise | forward_to_hr_admin | extend_contract | close_contract_deactivate | close_contract_delete
            $table->text('notes')->nullable();
            $table->dateTime('acted_at')->useCurrent();

            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE evaluation_approvals
                ADD CONSTRAINT chk_evaluation_approvals_role
                CHECK (role IN ('leader','section_head','manager','hr_admin'))
            ");
            DB::statement("
                ALTER TABLE evaluation_approvals
                ADD CONSTRAINT chk_evaluation_approvals_action
                CHECK (action IN ('submit','approve','reject','revise','forward_to_hr_admin','extend_contract','close_contract_deactivate','close_contract_delete'))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_approvals');
    }
};