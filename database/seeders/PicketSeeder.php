<?php

namespace Database\Seeders;

use App\Models\PicketArea;
use Illuminate\Database\Seeder;

class PicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areasData = [
            [
                'name' => 'IN FRONT OF MOSQUE/SECURITY AREA',
                'duty_hours' => '06.30 - 07.00',
                'jobs' => "Making sure all teachers are in duty based on schedule (Memastikan seluruh kegiatan piket berjalan sesuai tupoksi)\nGreeting the students with Salam, Smile, Greet, Salim, and Make sure the students surveillance and safety (Menyambut ananda dengan Salam, Senyum, Sapa, Salim, Memastikan pengawasan dan keamanan ananda)",
                'is_active' => true,
            ],
            [
                'name' => 'IN FRONT OF INNER GATE',
                'duty_hours' => '06.30 - 07.00',
                'jobs' => "Greeting the students with Salam, Smile, Greet, Salim, and Make sure the students surveillance and safety (Menyambut ananda dengan Salam, Senyum, Sapa, Salim, Memastikan pengawasan dan keamanan ananda)",
                'is_active' => true,
            ],
            [
                'name' => 'AROUND CANTEEN / HALL',
                'duty_hours' => '06.30 - 07.00',
                'jobs' => "Greeting the teachers with Salam, Smile, Greet, Shake hand, make sure the students surveillance and safety and also ring the bell on 06.55 a.m (Menyambut guru dengan Salam, Senyum, Sapa, Salaman dan Memastikan pengawasan dan keamanan ananda dan membunyikan bel jam 06.55)",
                'is_active' => true,
            ]
        ];

        foreach ($areasData as $areaVal) {
            PicketArea::updateOrCreate(
                ['name' => $areaVal['name']],
                $areaVal
            );
        }
    }
}
