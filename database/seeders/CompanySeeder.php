<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'PT. AVI'],
            ['name' => 'PT. AVI 2'],
            ['name' => 'Kopkar'],
            ['name' => 'Yayasan'],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
