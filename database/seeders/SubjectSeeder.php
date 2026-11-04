<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'PAI',
                'name' => 'Pendidikan Agama Islam dan Budi Pekerti',
                'group' => 'Keislaman',
                'order' => 1,
                'is_active' => true,
                'description' => 'Mata pelajaran PAI dan Budi Pekerti kurikulum nasional dan kekhasan Islam.',
            ],
            [
                'code' => 'GPQ',
                'name' => "Pendidikan Al-Qur'an (Tahsin / Tahfidz)",
                'group' => 'Keislaman',
                'order' => 2,
                'is_active' => true,
                'description' => "Pembelajaran Al-Qur'an, tahsin, tahfidz, dan tilawah.",
            ],
            [
                'code' => 'PP',
                'name' => 'Pendidikan Pancasila',
                'group' => 'Wajib',
                'order' => 3,
                'is_active' => true,
                'description' => 'Pendidikan Pancasila dan Kewarganegaraan.',
            ],
            [
                'code' => 'BIN',
                'name' => 'Bahasa Indonesia',
                'group' => 'Wajib',
                'order' => 4,
                'is_active' => true,
                'description' => 'Bahasa dan Sastra Indonesia.',
            ],
            [
                'code' => 'MTK',
                'name' => 'Matematika',
                'group' => 'Wajib',
                'order' => 5,
                'is_active' => true,
                'description' => 'Matematika SMP.',
            ],
            [
                'code' => 'IPA',
                'name' => 'Ilmu Pengetahuan Alam (IPA)',
                'group' => 'Wajib',
                'order' => 6,
                'is_active' => true,
                'description' => 'Fisika, Biologi, dan Kimia terpadu tingkat SMP.',
            ],
            [
                'code' => 'IPS',
                'name' => 'Ilmu Pengetahuan Sosial (IPS)',
                'group' => 'Wajib',
                'order' => 7,
                'is_active' => true,
                'description' => 'Sejarah, Geografi, Sosiologi, dan Ekonomi terpadu.',
            ],
            [
                'code' => 'BIG',
                'name' => 'Bahasa Inggris',
                'group' => 'Wajib',
                'order' => 8,
                'is_active' => true,
                'description' => 'Bahasa Inggris komunikatif dan literasi.',
            ],
            [
                'code' => 'INF',
                'name' => 'Informatika',
                'group' => 'Wajib',
                'order' => 9,
                'is_active' => true,
                'description' => 'Informatika, komputasi, dan literasi digital.',
            ],
            [
                'code' => 'PJOK',
                'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
                'group' => 'Wajib',
                'order' => 10,
                'is_active' => true,
                'description' => 'Kebugaran jasmani, olahraga, dan kesehatan.',
            ],
            [
                'code' => 'SBK',
                'name' => 'Seni dan Prakarya',
                'group' => 'Wajib',
                'order' => 11,
                'is_active' => true,
                'description' => 'Seni Rupa, Musik, Tari, Teater, dan Prakarya.',
            ],
            [
                'code' => 'BJAWA',
                'name' => 'Bahasa Jawa',
                'group' => 'Muatan Lokal',
                'order' => 12,
                'is_active' => true,
                'description' => 'Muatan lokal Bahasa Jawa Provinsi Jawa Timur.',
            ],
        ];

        foreach ($subjects as $s) {
            Subject::updateOrCreate(
                ['code' => $s['code']],
                $s
            );
        }
    }
}

