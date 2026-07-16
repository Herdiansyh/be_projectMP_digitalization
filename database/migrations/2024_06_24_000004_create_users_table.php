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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('npk')->unique();
            $table->string('name');
            $table->string('email')->unique();

            // Foreign Keys
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('role_level_id')->nullable();
            $table->unsignedBigInteger('director_id')->nullable();

            $table->string('username')->unique();
            $table->string('photo')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->boolean('is_admin')->default(false);
            $table->boolean('can_view_manpower')->default(false);
            $table->timestamp('last_login_at')->nullable();

            // Approval Chain
            $table->unsignedBigInteger('approver_manager_id')->nullable();
            $table->unsignedBigInteger('approver_section_head_id')->nullable();
            $table->unsignedBigInteger('approver_division_id')->nullable();
            $table->unsignedBigInteger('approver_director_id')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('name');
            $table->index('email');
            $table->index('npk');
            $table->index('username');

            $table->index('department_id');
            $table->index('section_id');
            $table->index('area_id');
            $table->index('role_level_id');
            $table->index('director_id');

            $table->index('approver_manager_id');
            $table->index('approver_section_head_id');
            $table->index('approver_division_id');
            $table->index('approver_director_id');

            $table->index('is_admin');

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('set null');

            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('set null');

            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('set null');

            $table->foreign('role_level_id')
                ->references('id')
                ->on('role_levels')
                ->onDelete('set null');

            // Self Reference
            $table->foreign('director_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('approver_manager_id')
                ->references('id')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('approver_section_head_id')
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

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};