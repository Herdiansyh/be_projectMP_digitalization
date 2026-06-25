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
            'Finance Accounting',
            'HR & Legal',
            'GA & EHS',
            'Maintenance & IT',
            'Manufacturing',
            'Marketing',
            'OPEX & PDCA',
            'Process Engineering',
            'Product Engineering',
            'QA',
            'QC',
            'MP&L',
            'Purchasing',
            'PPIC',
        ];

        foreach ($sections as $section) {
            Section::create([
                'name' => $section,
            ]);
        }
    }
}
