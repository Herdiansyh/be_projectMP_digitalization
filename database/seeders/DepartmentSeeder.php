<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Finance',
            'HRGA',
            'Maintenance & IT',
            'Manufacturing',
            'Project Management',
            'OPEX & PDCA',
            'Process Engineering',
            'Product Engineering',
            'PURCHASING, PPIC, MP&L',
            'Quality',
        ];

        foreach ($departments as $department) {
            Department::create([
                'name' => $department,
            ]);
        }
    }
}
