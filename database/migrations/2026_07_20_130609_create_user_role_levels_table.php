<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_role_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_level_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_level_id']);
        });

        // Migrasi data existing: role_level_id yang sudah di-assign ke user
        // dipindahkan jadi baris pertama di pivot, supaya user yang sudah
        // punya role sekarang tidak kehilangan aksesnya.
        $now = now();
        $rows = DB::table('users')
            ->whereNotNull('role_level_id')
            ->get(['id', 'role_level_id']);

        foreach ($rows as $row) {
            DB::table('user_role_levels')->insert([
                'user_id'       => $row->id,
                'role_level_id' => $row->role_level_id,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_levels');
    }
};