<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            'HR',
            'Finance, Accounting',
            'Manufacturing',
            'Product Engineering',
            'HR, GA',
            'Process Engineering',
            'Quality Assurance, Quality Control, AME',
            'Maintenance, IT',
            'Finance',
            'MP&L',
            'GA',
            'Purchasing, MP&L, SPE',
            'PPIC',
            'AME',
            'Maintenance',
            'Quality Control',
            'Accounting',
            'IT',
            'SPE',
            'Purchasing',
            'Quality Assurance',
        ];

        foreach ($sections as $section) {
            Section::create([
                'name' => $section,
            ]);
        }
    }
}