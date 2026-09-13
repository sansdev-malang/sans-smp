<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Classroom;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    /**
     * Display a listing of classrooms with filtering by Academic Year & Level.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? $academicYears->first();
        $classLevels = ClassLevel::orderBy('order_level', 'asc')->get();

        // Teachers for Homeroom selection
        $teachers = Employee::orderBy('name', 'asc')->get();

        $selectedYearId = $request->get('academic_year_id', $activeYear?->id);
        $selectedLevelId = $request->get('class_level_id');
        $search = $request->get('search');

        $query = Classroom::with(['academicYear', 'classLevel', 'homeroomTeacher'])
            ->withCount([
                'students as active_students_count' => function ($q) {
                    $q->where('status', 'aktif');
                },
                'students as total_students_count'
            ]);

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        if ($selectedLevelId) {
            $query->where('class_level_id', $selectedLevelId);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $classrooms = $query->orderBy('academic_year_id', 'desc')
            ->orderBy('class_level_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $totalCap = $classrooms->sum('capacity');
        $totalEnrolled = $classrooms->sum('active_students_count');
        $occupancyRate = $totalCap > 0 ? round(($totalEnrolled / $totalCap) * 100) : 0;

        $stats = [
            'total_classrooms' => $classrooms->count(),
            'total_capacity' => $totalCap,
            'total_students' => $totalEnrolled,
            'total_enrolled' => $totalEnrolled,
            'occupancy_rate' => $occupancyRate,
            'active_year_name' => $selectedYear?->name ?? 'Semua Periode',
        ];

        return view('admin.classrooms.index', compact(
            'classrooms',
            'academicYears',
            'activeYear',
            'classLevels',
            'teachers',
            'selectedYearId',
            'selectedLevelId',
            'search',
            'stats'
        ));
    }

    /**
     * Show single classroom detail (JSON).
     */
    public function show($id): JsonResponse
    {
        $classroom = Classroom::with(['academicYear', 'classLevel', 'homeroomTeacher'])
            ->withCount('students')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'classroom' => $classroom,
        ]);
    }

    /**
     * Get students in a classroom (JSON).
     */
    public function students($id): JsonResponse
    {
        $classroom = Classroom::with(['academicYear', 'classLevel', 'homeroomTeacher'])->findOrFail($id);
        $students = $classroom->students()->where('status', 'aktif')->orderBy('nis', 'asc')->get();

        return response()->json([
            'success' => true,
            'classroom' => $classroom,
            'students' => $students,
        ]);
    }

    /**
     * Store newly created classroom.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_level_id' => 'required|exists:class_levels,id',
            'name' => 'required|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'homeroom_teacher_id' => 'nullable|exists:employees,id',
            'capacity' => 'required|integer|min:1|max:100',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Check unique per academic year & level
        $exists = Classroom::where('academic_year_id', $validated['academic_year_id'])
            ->where('class_level_id', $validated['class_level_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Rombel {$validated['name']} sudah ada pada tingkat & tahun ajaran tersebut.",
            ], 422);
        }

        $classroom = Classroom::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Rombel {$classroom->name} berhasil ditambahkan.",
            'classroom' => $classroom->load(['academicYear', 'classLevel', 'homeroomTeacher']),
        ]);
    }

    /**
     * Update existing classroom.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $classroom = Classroom::findOrFail($id);

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_level_id' => 'required|exists:class_levels,id',
            'name' => 'required|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'homeroom_teacher_id' => 'nullable|exists:employees,id',
            'capacity' => 'required|integer|min:1|max:100',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $exists = Classroom::where('academic_year_id', $validated['academic_year_id'])
            ->where('class_level_id', $validated['class_level_id'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $classroom->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Rombel {$validated['name']} sudah terdaftar untuk tingkat & periode ini.",
            ], 422);
        }

        $classroom->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Rombel {$classroom->name} berhasil diperbarui.",
            'classroom' => $classroom->load(['academicYear', 'classLevel', 'homeroomTeacher']),
        ]);
    }

    /**
     * Delete classroom.
     */
    public function destroy($id): JsonResponse
    {
        $classroom = Classroom::findOrFail($id);

        $studentsCount = $classroom->students()->count();
        if ($studentsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Rombel {$classroom->name} tidak dapat dihapus karena memiliki {$studentsCount} siswa terdaftar.",
            ], 422);
        }

        $name = $classroom->name;
        $classroom->delete();

        return response()->json([
            'success' => true,
            'message' => "Rombel {$name} berhasil dihapus.",
        ]);
    }
}
