<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Classroom;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentController extends Controller
{
    /**
     * Display a listing of students with filters.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? $academicYears->first();
        $classLevels = ClassLevel::orderBy('order_level', 'asc')->get();

        // Selected Academic Year defaults strictly to Active Academic Year (no "all" by default)
        $selectedYearId = $request->get('academic_year_id', $activeYear?->id);
        $selectedLevelId = $request->get('class_level_id');
        $selectedClassroomId = $request->get('classroom_id');
        $status = $request->get('status', 'aktif');
        $search = $request->get('search');

        // Classrooms available for filtering / assignment
        $classrooms = Classroom::with(['classLevel', 'academicYear'])
            ->when($selectedYearId, function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $query = Student::with(['academicYear', 'classLevel', 'classroom', 'spmbCandidate']);

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        if ($selectedLevelId) {
            $query->where('class_level_id', $selectedLevelId);
        }

        if ($selectedClassroomId) {
            $query->where('classroom_id', $selectedClassroomId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('nis', 'asc')->paginate(15)->withQueryString();

        // Stats calculation based on active period
        $statsBaseQuery = Student::query();
        if ($selectedYearId) {
            $statsBaseQuery->where('academic_year_id', $selectedYearId);
        }

        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'active' => (clone $statsBaseQuery)->where('status', 'aktif')->count(),
            'spmb_enrolled' => (clone $statsBaseQuery)->where('enrollment_type', 'spmb')->count(),
            'male' => (clone $statsBaseQuery)->where('gender', 'L')->count(),
            'female' => (clone $statsBaseQuery)->where('gender', 'P')->count(),
        ];

        return view('admin.students.index', compact(
            'students',
            'academicYears',
            'activeYear',
            'classLevels',
            'classrooms',
            'selectedYearId',
            'selectedLevelId',
            'selectedClassroomId',
            'status',
            'search',
            'stats'
        ));
    }

    /**
     * Show single student detail (JSON).
     */
    public function show($id): JsonResponse
    {
        $student = Student::with(['academicYear', 'classLevel', 'classroom', 'spmbCandidate'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'student' => $student,
        ]);
    }

    /**
     * Store new student (manual entry).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:30|unique:students,nis',
            'nisn' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:30',
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_level_id' => 'required|exists:class_levels,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'address' => 'nullable|string',
            'parent_phone' => 'nullable|string|max:30',
            'parent_email' => 'nullable|email|max:100',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:30',
            'father_job' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:30',
            'mother_job' => 'nullable|string|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:30',
            'previous_school' => 'nullable|string|max:255',
            'enrollment_date' => 'nullable|date',
            'enrollment_type' => 'required|in:spmb,mutasi,manual,import',
            'status' => 'required|in:aktif,lulus,mutasi_keluar,drop_out,non_aktif',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['enrollment_date'])) {
            $validated['enrollment_date'] = now()->toDateString();
        }

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Data siswa {$student->full_name} berhasil ditambahkan.",
            'student' => $student->load(['academicYear', 'classLevel', 'classroom']),
        ]);
    }

    /**
     * Update existing student.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'nis' => 'required|string|max:30|unique:students,nis,' . $student->id,
            'nisn' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:30',
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_level_id' => 'required|exists:class_levels,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'address' => 'nullable|string',
            'parent_phone' => 'nullable|string|max:30',
            'parent_email' => 'nullable|email|max:100',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:30',
            'father_job' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:30',
            'mother_job' => 'nullable|string|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:30',
            'previous_school' => 'nullable|string|max:255',
            'enrollment_date' => 'nullable|date',
            'enrollment_type' => 'required|in:spmb,mutasi,manual,import',
            'status' => 'required|in:aktif,lulus,mutasi_keluar,drop_out,non_aktif',
            'notes' => 'nullable|string',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Data siswa {$student->full_name} berhasil diperbarui.",
            'student' => $student->load(['academicYear', 'classLevel', 'classroom']),
        ]);
    }

    /**
     * Delete student.
     */
    public function destroy($id): JsonResponse
    {
        $student = Student::findOrFail($id);

        // Unlink SPMB candidate if linked
        if ($student->spmbCandidate) {
            $student->spmbCandidate->update([
                'is_enrolled' => false,
                'enrolled_at' => null,
                'student_id' => null,
            ]);
        }

        $name = $student->full_name;
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => "Data siswa {$name} berhasil dihapus.",
        ]);
    }

    /**
     * Download Excel Import Template for Bulk Continuing Students.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        // Headers
        $headers = [
            'A1' => 'NIS (*Wajib Unik)',
            'B1' => 'Nama Lengkap (*Wajib)',
            'C1' => 'Nama Panggilan',
            'D1' => 'Jenis Kelamin (L/P)',
            'E1' => 'Tingkat Kelas (misal: 7 / 8 / 9)',
            'F1' => 'Nama Rombel (misal: 7-A / 8-B / 9-A)',
            'G1' => 'NISN',
            'H1' => 'NIK',
            'I1' => 'Tempat Lahir',
            'J1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'K1' => 'Agama',
            'L1' => 'Alamat Lengkap',
            'M1' => 'No HP / WhatsApp Ortu',
            'N1' => 'Nama Ayah',
            'O1' => 'Pekerjaan Ayah',
            'P1' => 'No HP Ayah',
            'Q1' => 'Nama Ibu',
            'Pekerjaan Ibu' => 'Pekerjaan Ibu',
            'No HP Ibu' => 'No HP Ibu',
            'Asal Sekolah' => 'Asal Sekolah',
            'Status Siswa (aktif/lulus/mutasi_keluar)' => 'Status Siswa'
        ];

        $columnLetters = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'
        ];

        $headerTitles = [
            'NIS (*Wajib Unik)',
            'Nama Lengkap (*Wajib)',
            'Nama Panggilan',
            'Jenis Kelamin (L/P)',
            'Tingkat Kelas (misal: 7 / 8 / 9)',
            'Nama Rombel (misal: 7-A / 8-B / 9-A)',
            'NISN',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Agama',
            'Alamat Lengkap',
            'No HP / WA Ortu',
            'Nama Ayah',
            'Pekerjaan Ayah',
            'No HP Ayah',
            'Nama Ibu',
            'Pekerjaan Ibu',
            'No HP Ibu',
            'Asal Sekolah',
            'Status (aktif/lulus/mutasi_keluar)'
        ];

        foreach ($headerTitles as $idx => $title) {
            $col = $columnLetters[$idx];
            $sheet->setCellValue("{$col}1", $title);
        }

        // Header Styling
        $headerRange = 'A1:U1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('047857'); // Emerald-700
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Sample Data Rows (SMP: Kelas 7, 8, 9)
        $sampleData = [
            ['26.SMP.001', 'Ahmad Farhan Kamil', 'Farhan', 'L', '8', '8-A', '0123456789', '3573010101120001', 'Malang', '2012-05-14', 'Islam', 'Jl. Danau Sentani No. 12, Sawojajar, Malang', '081234567890', 'Bambang Sudarsono', 'PNS', '081234567890', 'Siti Rahayu', 'Guru', '081234567891', 'SD Anak Saleh', 'aktif'],
            ['26.SMP.002', 'Aisyah Putri Azzahra', 'Aisyah', 'P', '8', '8-A', '0123456790', '3573014502120002', 'Surabaya', '2012-08-20', 'Islam', 'Jl. Ijen No. 45, Malang', '081398765432', 'Agus Setiawan', 'Wiraswasta', '081398765432', 'Nur Laila', 'Ibu Rumah Tangga', '081398765433', 'SD Anak Saleh', 'aktif'],
            ['25.SMP.001', 'Bagas Aditya Pratama', 'Bagas', 'L', '9', '9-B', '0112345678', '3573021103110001', 'Malang', '2011-03-11', 'Islam', 'Jl. Soekarno Hatta No. 88, Malang', '081255544433', 'Hadi Pranoto', 'Karyawan BUMN', '081255544433', 'Dewi Sartika', 'Dosen', '081255544434', 'SDN 1 Malang', 'aktif'],
        ];

        $rowNum = 2;
        foreach ($sampleData as $row) {
            foreach ($row as $idx => $val) {
                $col = $columnLetters[$idx];
                $sheet->setCellValueExplicit("{$col}{$rowNum}", $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $rowNum++;
        }

        // Auto size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Siswa_SMP.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Excel Data for Bulk Students.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            if (count($rows) <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel kosong atau hanya berisi baris header.',
                ], 422);
            }

            $imported = 0;
            $updated = 0;
            $errors = [];

            // Cache levels and classrooms for quick lookup
            $classLevels = ClassLevel::all()->keyBy(function ($item) {
                return strtolower(trim(str_replace(['kelas', 'level', ' '], '', $item->code ?? $item->name)));
            });

            // Iterate rows starting from index 1 (skipping header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nis = trim($row[0] ?? '');
                $fullName = trim($row[1] ?? '');

                if (empty($nis) || empty($fullName)) {
                    continue; // Skip empty rows
                }

                $nickname = trim($row[2] ?? '') ?: null;
                $genderRaw = strtoupper(trim($row[3] ?? 'L'));
                $gender = str_starts_with($genderRaw, 'P') ? 'P' : 'L';
                $levelInput = trim($row[4] ?? '7');
                $rombelInput = trim($row[5] ?? '7-A');
                $nisn = trim($row[6] ?? '') ?: null;
                $nik = trim($row[7] ?? '') ?: null;
                $birthPlace = trim($row[8] ?? '') ?: null;
                $birthDateRaw = trim($row[9] ?? '');
                $religion = trim($row[10] ?? 'Islam') ?: 'Islam';
                $address = trim($row[11] ?? '') ?: null;
                $parentPhone = trim($row[12] ?? '') ?: null;
                $fatherName = trim($row[13] ?? '') ?: null;
                $fatherJob = trim($row[14] ?? '') ?: null;
                $fatherPhone = trim($row[15] ?? '') ?: null;
                $motherName = trim($row[16] ?? '') ?: null;
                $motherJob = trim($row[17] ?? '') ?: null;
                $motherPhone = trim($row[18] ?? '') ?: null;
                $previousSchool = trim($row[19] ?? '') ?: null;
                $statusInput = strtolower(trim($row[20] ?? 'aktif'));

                $validStatuses = ['aktif', 'lulus', 'mutasi_keluar', 'drop_out', 'non_aktif'];
                $status = in_array($statusInput, $validStatuses) ? $statusInput : 'aktif';

                // Parse Birth Date safely
                $birthDate = null;
                if (!empty($birthDateRaw)) {
                    try {
                        $birthDate = Carbon::parse($birthDateRaw)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $birthDate = null;
                    }
                }

                // Match or create Class Level
                $levelKey = strtolower(trim(str_replace(['kelas', 'level', ' '], '', $levelInput)));
                $classLevel = $classLevels->get($levelKey);
                if (!$classLevel) {
                    $cleanCode = preg_replace('/[^0-9]/', '', $levelInput) ?: $levelInput;
                    $classLevel = ClassLevel::firstOrCreate(
                        ['code' => $cleanCode],
                        [
                            'name' => "Kelas {$cleanCode}",
                            'order_level' => (int) $cleanCode ?: 1,
                            'description' => "Tingkat Kelas {$cleanCode}",
                        ]
                    );
                    $classLevels->put($levelKey, $classLevel);
                }

                // Match or create Classroom
                $classroom = Classroom::firstOrCreate(
                    [
                        'academic_year_id' => $academicYear->id,
                        'class_level_id' => $classLevel->id,
                        'name' => $rombelInput,
                    ],
                    [
                        'room_number' => "Ruang {$rombelInput}",
                        'capacity' => 32,
                        'is_active' => true,
                        'description' => "Rombel {$rombelInput} TA {$academicYear->name}",
                    ]
                );

                // Upsert Student by NIS
                $student = Student::where('nis', $nis)->first();

                $studentData = [
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nik' => $nik,
                    'full_name' => $fullName,
                    'nickname' => $nickname,
                    'gender' => $gender,
                    'birth_place' => $birthPlace,
                    'birth_date' => $birthDate,
                    'religion' => $religion,
                    'academic_year_id' => $academicYear->id,
                    'class_level_id' => $classLevel->id,
                    'classroom_id' => $classroom->id,
                    'address' => $address,
                    'parent_phone' => $parentPhone,
                    'father_name' => $fatherName,
                    'father_job' => $fatherJob,
                    'father_phone' => $fatherPhone,
                    'mother_name' => $motherName,
                    'mother_job' => $motherJob,
                    'mother_phone' => $motherPhone,
                    'previous_school' => $previousSchool,
                    'status' => $status,
                    'enrollment_type' => 'import',
                    'enrollment_date' => $academicYear->start_date ?? now()->toDateString(),
                ];

                if ($student) {
                    $student->update($studentData);
                    $updated++;
                } else {
                    Student::create($studentData);
                    $imported++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Import selesai! {$imported} data baru berhasil ditambahkan, {$updated} data diperbarui ke Tahun Ajaran {$academicYear->name}.",
                'imported_count' => $imported,
                'updated_count' => $updated,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
