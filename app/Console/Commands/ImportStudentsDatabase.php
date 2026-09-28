<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;
use App\Models\StudentClassroomHistory;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class ImportStudentsDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:import-database {file? : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import official student database and classrooms from master Excel 2026/2027 for SMP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?: public_path('DATABASE MURID SMP 2026-2027.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan di: {$filePath}");
            return 1;
        }

        $this->info("Membaca file Excel: {$filePath}...");

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        // 1. Setup / Dapatkan Tahun Ajaran 2026/2027 (Berjalan)
        $academicYear = AcademicYear::where('name', '2026/2027')->where('is_active', true)->first()
            ?: (AcademicYear::where('name', '2026/2027')->first()
            ?: (AcademicYear::where('name', '2026-2027')->first()
            ?: (AcademicYear::where('code', '2627')->first()
            ?: (AcademicYear::where('is_active', true)->first()
            ?: AcademicYear::firstOrCreate(
                ['name' => '2026/2027'],
                [
                    'code' => '2627',
                    'semester' => 'ganjil',
                    'is_active' => true,
                    'start_date' => '2026-07-15',
                    'end_date' => '2027-06-30',
                    'description' => 'Tahun Pelajaran 2026/2027 (Berjalan) SMP Anak Saleh',
                ]
            )))));

        $this->info("Tahun Ajaran Terpilih: {$academicYear->name} (ID: {$academicYear->id})");

        // 2. Setup 3 Tingkat Kelas SMP (7, 8, 9)
        $levels = [];
        $levelDefinitions = [
            '7' => ['name' => 'Kelas 7', 'order' => 1, 'desc' => 'Tingkat Kelas 7 SMP (Fase D Awal)'],
            '8' => ['name' => 'Kelas 8', 'order' => 2, 'desc' => 'Tingkat Kelas 8 SMP (Fase D Madya)'],
            '9' => ['name' => 'Kelas 9', 'order' => 3, 'desc' => 'Tingkat Kelas 9 SMP (Fase D Akhir / Kelulusan)'],
        ];

        foreach ($levelDefinitions as $code => $def) {
            $levels[$code] = ClassLevel::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $def['name'],
                    'order' => $def['order'],
                    'order_level' => $def['order'],
                    'description' => $def['desc'],
                ]
            );
        }

        // 3. Setup Rombel Default SMP Anak Saleh
        $classroomDefinitions = [
            '7A' => ['level' => '7', 'name' => '7A - Samudra Pasai', 'code' => '7A', 'wali' => 'Davies Yudisno', 'gpk' => null],
            '7B' => ['level' => '7', 'name' => '7B - Demak', 'code' => '7B', 'wali' => null, 'gpk' => null],
            '8A' => ['level' => '8', 'name' => '8A - Majapahit', 'code' => '8A', 'wali' => null, 'gpk' => null],
            '8B' => ['level' => '8', 'name' => '8B - Mataram', 'code' => '8B', 'wali' => null, 'gpk' => null],
            '9A' => ['level' => '9', 'name' => '9A - Sriwijaya', 'code' => '9A', 'wali' => null, 'gpk' => null],
            '9B' => ['level' => '9', 'name' => '9B - Singhasari', 'code' => '9B', 'wali' => null, 'gpk' => null],
        ];

        // Load active employees for matching homeroom and GPK
        $employees = Employee::whereIn('status', ['Active', 'aktif', 'active', 'Aktif'])->get();

        $classroomMap = [];
        foreach ($classroomDefinitions as $code => $def) {
            $homeroomEmployee = null;
            if (!empty($def['wali'])) {
                $homeroomEmployee = $employees->first(function ($e) use ($def) {
                    return stripos($e->name, $def['wali']) !== false;
                });
            }

            $classroom = Classroom::updateOrCreate(
                [
                    'code' => $def['code'],
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'name' => $def['name'],
                    'class_level_id' => $levels[$def['level']]->id,
                    'homeroom_teacher_id' => $homeroomEmployee?->id,
                    'capacity' => 32,
                    'is_active' => true,
                    'description' => "Rombel {$def['name']} SMP Anak Saleh TA {$academicYear->name}",
                ]
            );

            // Register various aliases for robust matching
            $classroomMap[$code] = $classroom;
            $classroomMap[strtoupper($def['name'])] = $classroom;
            $classroomMap[strtoupper($def['code'])] = $classroom;
            $classroomMap[strtoupper(str_replace([' ', '-'], '', $def['name']))] = $classroom;
            $cleanName = strtoupper(trim(preg_replace('/^7[A-Z]?\s*[-–]?\s*/i', '', $def['name'])));
            if (!empty($cleanName)) {
                $classroomMap[$cleanName] = $classroom;
                $classroomMap["7 - {$cleanName}"] = $classroom;
                $classroomMap["7 {$cleanName}"] = $classroom;
            }
        }

        $this->info("Rombel dasar SMP Anak Saleh berhasil disiapkan!");

        // 4. Helper Normalisasi
        $formatTitleCase = function (?string $string): ?string {
            if ($string === null) return null;
            $string = trim(preg_replace('/\s+/', ' ', $string));
            if ($string === '') return null;

            $words = explode(' ', $string);
            $result = [];

            $specialTerms = [
                'dr.' => 'Dr.', 'drh.' => 'Drh.', 'dr' => 'Dr.', 'drh' => 'Drh.',
                'ir.' => 'Ir.', 'ir' => 'Ir.', 'prof.' => 'Prof.', 'prof' => 'Prof.',
                'h.' => 'H.', 'hj.' => 'Hj.', 's.t.' => 'S.T.', 's.t' => 'S.T.',
                's.pd.' => 'S.Pd.', 's.pd' => 'S.Pd.', 'm.pd.' => 'M.Pd.', 'm.pd' => 'M.Pd.',
                's.kom.' => 'S.Kom.', 's.kom' => 'S.Kom.', 's.si.' => 'S.Si.', 's.si' => 'S.Si.',
                's.ap.' => 'S.Ap.', 's.ap' => 'S.Ap.', 's.e.' => 'S.E.', 's.e' => 'S.E.',
                'se.' => 'S.E.', 'se' => 'S.E.', 's.sos.' => 'S.Sos.', 's.sos' => 'S.Sos.',
                's.h.' => 'S.H.', 's.h' => 'S.H.', 's.psi.' => 'S.Psi.', 's.psi' => 'S.Psi.',
                's.ked.' => 'S.Ked.', 's.ked' => 'S.Ked.', 's.ag.' => 'S.Ag.', 's.ag' => 'S.Ag.',
                's.hum.' => 'S.Hum.', 's.hum' => 'S.Hum.', 's.sn.' => 'S.Sn.', 's.sn' => 'S.Sn.',
                's.farm.' => 'S.Farm.', 's.farm' => 'S.Farm.', 's.ik.' => 'S.IK.', 's.ik' => 'S.IK.',
                's.kel.' => 'S.Kel.', 's.kel' => 'S.Kel.', 's.mat.' => 'S.Mat.', 's.mat' => 'S.Mat.',
                's.stat.' => 'S.Stat.', 's.stat' => 'S.Stat.', 's.tr.kom' => 'S.Tr.Kom', 's.tr.kom.' => 'S.Tr.Kom.',
                's.pd.sd' => 'S.Pd.SD', 's.pd.sd.' => 'S.Pd.SD.', 's.pd.i' => 'S.Pd.I', 's.pd.i.' => 'S.Pd.I.',
                'm.si.' => 'M.Si.', 'm.si' => 'M.Si.', 'm.m.' => 'M.M.', 'm.m' => 'M.M.',
                'm.ag.' => 'M.Ag.', 'm.ag' => 'M.Ag.', 'm.hum.' => 'M.Hum.', 'm.hum' => 'M.Hum.',
                'm.kom.' => 'M.Kom.', 'm.kom' => 'M.Kom.', 'm.psi.' => 'M.Psi.', 'm.psi' => 'M.Psi.',
                'gr.' => 'Gr.', 'gr' => 'Gr.',
                'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'smk' => 'SMK',
                'tk' => 'TK', 'ra' => 'RA', 'ba' => 'BA', 'mi' => 'MI', 'mts' => 'MTs', 'ma' => 'MA',
                'wni' => 'WNI', 'wna' => 'WNA', 'pdbk' => 'PDBK', 'adhd' => 'ADHD',
                'rt' => 'RT', 'rw' => 'RW', 'kk' => 'KK', 'nik' => 'NIK', 'nis' => 'NIS', 'nisn' => 'NISN',
                'ii' => 'II', 'iii' => 'III', 'iv' => 'IV', 'vi' => 'VI', 'vii' => 'VII', 'viii' => 'VIII', 'ix' => 'IX', 'xi' => 'XI', 'xii' => 'XII',
            ];

            foreach ($words as $w) {
                $cleanLower = mb_strtolower($w, 'UTF-8');
                if (isset($specialTerms[$cleanLower])) {
                    $result[] = $specialTerms[$cleanLower];
                } else {
                    $result[] = mb_convert_case($w, MB_CASE_TITLE, 'UTF-8');
                }
            }

            return implode(' ', $result);
        };

        $normalizeReligion = function (?string $rel) use ($formatTitleCase): string {
            if (empty($rel)) return 'Islam';
            $r = strtoupper(trim($rel));
            if (str_contains($r, 'ISLAM') || str_contains($r, 'IS;AM')) return 'Islam';
            if (str_contains($r, 'KRISTEN') || str_contains($r, 'PROTESTAN')) return 'Kristen';
            if (str_contains($r, 'KATOLIK') || str_contains($r, 'CATHOLIC')) return 'Katolik';
            if (str_contains($r, 'HINDU')) return 'Hindu';
            if (str_contains($r, 'BUDHA') || str_contains($r, 'BUDDHA')) return 'Buddha';
            if (str_contains($r, 'KONGHUCU') || str_contains($r, 'KHONGHUCU')) return 'Konghucu';
            return $formatTitleCase($rel) ?: 'Islam';
        };

        $normalizeCitizenship = function (?string $cit): string {
            if (empty($cit)) return 'WNI';
            $c = strtoupper(trim($cit));
            if (str_contains($c, 'INDONESIA') || $c === 'WNI') return 'WNI';
            if (str_contains($c, 'ASING') || $c === 'WNA') return 'WNA';
            return 'WNI';
        };

        $cleanStr = function ($val) {
            if ($val === null) return null;
            $s = trim((string)$val);
            return ($s === '' || $s === '-' || $s === '#VALUE!' || $s === 'N/A' || $s === 'null') ? null : $s;
        };

        $cleanInt = function ($val) {
            if ($val === null) return null;
            $v = trim((string)$val);
            if ($v === '' || $v === '-' || !is_numeric($v)) return null;
            return (int)$v;
        };

        $cleanPhone = function ($raw) {
            if (empty($raw) || trim((string)$raw) === '-' || trim((string)$raw) === '0') return null;
            $p = preg_replace('/[^0-9]/', '', (string)$raw);
            if (empty($p)) return null;
            if (str_starts_with($p, '0')) {
                $p = '62' . substr($p, 1);
            }
            return $p;
        };

        $parseDate = function ($val) {
            if (empty($val)) return null;
            $str = trim((string)$val);
            if ($str === '' || $str === '-' || $str === '#VALUE!') return null;

            if (is_numeric($val) && (int)$val > 1000) {
                try {
                    return ExcelDate::excelToDateTimeObject($val)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }

            // Handle Indonesian month names like "16 AGUSTUS 2013", "03 JULI 2026"
            $indoMonths = [
                'JANUARI' => '01', 'FEBRUARI' => '02', 'MARET' => '03', 'APRIL' => '04',
                'MEI' => '05', 'JUNI' => '06', 'JULI' => '07', 'AGUSTUS' => '08',
                'SEPTEMBER' => '09', 'OKTOBER' => '10', 'NOVEMBER' => '11', 'DESEMBER' => '12',
            ];
            $upper = strtoupper($str);
            foreach ($indoMonths as $mName => $mNum) {
                if (str_contains($upper, $mName)) {
                    if (preg_match('/(\d{1,2})\s+' . $mName . '\s+(\d{4})/i', $upper, $matches)) {
                        $d = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                        $y = $matches[2];
                        return "{$y}-{$mNum}-{$d}";
                    }
                }
            }

            try {
                return Carbon::parse($str)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        // 5. Baca Sheet MASTER & Import Data Siswa
        $sheet = $spreadsheet->getSheetByName('MASTER') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $this->info("Mengimpor data siswa dari Sheet MASTER (Total baris: {$highestRow})...");

        $bar = $this->output->createProgressBar($highestRow - 1);
        $bar->start();

        $imported = 0;
        $updated = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $rawFullName = $cleanStr($sheet->getCell('K' . $r)->getValue());
            if (empty($rawFullName)) {
                $bar->advance();
                continue;
            }

            $fullName = $formatTitleCase($rawFullName);
            $rawNis = $cleanStr($sheet->getCell('F' . $r)->getValue());
            $nis = !empty($rawNis) ? $rawNis : 'SMP.' . str_pad($r, 4, '0', STR_PAD_LEFT);

            $rawGrade = $cleanStr($sheet->getCell('L' . $r)->getValue()) ?? '7';
            $rawClassName = strtoupper($cleanStr($sheet->getCell('M' . $r)->getValue()) ?? 'SAMUDRA PASAI');

            // Find matching class level
            $classLevel = $levels[$rawGrade] ?? ($levels['7'] ?? ClassLevel::first());

            // Match or create classroom dynamically
            $classroom = $classroomMap[$rawClassName] 
                ?? ($classroomMap["{$rawGrade} - {$rawClassName}"] 
                ?? ($classroomMap["{$rawGrade}A"] 
                ?? Classroom::where('class_level_id', $classLevel?->id)->where('academic_year_id', $academicYear->id)->first()));

            if (!$classroom && !empty($rawClassName)) {
                $classroom = Classroom::firstOrCreate(
                    [
                        'name' => "{$rawGrade}A - " . $formatTitleCase($rawClassName),
                        'academic_year_id' => $academicYear->id,
                        'class_level_id' => $classLevel?->id,
                    ],
                    [
                        'code' => "{$rawGrade}A",
                        'room_number' => "Ruang {$rawGrade}A",
                        'capacity' => 32,
                        'is_active' => true,
                    ]
                );
                $classroomMap[$rawClassName] = $classroom;
            }

            $genderRaw = strtoupper($cleanStr($sheet->getCell('P' . $r)->getValue()) ?? '');
            $gender = (str_contains($genderRaw, 'PEREMPUAN') || $genderRaw === 'P' || $genderRaw === 'FEMALE') ? 'P' : 'L';

            $studentTypeRaw = strtoupper($cleanStr($sheet->getCell('T' . $r)->getValue()) ?? 'REGULER');
            $specialNeedsType = $cleanStr($sheet->getCell('U' . $r)->getValue());
            $isPdbk = str_contains($studentTypeRaw, 'PDBK') || str_contains($studentTypeRaw, 'MBK') || str_contains($studentTypeRaw, 'KHUSUS') || str_contains($studentTypeRaw, 'INKLUSI') || !empty($specialNeedsType);
            $studentType = $isPdbk ? 'PDBK' : 'REGULER';

            $studentData = [
                'nis' => $nis,
                'nisn' => $cleanStr($sheet->getCell('G' . $r)->getValue()),
                'nik' => $cleanStr($sheet->getCell('O' . $r)->getValue()),
                'no_kk' => $cleanStr($sheet->getCell('J' . $r)->getValue()),
                'birth_certificate_no' => $cleanStr($sheet->getCell('I' . $r)->getValue()),
                'diploma_number' => $cleanStr($sheet->getCell('H' . $r)->getValue()),
                'full_name' => $fullName,
                'nickname' => $formatTitleCase($cleanStr($sheet->getCell('N' . $r)->getValue())),
                'gender' => $gender,
                'birth_place' => $formatTitleCase($cleanStr($sheet->getCell('Q' . $r)->getValue())),
                'birth_date' => $parseDate($sheet->getCell('R' . $r)->getValue()),
                'religion' => $normalizeReligion($cleanStr($sheet->getCell('AM' . $r)->getValue())),
                'citizenship' => $normalizeCitizenship($cleanStr($sheet->getCell('AL' . $r)->getValue())),
                'ethnic_group' => $formatTitleCase($cleanStr($sheet->getCell('AN' . $r)->getValue())),

                // Akademik & Rombel
                'academic_year_id' => $academicYear->id,
                'class_level_id' => $classLevel?->id,
                'classroom_id' => $classroom?->id,
                'student_type' => $studentType,
                'special_needs_type' => $specialNeedsType,

                // Alamat Domisili
                'address' => $cleanStr($sheet->getCell('V' . $r)->getValue()),
                'rt' => $cleanStr($sheet->getCell('W' . $r)->getValue()),
                'rw' => $cleanStr($sheet->getCell('X' . $r)->getValue()),
                'village' => $formatTitleCase($cleanStr($sheet->getCell('Y' . $r)->getValue())),
                'district' => $formatTitleCase($cleanStr($sheet->getCell('Z' . $r)->getValue())),
                'city' => $formatTitleCase($cleanStr($sheet->getCell('AA' . $r)->getValue())),
                'postal_code' => $cleanStr($sheet->getCell('AB' . $r)->getValue()),
                'home_phone' => $cleanPhone($sheet->getCell('AC' . $r)->getValue()),
                'parent_phone' => $cleanPhone($sheet->getCell('AD' . $r)->getValue()),

                // Keluarga & Saudara
                'child_number' => $cleanInt($sheet->getCell('AG' . $r)->getValue()),
                'siblings_count' => $cleanInt($sheet->getCell('AH' . $r)->getValue()),
                'step_siblings_count' => $cleanInt($sheet->getCell('AI' . $r)->getValue()),
                'adoptive_siblings_count' => $cleanInt($sheet->getCell('AJ' . $r)->getValue()),
                'home_language' => $formatTitleCase($cleanStr($sheet->getCell('AK' . $r)->getValue())),

                // Fisik & Kesehatan
                'weight' => $cleanInt($sheet->getCell('AO' . $r)->getValue()),
                'height' => $cleanInt($sheet->getCell('AP' . $r)->getValue()),
                'blood_type' => $cleanStr($sheet->getCell('AQ' . $r)->getValue()),
                'skin_color' => $formatTitleCase($cleanStr($sheet->getCell('AR' . $r)->getValue())),
                'hair_type' => $formatTitleCase($cleanStr($sheet->getCell('AS' . $r)->getValue())),
                'hair_color' => $formatTitleCase($cleanStr($sheet->getCell('AT' . $r)->getValue())),

                // Data Ayah
                'father_name' => $formatTitleCase($cleanStr($sheet->getCell('AU' . $r)->getValue()) ?? $cleanStr($sheet->getCell('AE' . $r)->getValue())),
                'father_birth_place' => $formatTitleCase($cleanStr($sheet->getCell('AV' . $r)->getValue())),
                'father_birth_date' => $parseDate($sheet->getCell('AW' . $r)->getValue()),
                'father_nik' => $cleanStr($sheet->getCell('AX' . $r)->getValue()),
                'father_address' => $cleanStr($sheet->getCell('AY' . $r)->getValue()),
                'father_religion' => $normalizeReligion($cleanStr($sheet->getCell('BH' . $r)->getValue())),
                'father_phone' => $cleanPhone($sheet->getCell('BI' . $r)->getValue()),
                'father_education' => $cleanStr($sheet->getCell('BJ' . $r)->getValue()),
                'father_job' => $formatTitleCase($cleanStr($sheet->getCell('BK' . $r)->getValue())),
                'father_company' => $cleanStr($sheet->getCell('BL' . $r)->getValue()),
                'father_company_address' => $cleanStr($sheet->getCell('BM' . $r)->getValue()),
                'father_company_phone' => $cleanPhone($sheet->getCell('BN' . $r)->getValue()),
                'father_income' => $cleanStr($sheet->getCell('BO' . $r)->getValue()),
                'father_email' => $cleanStr($sheet->getCell('BP' . $r)->getValue()),
                'father_social_media' => $cleanStr($sheet->getCell('BQ' . $r)->getValue()),

                // Data Ibu
                'mother_name' => $formatTitleCase($cleanStr($sheet->getCell('BR' . $r)->getValue()) ?? $cleanStr($sheet->getCell('AF' . $r)->getValue())),
                'mother_birth_place' => $formatTitleCase($cleanStr($sheet->getCell('BS' . $r)->getValue())),
                'mother_birth_date' => $parseDate($sheet->getCell('BT' . $r)->getValue()),
                'mother_nik' => $cleanStr($sheet->getCell('BU' . $r)->getValue()),
                'mother_address' => $cleanStr($sheet->getCell('BV' . $r)->getValue()),
                'mother_religion' => $normalizeReligion($cleanStr($sheet->getCell('CD' . $r)->getValue())),
                'mother_phone' => $cleanPhone($sheet->getCell('CE' . $r)->getValue()),
                'mother_education' => $cleanStr($sheet->getCell('CF' . $r)->getValue()),
                'mother_job' => $formatTitleCase($cleanStr($sheet->getCell('CG' . $r)->getValue())),
                'mother_company' => $cleanStr($sheet->getCell('CH' . $r)->getValue()),
                'mother_company_address' => $cleanStr($sheet->getCell('CI' . $r)->getValue()),
                'mother_company_phone' => $cleanPhone($sheet->getCell('CJ' . $r)->getValue()),
                'mother_income' => $cleanStr($sheet->getCell('CK' . $r)->getValue()),
                'mother_email' => $cleanStr($sheet->getCell('CL' . $r)->getValue()),
                'mother_social_media' => $cleanStr($sheet->getCell('CM' . $r)->getValue()),

                // Data Wali
                'guardian_name' => $formatTitleCase($cleanStr($sheet->getCell('CN' . $r)->getValue())),
                'guardian_birth_place' => $formatTitleCase($cleanStr($sheet->getCell('CO' . $r)->getValue())),
                'guardian_birth_date' => $parseDate($sheet->getCell('CP' . $r)->getValue()),
                'guardian_nik' => $cleanStr($sheet->getCell('CQ' . $r)->getValue()),
                'guardian_address' => $cleanStr($sheet->getCell('CR' . $r)->getValue()),
                'guardian_religion' => $normalizeReligion($cleanStr($sheet->getCell('CY' . $r)->getValue())),
                'guardian_phone' => $cleanPhone($sheet->getCell('CZ' . $r)->getValue()),
                'guardian_education' => $cleanStr($sheet->getCell('DA' . $r)->getValue()),
                'guardian_job' => $formatTitleCase($cleanStr($sheet->getCell('DB' . $r)->getValue())),
                'guardian_company' => $cleanStr($sheet->getCell('DC' . $r)->getValue()),
                'guardian_company_address' => $cleanStr($sheet->getCell('DD' . $r)->getValue()),
                'guardian_company_phone' => $cleanPhone($sheet->getCell('DE' . $r)->getValue()),
                'guardian_income' => $cleanStr($sheet->getCell('DF' . $r)->getValue()),
                'guardian_email' => $cleanStr($sheet->getCell('DG' . $r)->getValue()),
                'guardian_social_media' => $cleanStr($sheet->getCell('DH' . $r)->getValue()),

                // Asal Sekolah & Status Masuk
                'origin_category' => $cleanStr($sheet->getCell('C' . $r)->getValue()),
                'previous_school' => $formatTitleCase($cleanStr($sheet->getCell('D' . $r)->getValue())),
                'previous_school_address' => $cleanStr($sheet->getCell('E' . $r)->getValue()),
                'enrollment_date' => $parseDate($sheet->getCell('B' . $r)->getValue()) ?? now()->toDateString(),
                'enrollment_type' => 'import',
                'status' => 'aktif',
            ];

            $existing = Student::where('nis', $nis)->first();
            if ($existing) {
                $existing->update($studentData);
                $student = $existing;
                $updated++;
            } else {
                $student = Student::create($studentData);
                $imported++;
            }

            // Synchronize StudentClassroomHistory
            if ($student && $student->classroom_id) {
                $student->load(['classroom.classLevel', 'classroom.homeroomTeacher']);
                StudentClassroomHistory::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'classroom_id' => $student->classroom_id,
                        'classroom_name' => $student->classroom ? $student->classroom->name : null,
                        'grade_level' => $student->classroom && $student->classroom->classLevel ? $student->classroom->classLevel->name : 'Kelas 7',
                        'homeroom_teacher_name' => $student->classroom && $student->classroom->homeroomTeacher ? $student->classroom->homeroomTeacher->name : null,
                        'status' => 'aktif',
                        'start_date' => $student->enrollment_date ?? now()->toDateString(),
                        'notes' => 'Import Master Database Siswa SMP',
                    ]
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=========================================");
        $this->info(" IMPORT DATABASE SISWA SMP SELESAI");
        $this->info("=========================================");
        $this->table(
            ['Kategori', 'Jumlah'],
            [
                ['Siswa Baru Diimpor', $imported],
                ['Siswa Diperbarui', $updated],
                ['Total Siswa Terproses', $imported + $updated],
                ['Tahun Ajaran', $academicYear->name],
            ]
        );

        return 0;
    }
}
