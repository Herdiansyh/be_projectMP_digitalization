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
        // Ambil semua role level
        $admin           = RoleLevel::where('name', 'Admin')->first();
        $director        = RoleLevel::where('name', 'Director')->first();
        $divisionHead    = RoleLevel::where('name', 'Division Head')->first();
        $Manager         = RoleLevel::where('name', 'Manager')->first();
        $sectionHead     = RoleLevel::where('name', 'Section Head')->first();
        $staff           = RoleLevel::where('name', 'Staff')->first();
        $operator        = RoleLevel::where('name', 'Operator')->first();

        if (!$admin || !$director || !$divisionHead || !$Manager|| !$sectionHead || !$staff || !$operator) {
            $this->command->error('RoleLevel not found. Run RoleLevelSeeder first.');
            return;
        }

        $users = [
            // ── Admin ──
            [
                'npk'           => 'EMP0001',
                'username'      => 'admin',
                'name'          => 'Administrator',
                'email'         => 'admin@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $admin->id,
            ],

            // ── Director ──
            [
                'npk'           => 'EMP0002',
                'username'      => 'director1',
                'name'          => 'Budi Santoso',
                'email'         => 'director1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $director->id,
            ],
            [
                'npk'           => 'EMP0003',
                'username'      => 'director2',
                'name'          => 'Siti Rahayu',
                'email'         => 'director2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $director->id,
            ],

            // ── Division Head ──
            [
                'npk'           => 'EMP0004',
                'username'      => 'divhead1',
                'name'          => 'Ahmad Fauzi',
                'email'         => 'divhead1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $divisionHead->id,
            ],
            [
                'npk'           => 'EMP0005',
                'username'      => 'divhead2',
                'name'          => 'Dewi Kusuma',
                'email'         => 'divhead2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $divisionHead->id,
            ],
            [
                'npk'           => 'EMP0006',
                'username'      => 'divhead3',
                'name'          => 'Rizky Pratama',
                'email'         => 'divhead3@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $divisionHead->id,
            ],

            // ── Departement Head (Manager) ──
            [
                'npk'           => 'EMP0007',
                'username'      => 'manager1',
                'name'          => 'Hendra Wijaya',
                'email'         => 'manager1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $Manager     ->id,
            ],
            [
                'npk'           => 'EMP0008',
                'username'      => 'manager2',
                'name'          => 'Rina Marlina',
                'email'         => 'manager2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $Manager     ->id,
            ],
            [
                'npk'           => 'EMP0009',
                'username'      => 'manager3',
                'name'          => 'Teguh Setiawan',
                'email'         => 'manager3@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $Manager     ->id,
            ],
            [
                'npk'           => 'EMP0010',
                'username'      => 'manager4',
                'name'          => 'Yuni Astuti',
                'email'         => 'manager4@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $Manager     ->id,
            ],

            // ── Section Head ──
            [
                'npk'           => 'EMP0011',
                'username'      => 'sectionhead1',
                'name'          => 'Doni Prasetyo',
                'email'         => 'sectionhead1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $sectionHead->id,
            ],
            [
                'npk'           => 'EMP0012',
                'username'      => 'sectionhead2',
                'name'          => 'Fitri Handayani',
                'email'         => 'sectionhead2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $sectionHead->id,
            ],

            // ── Staff ──
            [
                'npk'           => 'EMP0013',
                'username'      => 'staff1',
                'name'          => 'Andi Kurniawan',
                'email'         => 'staff1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $staff->id,
            ],
            [
                'npk'           => 'EMP0014',
                'username'      => 'staff2',
                'name'          => 'Bayu Nugroho',
                'email'         => 'staff2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $staff->id,
            ],
            [
                'npk'           => 'EMP0015',
                'username'      => 'staff3',
                'name'          => 'Citra Lestari',
                'email'         => 'staff3@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $staff->id,
            ],

            // ── Operator ──
            [
                'npk'           => 'EMP0016',
                'username'      => 'operator1',
                'name'          => 'Eko Purnomo',
                'email'         => 'operator1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $operator->id,
            ],
            [
                'npk'           => 'EMP0017',
                'username'      => 'operator2',
                'name'          => 'Fajar Hidayat',
                'email'         => 'operator2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $operator->id,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'department_id' => null,
                    'section_id'    => null,
                    'photo'         => null,
                    'director_id'   => null,
                ])
            );
        }

        $this->command->info('UserSeeder completed: ' . count($users) . ' users seeded.');
    }
}