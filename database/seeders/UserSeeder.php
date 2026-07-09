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
        $hradmin        = RoleLevel::where('name', 'HR Admin')->first();
        $leader          = RoleLevel::where('name', 'Leader')->first();
        if (!$admin || !$director || !$divisionHead || !$Manager|| !$sectionHead || !$staff || !$hradmin || !$leader) {
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

            // ── Director ──
            [
                'npk'           => '04',
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
            //leader user
            [
                'npk'           => '07',
                'username'      => 'leader1',
                'name'          => 'Agus Santoso',
                'email'         => 'leader1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $leader->id,
            ],

            // ── Division Head ──
            [
                'npk'           => '03',
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
                'npk'           => '02',
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
                'npk'           => '06',
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

            // ── hradmin ──
            [
                'npk'           => '05',
                'username'      => 'hradmin1',
                'name'          => 'Eko Purnomo',
                'email'         => 'hradmin1@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $hradmin->id,
            ],
            [
                'npk'           => 'EMP0017',
                'username'      => 'hradmin2',
                'name'          => 'Fajar Hidayat',
                'email'         => 'hradmin2@gmail.com',
                'password'      => Hash::make('123123123'),
                'role_level_id' => $hradmin->id,
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
                    'is_admin'      => $userData['role_level_id'] === $admin->id,

                ])
            );
        }

        $this->command->info('UserSeeder completed: ' . count($users) . ' users seeded.');
    }
}