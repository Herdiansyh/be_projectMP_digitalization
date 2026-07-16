<?php

namespace Database\Seeders;

use App\Models\EvaluationCriteria;
use App\Models\EvaluationCriteriaGroup;
use App\Models\EvaluationCriteriaScaleOptions;
use App\Models\EvaluationCriteriaSubgroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EvaluationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $groupA = EvaluationCriteriaGroup::updateOrCreate(
            ['code' => 'A'],
            ['name' => 'Hasil Kerja', 'order' => 1]
        );

        $groupB = EvaluationCriteriaGroup::updateOrCreate(
            ['code' => 'B'],
            ['name' => 'Proses Kerja', 'order' => 2]
        );

        $this->createCriteria(
            $groupA,
            null,
            'Penguasaan Kerja',
            10,
            'custom_text',
            [
                1 => 'Belum mampu menjalankan tugas sesuai Standar Kerja',
                2 => 'Mampu menjalankan tugas sesuai Standard Kerja tetapi masih perlu pengawasan',
                3 => 'Mampu menjalankan tugas sesuai Standard Kerja tanpa pengawasan',
                4 => 'Mampu menjalankan tugas sesuai Standard Kerja tanpa pengawasan dan tahu hubungannya dengan proses yang terkait',
                5 => 'Mampu menjalankan tugas sesuai Standard Kerja tanpa pengawasan dan tahu hubungannya dengan proses yang terkait, dan bisa menjadi Trainer',
            ],
            1
        );

        $this->createCriteria(
            $groupA,
            null,
            'Kuantitas',
            10,
            'custom_text',
            [
                1 => 'Dibawah 85% dari target',
                2 => '85-90% dari target',
                3 => '91-95% dari target',
                4 => '96-99% dari target',
                5 => '100% dari target, ada usaha tambahan',
            ],
            2
        );

        $this->createCriteria(
            $groupA,
            null,
            'Kualitas',
            10,
            'custom_text',
            [
                1 => 'Dibawah 85% dari target',
                2 => '85-90% dari target',
                3 => '91-95% dari target',
                4 => '96-99% dari target',
                5 => '100% dari target',
            ],
            3
        );

        $subgroupI = EvaluationCriteriaSubgroup::updateOrCreate(
            ['group_id' => $groupB->id, 'roman_code' => 'I'],
            ['name' => 'Kedisiplinan', 'order' => 1]
        );

        $this->createCriteria(
            $groupB,
            $subgroupI,
            'Ketidakhadiran (1 Hari Penuh)',
            15,
            'custom_text',
            [
                1 => 'Melebihi 3 hari kerja atau Mangkir 1 Hari',
                2 => '3 Hari Kerja',
                3 => '2 Hari Kerja',
                4 => '1 Hari Kerja',
                5 => 'Selalu Hadir',
            ],
            1
        );

        $this->createCriteria(
            $groupB,
            $subgroupI,
            'Ijin Meninggalkan Pekerjaan',
            6,
            'custom_text',
            [
                1 => 'Melebihi 3 Kali',
                2 => '3 Kali',
                3 => '2 Kali',
                4 => '1 Kali',
                5 => 'Tidak Pernah',
            ],
            2
        );

        $this->createCriteria(
            $groupB,
            $subgroupI,
            'Terlambat',
            8,
            'custom_text',
            [
                1 => 'Melebihi 3 Kali',
                2 => '3 Kali',
                3 => '2 Kali',
                4 => '1 Kali',
                5 => 'Tidak Pernah',
            ],
            3
        );

        $this->createCriteria(
            $groupB,
            $subgroupI,
            'Pelanggaran Peraturan',
            10,
            'custom_text',
            [
                1 => 'Pernah mendapat Surat peringatan III / Skorsing',
                2 => 'Pernah mendapat Surat peringatan II',
                3 => 'Pernah mendapat Surat peringatan I',
                4 => 'Pernah mendapat teguran lisan atau mendapat Memo Teguran Internal',
                5 => 'Tidak pernah melanggar per-aturan perusahaan yang berlaku',
            ],
            4
        );

        $this->createCriteria(
            $groupB,
            $subgroupI,
            'Disiplin Terhadap Peraturan 5R',
            5,
            'custom_text',
            [
                1 => 'Total Poin Hasil Patroli 5R Sebanyak <= 5 Poin',
                2 => 'Total Poin Hasil Patroli 5R Sebanyak 6-7 Poin',
                3 => 'Total Poin Hasil Patroli 5R Sebanyak 8-9 Poin',
                4 => 'Total Poin Hasil Patroli 5R Sebanyak 10-11 Poin',
                5 => 'Total Poin Hasil Patroli 5R Sebanyak 12 Poin',
            ],
            5
        );

        $subgroupII = EvaluationCriteriaSubgroup::updateOrCreate(
            ['group_id' => $groupB->id, 'roman_code' => 'II'],
            ['name' => 'Hubungan Manusia', 'order' => 2]
        );

        $this->createCriteria(
            $groupB,
            $subgroupII,
            'Komunikasi',
            3,
            'custom_text',
            [
                1 => 'Belum dapat menyampaikan informasi sesuai aktual yang terjadi dan terkadang salah mengartikan informasi yang diterima. Belum terbuka terhadap saran dan kritik yang disampaikan orang lain',
                2 => 'Dapat menyampaikan informasi sesuai aktual yang terjadi namun terkadang salah mengartikan informasi yang diterima. Belum terbuka terhadap saran dan kritik yang disampaikan orang lain',
                3 => 'Dapat menyampaikan informasi sesuai aktual yang terjadi dan tidak salah mengartikan informasi yang diterima. Terbuka terhadap saran dan kritik yang disampaikan orang lain',
                4 => 'Dapat menyampaikan informasi sesuai aktual yang terjadi secara ringkas, jelas, & benar, serta tidak salah mengartikan informasi yang diterima. Terbuka terhadap saran dan kritik yang disampaikan orang lain. Dapat menyampaikan saran/masukan ke orang lain dengan cara yang tepat',
                5 => 'Dapat menyampaikan informasi sesuai aktual yang terjadi secara ringkas, jelas, & benar, serta tidak salah mengartikan informasi yang diterima. Dapat meyakinkan orang lain dengan argumennya atas dasar yang jelas dan benar. Terbuka terhadap saran dan kritik yang disampaikan orang lain. Dapat menyampaikan saran/masukan ke orang lain dengan cara yang tepat',
            ],
            1
        );

        $this->createCriteria(
            $groupB,
            $subgroupII,
            'Kerjasama',
            3,
            'custom_text',
            [
                1 => 'Sulit bekerja dalam kelompok dan cenderung mengutamakan kepentingan pribadi di atas kepentingan kelompok sehingga timbul konflik antar grup',
                2 => 'Dapat bekerja dalam kelompok namun terkadang masih mengutamakan kepentingan pribadi di atas kepentingan kelompok sehingga timbul konflik di internal grup nya',
                3 => 'Dapat bekerja dalam kelompok dan memiliki hubungan kerja yang baik dengan atasan, bawahan, dan rekan kerja. Mau melibatkan diri dalam mencapai tujuan bersama sesuai dengan arahan dan standar kerja nya',
                4 => 'Dapat bekerja dalam kelompok dan memiliki hubungan kerja yang baik dengan atasan, bawahan, dan rekan kerja, serta dapat meredam konflik yang terjadi. Memiliki inisiatif melibatkan diri dalam mencapai tujuan bersama sesuai dengan arahan dan standar kerja nya',
                5 => 'Dapat bekerja dalam kelompok dan memiliki hubungan kerja yang baik dengan atasan, bawahan, dan rekan kerja, serta dapat meredam konflik yang terjadi. Memiliki inisiatif melibatkan diri dalam mencapai tujuan bersama sesuai dengan arahan dan standar kerja nya. Lebih mengutamakan kepentingan kelompok daripada kepentingan pribadi',
            ],
            2
        );

        $subgroupIII = EvaluationCriteriaSubgroup::updateOrCreate(
            ['group_id' => $groupB->id, 'roman_code' => 'III'],
            ['name' => 'Ide Perbaikan (SS)', 'order' => 3]
        );

        $this->createCriteria(
            $groupB,
            $subgroupIII,
            null,
            10,
            'custom_text',
            [
                1 => 'Membuat Ide Perbaikan dan belum mendapat rekomendasi untuk dilaksanakan',
                2 => 'Membuat Ide Perbaikan < 3 dan mendapat 1 rekomendasi untuk dilaksanakan',
                3 => 'Membuat Ide Perbaikan < 4 dan mendapat 2 rekomendasi untuk di laksanakan',
                4 => 'Membuat Ide Perbaikan < 5 dan mendapat 3 rekomendasi untuk di laksanakan',
                5 => 'Membuat Ide Perbaikan < 6 dan mendapat 4 rekomendasi untuk dilaksanakan',
            ],
            1
        );

        $subgroupIV = EvaluationCriteriaSubgroup::updateOrCreate(
            ['group_id' => $groupB->id, 'roman_code' => 'IV'],
            ['name' => 'Ketelitian', 'order' => 4]
        );

        $this->createCriteria($groupB, $subgroupIV, null, 3, 'standard', [], 1);

        $subgroupV = EvaluationCriteriaSubgroup::updateOrCreate(
            ['group_id' => $groupB->id, 'roman_code' => 'V'],
            ['name' => 'Semangat Kerja', 'order' => 5]
        );

        $this->createCriteria($groupB, $subgroupV, null, 3, 'standard', [], 1);

        $subgroupVI = EvaluationCriteriaSubgroup::updateOrCreate(
            ['group_id' => $groupB->id, 'roman_code' => 'VI'],
            ['name' => 'Etika Kerja', 'order' => 6]
        );

        $this->createCriteria($groupB, $subgroupVI, null, 4, 'standard', [], 1);

        $totalWeight = EvaluationCriteria::sum('weight');
        Log::info("EvaluationCriteriaSeeder: total weight = {$totalWeight}");

        if ((float) $totalWeight !== 100.0) {
            throw new \RuntimeException("Evaluation criteria total weight must equal 100, got {$totalWeight}");
        }
    }

    private function createCriteria(
        EvaluationCriteriaGroup $group,
        ?EvaluationCriteriaSubgroup $subgroup,
        ?string $name,
        float $weight,
        string $scaleType,
        array $scaleOptions = [],
        int $order = 0
    ): EvaluationCriteria {
        $criteria = EvaluationCriteria::updateOrCreate(
            [
                'group_id' => $group->id,
                'subgroup_id' => $subgroup?->id,
                'name' => $name,
            ],
            [
                'weight' => $weight,
                'scale_type' => $scaleType,
                'is_active' => true,
                'order' => $order,
            ]
        );

        if ($scaleType === 'custom_text') {
            foreach ($scaleOptions as $score => $description) {
                EvaluationCriteriaScaleOptions::updateOrCreate(
                    [
                        'criteria_id' => $criteria->id,
                        'score' => $score,
                    ],
                    [
                        'description' => $description,
                    ]
                );
            }
        } else {
            EvaluationCriteriaScaleOptions::where('criteria_id', $criteria->id)->delete();
        }

        return $criteria;
    }
}
