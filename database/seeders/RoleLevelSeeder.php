<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleLevel;

class RoleLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    $roleLevels = [
        ['name' => 'Admin', 'is_system' => true],
        ['name' => 'Director', 'is_system' => false],
        ['name' => 'Division Head', 'is_system' => false],
        ['name' => 'Manager', 'is_system' => false],
        ['name' => 'Section Head', 'is_system' => false],
        ['name' => 'Staff', 'is_system' => false],
        ['name' => 'HR Admin', 'is_system' => false],
    ];

    RoleLevel::upsert(
        $roleLevels, 
        ['name'],          // Kolom unik yang menjadi acuan (unique index)
        ['is_system']      // Kolom yang ingin di-update jika datanya duplikat
    );
}
}
