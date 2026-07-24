<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RoleLevel;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Mapping role name -> permission keys.
     * Disusun berdasarkan logic hardcode yang sudah ada di Sidebar.tsx & ProtectedRoute.tsx,
     * supaya perilaku aplikasi TIDAK berubah begitu sistem permission diaktifkan.
     * Admin tidak perlu didaftarkan di sini — is_admin selalu bypass semua permission.
     */
    public const MAP = [
        'Staff' => [
            'fptk.create', 'fptk.view_list', 'fptk.view_approved', 'fptk.view_rejected',
        ],
        'Operator' => [
            'fptk.create', 'fptk.view_list', 'fptk.view_approved', 'fptk.view_rejected',
        ],
        'Section Head' => [
            'fptk.create', 'fptk.view_list', 'fptk.view_approved', 'fptk.view_rejected',
            'evaluations.view',
        ],
        'Manager' => [
            'fptk.approve', 'fptk.view_approved', 'fptk.view_history',
            'evaluations.view',
        ],
        'Division Head' => [
            'fptk.approve', 'fptk.view_approved', 'fptk.view_history',
        ],
        'Director' => [
            'fptk.approve', 'fptk.view_approved', 'fptk.view_history',
        ],
        'HR Admin' => [
            'fptk.view_approved', 'evaluations.hr_decisions',
        ],
        'Leader' => [
            'competency.assess',
        ],
        'Quality Assurance' => [
            'competency.qa_review',
        ],
    ];

    public function run(): void
    {
        foreach (self::MAP as $roleName => $permissionKeys) {
            $role = RoleLevel::where('name', $roleName)->first();

            if (!$role) {
                $this->command->warn("RoleLevel '{$roleName}' not found, skipped.");
                continue;
            }

            $permissionIds = Permission::whereIn('key', $permissionKeys)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('RolePermissionSeeder completed.');
    }
}