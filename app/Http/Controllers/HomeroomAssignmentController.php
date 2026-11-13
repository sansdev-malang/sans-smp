<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\HomeroomAssignment;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeroomAssignmentController extends Controller
{
    /**
     * Display a listing of teacher assignments (Wali Kelas, Guru Mapel, GPK, GPQ) with filters.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $activeAcademicYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        $selectedYearId = $request->get('academic_year_id', $activeAcademicYear?->id);
        if (!$selectedYearId || $selectedYearId === 'all') {
            $selectedYearId = $activeAcademicYear?->id;
        }

        $classLevels = ClassLevel::orderBy('order')->get();
        $selectedClassLevelId = $request->get('class_level_id', 'all');
        $selectedSubjectId = $request->get('subject_id', 'all');
        $selectedRole = $request->get('role', 'all');
        $selectedStatus = $request->get('status', 'all');
        $search = $request->get('search');

        $query = HomeroomAssignment::with([
            'academicYear',
            'classLevel',
            'classroom' => function ($cq) {
                $cq->withCount(['students' => function ($sq) {
                    $sq->where('status', 'aktif');
                }]);
            },
            'subject',
            'teacher.user'
        ]);

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        if ($selectedClassLevelId && $selectedClassLevelId !== 'all') {
            $query->where(function ($q) use ($selectedClassLevelId) {
                $q->where('class_level_id', $selectedClassLevelId)
                  ->orWhereHas('classroom', function ($cq) use ($selectedClassLevelId) {
                      $cq->where('class_level_id', $selectedClassLevelId);
                  });
            });
        }

        if ($selectedSubjectId && $selectedSubjectId !== 'all') {
            $query->where('subject_id', $selectedSubjectId);
        }

        if ($selectedRole && $selectedRole !== 'all') {
            $query->where('role', $selectedRole);
        }

        if ($selectedStatus && $selectedStatus !== 'all') {
            $query->where('is_active', $selectedStatus === 'active' || $selectedStatus === '1');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', function ($tq) use ($search) {
                    $tq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('nuptk', 'like', "%{$search}%")
                       ->orWhere('niy', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('classroom', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                })->orWhereHas('subject', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                })->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $perPageCount = ($perPage === 'all' || $perPage == 99999 || $perPage === '99999') ? 99999 : (int) $perPage;
        if ($perPageCount <= 0) {
            $perPageCount = 15;
        }

        $assignments = $query->orderBy('academic_year_id', 'desc')
            ->orderBy('class_level_id', 'asc')
            ->orderBy('classroom_id', 'asc')
            ->orderBy('role', 'asc')
            ->paginate($perPageCount)
            ->withQueryString();

        $classrooms = Classroom::with('classLevel')->where('is_active', true)->orderBy('code')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('order')->get();

        // Teachers (Active employees)
        $teachers = Employee::whereIn('status', ['Active', 'aktif', 'active', 'Aktif'])
            ->orderBy('name')
            ->get();

        $stats = [
            'total_assignments' => HomeroomAssignment::where('academic_year_id', $selectedYearId)->count(),
            'total_wali_kelas' => HomeroomAssignment::where('academic_year_id', $selectedYearId)->where('role', 'wali_kelas')->where('is_active', true)->count(),
            'total_guru_mapel' => HomeroomAssignment::where('academic_year_id', $selectedYearId)->where('role', 'guru_mapel')->where('is_active', true)->count(),
            'total_gpk' => HomeroomAssignment::where('academic_year_id', $selectedYearId)->where('role', 'gpk')->where('is_active', true)->count(),
            'total_gpq' => HomeroomAssignment::where('academic_year_id', $selectedYearId)->where('role', 'gpq')->where('is_active', true)->count(),
            'total_classrooms' => Classroom::where('is_active', true)->count(),
        ];

        return view('admin.homeroom-assignments.index', compact(
            'assignments',
            'academicYears',
            'activeAcademicYear',
            'selectedYearId',
            'selectedClassLevelId',
            'selectedSubjectId',
            'selectedRole',
            'selectedStatus',
            'classLevels',
            'classrooms',
            'subjects',
            'teachers',
            'stats',
            'search',
            'perPage'
        ));
    }

    /**
     * Store a newly created teacher assignment.
     */
    public function store(Request $request)
    {
        // Sanitize nullable fields
        foreach (['class_level_id', 'classroom_id', 'subject_id', 'semester', 'notes', 'subject'] as $field) {
            if ($request->has($field) && ($request->input($field) === '' || ($field === 'classroom_id' && $request->input($field) === 'all'))) {
                $request->merge([$field => null]);
            }
        }
        if ($request->has('is_active')) {
            $val = $request->input('is_active');
            $request->merge([
                'is_active' => ($val === '1' || $val === 1 || $val === true || $val === 'true' || $val === 'Aktif') ? 1 : 0
            ]);
        }

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'employee_id' => 'required|exists:employees,id',
            'role' => 'required|in:wali_kelas,guru_mapel,gpk,gpq',
            'subject' => 'nullable|string|max:100',
            'semester' => 'nullable|string|in:all,ganjil,genap',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $classroom = Classroom::findOrFail($validated['classroom_id']);
        $validated['class_level_id'] = $classroom->class_level_id;

        // Auto-link GPQ subject if role is gpq and subject_id not specified
        if ($validated['role'] === 'gpq' && empty($validated['subject_id'])) {
            $gpqSub = Subject::where('code', 'GPQ')->first();
            if ($gpqSub) {
                $validated['subject_id'] = $gpqSub->id;
                $validated['subject'] = $gpqSub->name;
            }
        }

        // Auto fill subject name if subject_id provided
        if (!empty($validated['subject_id']) && empty($validated['subject'])) {
            $validated['subject'] = Subject::find($validated['subject_id'])?->name;
        }

        // Check if duplicate assignment exists
        $existing = HomeroomAssignment::where('academic_year_id', $validated['academic_year_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->where('employee_id', $validated['employee_id'])
            ->where('role', $validated['role'])
            ->when($validated['role'] === 'guru_mapel' && !empty($validated['subject_id']), function($q) use ($validated) {
                $q->where('subject_id', $validated['subject_id']);
            })
            ->first();

        if ($existing) {
            $existing->update([
                'class_level_id' => $validated['class_level_id'],
                'subject_id' => $validated['subject_id'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'semester' => $validated['semester'] ?? 'all',
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'],
            ]);
            $assignment = $existing;
        } else {
            $assignment = HomeroomAssignment::create($validated);
        }

        // If wali_kelas and is_active, sync homeroom_teacher_id on classroom
        if ($assignment->is_active && $assignment->role === 'wali_kelas') {
            HomeroomAssignment::where('academic_year_id', $assignment->academic_year_id)
                ->where('classroom_id', $assignment->classroom_id)
                ->where('role', 'wali_kelas')
                ->where('id', '!=', $assignment->id)
                ->update(['is_active' => false]);

            $classroom->update(['homeroom_teacher_id' => $assignment->employee_id]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penugasan pendidik berhasil disimpan.',
                'data' => $assignment,
            ]);
        }

        return redirect()->route('homeroom-assignments.index', [
            'academic_year_id' => $assignment->academic_year_id,
        ])->with('success', 'Penugasan pendidik berhasil disimpan.');
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, $id)
    {
        $assignment = HomeroomAssignment::findOrFail($id);

        // Sanitize nullable fields
        foreach (['class_level_id', 'classroom_id', 'subject_id', 'semester', 'notes', 'subject'] as $field) {
            if ($request->has($field) && ($request->input($field) === '' || ($field === 'classroom_id' && $request->input($field) === 'all'))) {
                $request->merge([$field => null]);
            }
        }
        if ($request->has('is_active')) {
            $val = $request->input('is_active');
            $request->merge([
                'is_active' => ($val === '1' || $val === 1 || $val === true || $val === 'true' || $val === 'Aktif') ? 1 : 0
            ]);
        }

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'employee_id' => 'required|exists:employees,id',
            'role' => 'required|in:wali_kelas,guru_mapel,gpk,gpq',
            'subject' => 'nullable|string|max:100',
            'semester' => 'nullable|string|in:all,ganjil,genap',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $classroom = Classroom::findOrFail($validated['classroom_id']);
        $validated['class_level_id'] = $classroom->class_level_id;

        // Auto-link GPQ subject if role is gpq and subject_id not specified
        if ($validated['role'] === 'gpq' && empty($validated['subject_id'])) {
            $gpqSub = Subject::where('code', 'GPQ')->first();
            if ($gpqSub) {
                $validated['subject_id'] = $gpqSub->id;
                $validated['subject'] = $gpqSub->name;
            }
        }

        if (!empty($validated['subject_id']) && empty($validated['subject'])) {
            $validated['subject'] = Subject::find($validated['subject_id'])?->name;
        }

        $assignment->update($validated);

        if ($assignment->role === 'wali_kelas') {
            if ($assignment->is_active) {
                HomeroomAssignment::where('academic_year_id', $assignment->academic_year_id)
                    ->where('classroom_id', $assignment->classroom_id)
                    ->where('role', 'wali_kelas')
                    ->where('id', '!=', $assignment->id)
                    ->update(['is_active' => false]);

                $classroom->update(['homeroom_teacher_id' => $assignment->employee_id]);
            } else {
                if ($classroom->homeroom_teacher_id == $assignment->employee_id) {
                    $classroom->update(['homeroom_teacher_id' => null]);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penugasan pendidik berhasil diperbarui.',
                'data' => $assignment,
            ]);
        }

        return redirect()->route('homeroom-assignments.index', [
            'academic_year_id' => $assignment->academic_year_id,
        ])->with('success', 'Penugasan pendidik berhasil diperbarui.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $assignment = HomeroomAssignment::findOrFail($id);
        $assignment->is_active = !$assignment->is_active;
        $assignment->save();

        if ($assignment->role === 'wali_kelas') {
            $classroom = Classroom::find($assignment->classroom_id);
            if ($classroom) {
                if ($assignment->is_active) {
                    HomeroomAssignment::where('academic_year_id', $assignment->academic_year_id)
                        ->where('classroom_id', $assignment->classroom_id)
                        ->where('role', 'wali_kelas')
                        ->where('id', '!=', $assignment->id)
                        ->update(['is_active' => false]);

                    $classroom->update(['homeroom_teacher_id' => $assignment->employee_id]);
                } else {
                    if ($classroom->homeroom_teacher_id == $assignment->employee_id) {
                        $classroom->update(['homeroom_teacher_id' => null]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'is_active' => $assignment->is_active,
            'message' => 'Status penugasan berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Request $request, $id)
    {
        $assignment = HomeroomAssignment::findOrFail($id);
        $classroom = Classroom::find($assignment->classroom_id);

        if ($assignment->role === 'wali_kelas' && $classroom && $classroom->homeroom_teacher_id == $assignment->employee_id) {
            $classroom->update(['homeroom_teacher_id' => null]);
        }

        $assignment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penugasan pendidik berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Penugasan pendidik berhasil dihapus.');
    }

    /**
     * Synchronize wali kelas assignments from existing active classrooms.
     */
    public function syncFromClassrooms(Request $request)
    {
        $selectedYearId = $request->get('academic_year_id');
        $activeTapel = AcademicYear::where('is_active', true)->first();
        $targetYearId = $selectedYearId ?: ($activeTapel?->id ?? null);

        $query = Classroom::whereNotNull('homeroom_teacher_id');
        if ($targetYearId && $targetYearId !== 'all') {
            $query->where('academic_year_id', $targetYearId);
        }

        $classrooms = $query->get();
        $syncedCount = 0;

        foreach ($classrooms as $c) {
            $assignment = HomeroomAssignment::updateOrCreate(
                [
                    'academic_year_id' => $c->academic_year_id,
                    'classroom_id' => $c->id,
                    'role' => 'wali_kelas',
                ],
                [
                    'class_level_id' => $c->class_level_id,
                    'employee_id' => $c->homeroom_teacher_id,
                    'is_active' => true,
                    'semester' => 'all',
                ]
            );

            // Deactivate any other wali_kelas assignment for this room
            HomeroomAssignment::where('academic_year_id', $c->academic_year_id)
                ->where('classroom_id', $c->id)
                ->where('role', 'wali_kelas')
                ->where('id', '!=', $assignment->id)
                ->update(['is_active' => false]);

            $syncedCount++;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil menyinkronkan {$syncedCount} penugasan wali kelas dari data rombel.",
                'count' => $syncedCount,
            ]);
        }

        return redirect()->back()->with('success', "Berhasil menyinkronkan {$syncedCount} penugasan wali kelas dari data rombel.");
    }
}
