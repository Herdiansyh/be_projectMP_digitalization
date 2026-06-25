<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    

        // FPTK related seeders
        $this->call([
            DepartmentSeeder::class,
            SectionSeeder::class,
            RoleLevelSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
            EmployeeStatusSeeder::class,
        ]);
    }
}
