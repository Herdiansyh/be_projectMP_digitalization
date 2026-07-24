<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar permission key, dikelompokkan per modul.
     * key => [group, label]
     *
     * PRINSIP LABEL: label harus sama persis dengan nama menu yang muncul
     * di Sidebar.tsx, BUKAN nama fungsi/aksi teknis. Tujuannya supaya admin
     * yang mengatur centang di halaman Permission Matrix langsung paham
     * "kalau dicentang, role ini bisa buka menu apa" tanpa perlu tahu detail
     * mekanisme di baliknya (siapa approver, dst).
     *
     * Dua permission di bawah ini BUKAN menu (tidak ada di sidebar), tapi
     * tombol aksi di dalam sebuah halaman — sengaja dipertahankan terpisah:
     *   - fptk.create      → tombol "+ Buat FPTK" di menu On Progress FPTK
     *   - fptk.process_hrd → tombol Process HRD/Assign Manpower/Assign
     *                        Area-Line di halaman detail FPTK (khusus HR Admin)
     *
     * CATATAN: key untuk Data Master (users.*, masterdata.manage,
     * competency.manage_matrix, evaluations.manage_form,
     * permissions.manage) dan Manpower (manpower.employees,
     * manpower.interns) TIDAK ada di sini — akses ke halaman-halaman itu
     * diatur langsung lewat flag per-user (is_admin / can_view_manpower)
     * di User Management, bukan lewat permission matrix.
     */
    public const LIST = [
        // ── FPTK ── (label = nama menu persis di Sidebar.tsx)
        'fptk.view_list'     => ['FPTK', 'On Progress FPTK'],
        'fptk.view_approved' => ['FPTK', 'Approved FPTK'],
        'fptk.view_rejected' => ['FPTK', 'Rejected FPTK'],
        'fptk.approve'       => ['FPTK', 'Pending FPTK'],
        'fptk.view_history'  => ['FPTK', 'FPTK History'],

        // ── FPTK (bukan menu, tombol aksi) ──
        'fptk.create'      => ['FPTK', 'Buat FPTK Baru (tombol)'],
        'fptk.process_hrd'      => ['FPTK', 'Proses HRD & Assign Manpower (tombol, khusus HR Admin)'],
        'fptk.assign_area_line' => ['FPTK', 'Assign Area/Line)'],

        // ── Competency ── (label = nama menu persis di Sidebar.tsx)
        'competency.assess'    => ['Competency', 'Competency Assessment'],
        'competency.qa_review' => ['Competency', 'QA Review'],

        // ── Evaluation ── (label = nama menu persis di Sidebar.tsx)
        'evaluations.view'         => ['Evaluation', 'Evaluations'],
        'evaluations.hr_decisions' => ['Evaluation', 'HR Decisions'],
        'competency.monitor' => ['Competency', 'Assessment Monitoring'],
    
        ];

    public function run(): void
    {
        foreach (self::LIST as $key => [$group, $label]) {
            Permission::updateOrCreate(
                ['key' => $key],
                ['group' => $group, 'label' => $label]
            );
        }

        $this->command->info('PermissionSeeder completed: ' . count(self::LIST) . ' permissions.');
    }
}