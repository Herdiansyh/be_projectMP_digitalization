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
            'HR & GA',
            'Finance Accounting',
            'Manufacturing',
            'Product Engineering',
            'Process Engineering',
            'Quality',
            'Maintenance & IT',
            'Purchasing & MP&L',
            'PPIC',
        ];

        foreach ($departments as $department) {
            Department::create([
                'name' => $department,
            ]);
        }
    }
}