<?php

namespace Database\Seeders;

use App\Models\RoleLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil role level admin
        $admin = RoleLevel::where('name', 'Admin')->first();

        if (!$admin) {
            $this->command->error('RoleLevel not found. Run RoleLevelSeeder first.');
            return;
        }

        $users = [
            // ── Admin ──
            [
                'npk'           => '01',
                'username'      => 'admin',
                'name'          => 'Administrator',
                'email'         => 'admin@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $admin->id,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'department_id' => null,
                    'section_id'    => null,
                    'photo'         => null,
                    'is_admin'      => $userData['role_level_id'] === $admin->id,
                ])
            );
            $user->roleLevels()->sync([$userData['role_level_id']]);
        }

        $this->command->info('UserSeeder completed: ' . count($users) . ' users seeded.');
    }
}