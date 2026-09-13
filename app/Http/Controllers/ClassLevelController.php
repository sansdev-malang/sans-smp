<?php

namespace App\Http\Controllers;

use App\Models\ClassLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassLevelController extends Controller
{
    /**
     * Display a listing of class levels with stats.
     */
    public function index(Request $request)
    {
        $classLevels = ClassLevel::withCount(['classrooms', 'students'])
            ->orderBy('order_level', 'asc')
            ->get();

        $stats = [
            'total_levels' => $classLevels->count(),
            'total_classrooms' => $classLevels->sum('classrooms_count'),
            'total_students' => $classLevels->sum('students_count'),
        ];

        return view('admin.class-levels.index', compact('classLevels', 'stats'));
    }

    /**
     * Show single level (JSON).
     */
    public function show($id): JsonResponse
    {
        $classLevel = ClassLevel::withCount(['classrooms', 'students'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'class_level' => $classLevel,
        ]);
    }

    /**
     * Store a newly created class level.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:class_levels,code',
            'order_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $classLevel = ClassLevel::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Tingkat {$classLevel->name} berhasil ditambahkan.",
            'class_level' => $classLevel,
        ]);
    }

    /**
     * Update class level.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $classLevel = ClassLevel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:class_levels,code,' . $classLevel->id,
            'order_level' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $classLevel->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Tingkat {$classLevel->name} berhasil diperbarui.",
            'class_level' => $classLevel,
        ]);
    }

    /**
     * Delete class level.
     */
    public function destroy($id): JsonResponse
    {
        $classLevel = ClassLevel::findOrFail($id);

        $classroomsCount = $classLevel->classrooms()->count();
        $studentsCount = $classLevel->students()->count();

        if ($classroomsCount > 0 || $studentsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tingkat kelas tidak dapat dihapus karena masih digunakan oleh {$classroomsCount} rombel dan {$studentsCount} siswa.",
            ], 422);
        }

        $name = $classLevel->name;
        $classLevel->delete();

        return response()->json([
            'success' => true,
            'message' => "Tingkat {$name} berhasil dihapus.",
        ]);
    }
}
