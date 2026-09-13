<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Classroom;
use Illuminate\Database\Seeder;

class AcademicMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Academic Years
        $academicYears = [
            [
                'name' => '2026/2027',
                'code' => '2627',
                'semester' => 'ganjil',
                'is_active' => true,
                'start_date' => '2026-07-15',
                'end_date' => '2027-06-30',
                'description' => 'Tahun Ajaran Aktif Berjalan 2026/2027 SMP Anak Saleh',
            ],
            [
                'name' => '2027/2028',
                'code' => '2728',
                'semester' => 'ganjil',
                'is_active' => false,
                'start_date' => '2027-07-15',
                'end_date' => '2028-06-30',
                'description' => 'Tahun Ajaran Baru SPMB 2027/2028 SMP Anak Saleh',
            ],
        ];

        foreach ($academicYears as $ayData) {
            AcademicYear::updateOrCreate(
                ['name' => $ayData['name']],
                $ayData
            );
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $nextYear = AcademicYear::where('name', '2027/2028')->first();

        // 2. Class Levels for SMP (Kelas 7 - 9)
        $classLevels = [
            [
                'name' => 'Kelas 7',
                'code' => '7',
                'order_level' => 1,
                'description' => 'Tingkat Kelas 7 SMP (Fase D Awal)',
            ],
            [
                'name' => 'Kelas 8',
                'code' => '8',
                'order_level' => 2,
                'description' => 'Tingkat Kelas 8 SMP (Fase D Madya)',
            ],
            [
                'name' => 'Kelas 9',
                'code' => '9',
                'order_level' => 3,
                'description' => 'Tingkat Kelas 9 SMP (Fase D Akhir / Kelulusan)',
            ],
        ];

        $levelMap = [];
        foreach ($classLevels as $levelData) {
            $level = ClassLevel::updateOrCreate(
                ['code' => $levelData['code']],
                $levelData
            );
            $levelMap[$level->code] = $level;
        }

        // 3. Classrooms (Rombongan Belajar)
        // Letters: A, B per level
        $rombelLetters = ['A', 'B'];

        $targetYears = array_filter([$activeYear, $nextYear]);

        foreach ($targetYears as $ay) {
            foreach ($levelMap as $code => $level) {
                foreach ($rombelLetters as $letter) {
                    $rombelName = "{$code}-{$letter}"; // e.g. 7-A, 7-B, 8-A, etc.
                    Classroom::updateOrCreate(
                        [
                            'academic_year_id' => $ay->id,
                            'class_level_id' => $level->id,
                            'name' => $rombelName,
                        ],
                        [
                            'room_number' => "Ruang {$rombelName}",
                            'capacity' => 32,
                            'is_active' => true,
                            'description' => "Rombel {$rombelName} SMP Anak Saleh TA {$ay->name}",
                        ]
                    );
                }
            }
        }
    }
}
