<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
{
    $superAdminRole = Role::where('name', 'Super Admin')->first();
    $hrAdminRole = Role::where('name', 'HR Admin')->first();

    if (!$superAdminRole || !$hrAdminRole) {
        $this->command->error('Required roles not found. Please run RoleSeeder first.');
        return;
    }

    User::firstOrCreate(
        ['email' => 'admin@gmail.com'],
        [
            'role_id' => $superAdminRole->id,
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123123123'),
            'is_active' => true,
        ]
    );

    User::firstOrCreate(
        ['email' => 'hradmin@gmail.com'],
        [
            'role_id' => $hrAdminRole->id,
            'name' => 'HR Admin',
            'email' => 'hradmin@gmail.com',
            'password' => Hash::make('123123123'),
            'is_active' => true,
        ]
    );
}
}
