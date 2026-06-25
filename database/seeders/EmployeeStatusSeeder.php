<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeStatus;

class EmployeeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'PKWT', 'level_default' => 0],
            ['name' => 'PKWTT', 'level_default' => 0],
            ['name' => 'Kontrak', 'level_default' => 0],
            ['name' => 'Harian Lepas', 'level_default' => 0],
            ['name' => 'Tetap', 'level_default' => 0],
        ];

        foreach ($statuses as $status) {
            EmployeeStatus::create($status);
        }
    }
}
