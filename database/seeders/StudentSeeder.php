<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('DATABASE MURID SMP 2026-2027.xlsx');
        if (!file_exists($filePath)) {
            $this->command?->warn("File {$filePath} tidak ditemukan, seeding dilewati.");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('MASTER') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $ay = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
        $level7 = ClassLevel::where('code', '7')->orWhere('name', 'like', '%7%')->first();
        $classroom = Classroom::where('name', 'like', '%Samudra Pasai%')->first() 
            ?? Classroom::where('class_level_id', $level7?->id)->first();

        $count = 0;
        for ($r = 2; $r <= $highestRow; $r++) {
            $fullName = $this->cleanStr($sheet->getCell('K' . $r)->getFormattedValue());
            if (empty($fullName)) continue;

            $nis = $this->cleanStr($sheet->getCell('F' . $r)->getFormattedValue());
            $nisn = $this->cleanStr($sheet->getCell('G' . $r)->getFormattedValue());
            $genderRaw = strtoupper($this->cleanStr($sheet->getCell('P' . $r)->getFormattedValue()) ?? '');
            $gender = str_contains($genderRaw, 'PEREMPUAN') ? 'P' : 'L';

            $studentTypeRaw = strtoupper($this->cleanStr($sheet->getCell('T' . $r)->getFormattedValue()) ?? 'REGULER');
            $studentType = str_contains($studentTypeRaw, 'MBK') || str_contains($studentTypeRaw, 'KHUSUS') ? 'MBK' : 'Reguler';

            $studentData = [
                'nis' => $nis,
                'nisn' => $nisn,
                'nik' => $this->cleanStr($sheet->getCell('O' . $r)->getFormattedValue()),
                'no_kk' => $this->cleanStr($sheet->getCell('J' . $r)->getFormattedValue()),
                'birth_certificate_no' => $this->cleanStr($sheet->getCell('I' . $r)->getFormattedValue()),
                'full_name' => $fullName,
                'nickname' => $this->cleanStr($sheet->getCell('N' . $r)->getFormattedValue()),
                'gender' => $gender,
                'birth_place' => $this->cleanStr($sheet->getCell('Q' . $r)->getFormattedValue()),
                'birth_date' => $this->parseDate($sheet->getCell('R' . $r)->getFormattedValue()),
                'religion' => $this->cleanStr($sheet->getCell('AM' . $r)->getFormattedValue()) ?? 'Islam',
                'citizenship' => $this->cleanStr($sheet->getCell('AL' . $r)->getFormattedValue()) ?? 'WNI',
                'academic_year_id' => $ay?->id,
                'class_level_id' => $level7?->id,
                'classroom_id' => $classroom?->id,
                'student_type' => $studentType,
                'special_needs_type' => $this->cleanStr($sheet->getCell('U' . $r)->getFormattedValue()),
                'address' => $this->cleanStr($sheet->getCell('V' . $r)->getFormattedValue()),
                'rt' => $this->cleanStr($sheet->getCell('W' . $r)->getFormattedValue()),
                'rw' => $this->cleanStr($sheet->getCell('X' . $r)->getFormattedValue()),
                'village' => $this->cleanStr($sheet->getCell('Y' . $r)->getFormattedValue()),
                'district' => $this->cleanStr($sheet->getCell('Z' . $r)->getFormattedValue()),
                'city' => $this->cleanStr($sheet->getCell('AA' . $r)->getFormattedValue()),
                'postal_code' => $this->cleanStr($sheet->getCell('AB' . $r)->getFormattedValue()),
                'home_phone' => $this->cleanStr($sheet->getCell('AC' . $r)->getFormattedValue()),
                'parent_phone' => $this->cleanStr($sheet->getCell('AD' . $r)->getFormattedValue()),
                'father_name' => $this->cleanStr($sheet->getCell('AE' . $r)->getFormattedValue()) ?? $this->cleanStr($sheet->getCell('AU' . $r)->getFormattedValue()),
                'mother_name' => $this->cleanStr($sheet->getCell('AF' . $r)->getFormattedValue()) ?? $this->cleanStr($sheet->getCell('BR' . $r)->getFormattedValue()),
                'child_number' => $this->cleanInt($sheet->getCell('AG' . $r)->getFormattedValue()),
                'siblings_count' => $this->cleanInt($sheet->getCell('AH' . $r)->getFormattedValue()),
                'step_siblings_count' => $this->cleanInt($sheet->getCell('AI' . $r)->getFormattedValue()),
                'adoptive_siblings_count' => $this->cleanInt($sheet->getCell('AJ' . $r)->getFormattedValue()),
                'home_language' => $this->cleanStr($sheet->getCell('AK' . $r)->getFormattedValue()),
                'ethnic_group' => $this->cleanStr($sheet->getCell('AN' . $r)->getFormattedValue()),
                'weight' => $this->cleanInt($sheet->getCell('AO' . $r)->getFormattedValue()),
                'height' => $this->cleanInt($sheet->getCell('AP' . $r)->getFormattedValue()),
                'blood_type' => $this->cleanStr($sheet->getCell('AQ' . $r)->getFormattedValue()),
                'skin_color' => $this->cleanStr($sheet->getCell('AR' . $r)->getFormattedValue()),
                'hair_type' => $this->cleanStr($sheet->getCell('AS' . $r)->getFormattedValue()),
                'hair_color' => $this->cleanStr($sheet->getCell('AT' . $r)->getFormattedValue()),

                // Father details
                'father_birth_place' => $this->cleanStr($sheet->getCell('AV' . $r)->getFormattedValue()),
                'father_birth_date' => $this->parseDate($sheet->getCell('AW' . $r)->getFormattedValue()),
                'father_nik' => $this->cleanStr($sheet->getCell('AX' . $r)->getFormattedValue()),
                'father_address' => $this->cleanStr($sheet->getCell('AY' . $r)->getFormattedValue()),
                'father_religion' => $this->cleanStr($sheet->getCell('BH' . $r)->getFormattedValue()),
                'father_phone' => $this->cleanStr($sheet->getCell('BI' . $r)->getFormattedValue()),
                'father_education' => $this->cleanStr($sheet->getCell('BJ' . $r)->getFormattedValue()),
                'father_job' => $this->cleanStr($sheet->getCell('BK' . $r)->getFormattedValue()),
                'father_company' => $this->cleanStr($sheet->getCell('BL' . $r)->getFormattedValue()),
                'father_company_address' => $this->cleanStr($sheet->getCell('BM' . $r)->getFormattedValue()),
                'father_company_phone' => $this->cleanStr($sheet->getCell('BN' . $r)->getFormattedValue()),
                'father_income' => $this->cleanStr($sheet->getCell('BO' . $r)->getFormattedValue()),
                'father_email' => $this->cleanStr($sheet->getCell('BP' . $r)->getFormattedValue()),
                'father_social_media' => $this->cleanStr($sheet->getCell('BQ' . $r)->getFormattedValue()),

                // Mother details
                'mother_birth_place' => $this->cleanStr($sheet->getCell('BS' . $r)->getFormattedValue()),
                'mother_birth_date' => $this->parseDate($sheet->getCell('BT' . $r)->getFormattedValue()),
                'mother_nik' => $this->cleanStr($sheet->getCell('BU' . $r)->getFormattedValue()),
                'mother_address' => $this->cleanStr($sheet->getCell('BV' . $r)->getFormattedValue()),
                'mother_religion' => $this->cleanStr($sheet->getCell('CD' . $r)->getFormattedValue()),
                'mother_phone' => $this->cleanStr($sheet->getCell('CE' . $r)->getFormattedValue()),
                'mother_education' => $this->cleanStr($sheet->getCell('CF' . $r)->getFormattedValue()),
                'mother_job' => $this->cleanStr($sheet->getCell('CG' . $r)->getFormattedValue()),
                'mother_company' => $this->cleanStr($sheet->getCell('CH' . $r)->getFormattedValue()),
                'mother_company_address' => $this->cleanStr($sheet->getCell('CI' . $r)->getFormattedValue()),
                'mother_company_phone' => $this->cleanStr($sheet->getCell('CJ' . $r)->getFormattedValue()),
                'mother_income' => $this->cleanStr($sheet->getCell('CK' . $r)->getFormattedValue()),
                'mother_email' => $this->cleanStr($sheet->getCell('CL' . $r)->getFormattedValue()),
                'mother_social_media' => $this->cleanStr($sheet->getCell('CM' . $r)->getFormattedValue()),

                // Guardian details
                'guardian_name' => $this->cleanStr($sheet->getCell('CN' . $r)->getFormattedValue()),
                'guardian_birth_place' => $this->cleanStr($sheet->getCell('CO' . $r)->getFormattedValue()),
                'guardian_birth_date' => $this->parseDate($sheet->getCell('CP' . $r)->getFormattedValue()),
                'guardian_nik' => $this->cleanStr($sheet->getCell('CQ' . $r)->getFormattedValue()),
                'guardian_address' => $this->cleanStr($sheet->getCell('CR' . $r)->getFormattedValue()),
                'guardian_religion' => $this->cleanStr($sheet->getCell('CY' . $r)->getFormattedValue()),
                'guardian_phone' => $this->cleanStr($sheet->getCell('CZ' . $r)->getFormattedValue()),
                'guardian_education' => $this->cleanStr($sheet->getCell('DA' . $r)->getFormattedValue()),
                'guardian_job' => $this->cleanStr($sheet->getCell('DB' . $r)->getFormattedValue()),
                'guardian_company' => $this->cleanStr($sheet->getCell('DC' . $r)->getFormattedValue()),
                'guardian_company_address' => $this->cleanStr($sheet->getCell('DD' . $r)->getFormattedValue()),
                'guardian_company_phone' => $this->cleanStr($sheet->getCell('DE' . $r)->getFormattedValue()),
                'guardian_income' => $this->cleanStr($sheet->getCell('DF' . $r)->getFormattedValue()),
                'guardian_email' => $this->cleanStr($sheet->getCell('DG' . $r)->getFormattedValue()),
                'guardian_social_media' => $this->cleanStr($sheet->getCell('DH' . $r)->getFormattedValue()),

                // Previous school / origin
                'origin_category' => $this->cleanStr($sheet->getCell('C' . $r)->getFormattedValue()),
                'previous_school' => $this->cleanStr($sheet->getCell('D' . $r)->getFormattedValue()),
                'previous_school_address' => $this->cleanStr($sheet->getCell('E' . $r)->getFormattedValue()),
                'diploma_number' => $this->cleanStr($sheet->getCell('H' . $r)->getFormattedValue()),
                'enrollment_date' => $this->parseDate($sheet->getCell('B' . $r)->getFormattedValue()),
                'enrollment_type' => 'import',
                'status' => 'aktif',
            ];

            Student::updateOrCreate(
                ['nis' => $nis],
                $studentData
            );
            $count++;
        }

        $this->command?->info("Berhasil mengimpor {$count} data siswa SMP dari {$filePath}.");
    }

    private function parseDate($val): ?string
    {
        if (empty($val) || $val === '-' || $val === '#VALUE!') return null;

        if (is_numeric($val) && $val > 1000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        $val = trim((string)$val);
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[1], $m[2]);
        }

        $months = [
            'JANUARI' => '01', 'JAN' => '01',
            'FEBRUARI' => '02', 'PEBRUARI' => '02', 'FEB' => '02',
            'MARET' => '03', 'MAR' => '03',
            'APRIL' => '04', 'APR' => '04',
            'MEI' => '05',
            'JUNI' => '06', 'JUN' => '06',
            'JULI' => '07', 'JUL' => '07',
            'AGUSTUS' => '08', 'AGT' => '08', 'AGU' => '08',
            'SEPTEMBER' => '09', 'SEP' => '09',
            'OKTOBER' => '10', 'OKT' => '10',
            'NOVEMBER' => '11', 'NOPEMBER' => '11', 'NOV' => '11',
            'DESEMBER' => '12', 'DES' => '12'
        ];

        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $val, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $monthName = strtoupper($m[2]);
            $year = $m[3];
            $month = $months[$monthName] ?? '01';
            return "{$year}-{$month}-{$day}";
        }

        return null;
    }

    private function cleanStr($val): ?string
    {
        if ($val === null || $val === '' || $val === '-' || $val === '#VALUE!') return null;
        return trim((string)$val);
    }

    private function cleanInt($val): ?int
    {
        if (empty($val) || $val === '-' || !is_numeric($val)) return null;
        return (int)$val;
    }
}
