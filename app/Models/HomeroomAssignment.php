<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeroomAssignment extends Model
{
    use HasFactory;

    protected $table = 'homeroom_assignments';

    protected $fillable = [
        'academic_year_id',
        'class_level_id',
        'classroom_id',
        'employee_id',
        'role', // wali_kelas, guru_mapel, gpk, gpq
        'subject',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classLevel(): BelongsTo
    {
        return $this->belongsTo(ClassLevel::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'wali_kelas' => 'Wali Kelas',
            'guru_mapel' => 'Guru Mapel' . ($this->subject ? " ({$this->subject})" : ''),
            'gpk' => 'Guru Pendamping Khusus (GPK)',
            'gpq' => "Guru Pendamping Al-Qur'an (GPQ)",
            default => ucfirst(str_replace('_', ' ', $this->role ?? 'Wali Kelas')),
        };
    }

    protected static function booted()
    {
        // When assigned as wali_kelas and is_active, sync homeroom_teacher_id on classroom
        static::saved(function ($assignment) {
            if ($assignment->is_active && $assignment->role === 'wali_kelas' && $assignment->classroom_id && $assignment->employee_id) {
                Classroom::where('id', $assignment->classroom_id)->update([
                    'homeroom_teacher_id' => $assignment->employee_id,
                ]);
            }
        });
    }
}
