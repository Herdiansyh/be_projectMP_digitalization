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
            ['name' => 'Operator', 'is_system' => false],
        ];

        foreach ($roleLevels as $roleLevel) {
            RoleLevel::create($roleLevel);
        }
    }
}
