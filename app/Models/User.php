<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'employee_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Helper untuk cek role
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }
        return in_array($this->role, $roles);
    }

    /**
     * Check if user has an admin-level role.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin_sd', 'admin_paud', 'admin_smp', 'kepala_sekolah', 'waka']);
    }

    /**
     * Check if user is executive leadership (Kepala Sekolah or Waka).
     */
    public function isExecutive(): bool
    {
        return in_array($this->role, ['kepala_sekolah', 'waka']);
    }

    /**
     * Check if the user is currently assigned as an active homeroom teacher in the active academic year.
     */
    public function isActiveHomeroomTeacher(): bool
    {
        if (!$this->employee_id) {
            return false;
        }

        return \Illuminate\Support\Facades\Cache::remember('user_is_active_homeroom_' . $this->employee_id, 60, function () {
            $activeAy = \App\Models\AcademicYear::where('is_active', true)->first();
            $query = \App\Models\Classroom::where('homeroom_teacher_id', $this->employee_id);
            if ($activeAy) {
                $query->where('academic_year_id', $activeAy->id);
            }
            return $query->exists();
        });
    }

    /**
     * Check if the user is a teacher / tenaga pendidik (Semua Guru).
     */
    public function isTeacher(): bool
    {
        if (in_array($this->role, ['guru', 'wali_kelas'])) {
            return true;
        }

        if (!$this->employee_id) {
            return false;
        }

        return \Illuminate\Support\Facades\Cache::remember('user_is_teacher_' . $this->employee_id, 300, function () {
            $employee = $this->employee;
            if (!$employee) {
                return false;
            }

            if ($employee->employee_type_id == 1) {
                return true;
            }

            if ($employee->relationLoaded('employeeType') ? ($employee->employeeType?->code === 'teacher') : ($employee->employeeType()->where('code', 'teacher')->exists())) {
                return true;
            }

            $pos = strtolower($employee->position ?? '');
            return str_contains($pos, 'guru') || in_array(strtoupper(trim($employee->position ?? '')), ['GPK', 'GPQ']);
        });
    }

    /**
     * Check if the user can access E-Rapor (Super Admin, Kepala Sekolah, Admin, or Active Homeroom Teachers).
     */
    public function canAccessRapor(): bool
    {
        if (in_array($this->role, ['super_admin', 'superadmin', 'kepala_sekolah', 'admin_paud', 'admin_sd', 'admin_smp', 'waka', 'wali_kelas'])) {
            return true;
        }

        if ($this->isTeacher()) {
            return true;
        }

        return $this->isActiveHomeroomTeacher();
    }

    /**
     * Check if the user can access CBT (Super Admin, Kepala Sekolah, Admin, Homeroom Teachers, or Any Teacher).
     */
    public function canAccessCbt(): bool
    {
        if (in_array($this->role, ['super_admin', 'superadmin', 'kepala_sekolah', 'admin_paud', 'admin_sd', 'admin_smp', 'waka', 'wali_kelas', 'guru'])) {
            return true;
        }

        return $this->canAccessRapor() || $this->isTeacher();
    }

    /**
     * Check if user can access Data Rapor Mengaji.
     * Allowed: Admin, Kepala Sekolah, Waka, Koordinator GPQ, Wali Kelas aktif, dan Guru GPQ.
     */
    public function canAccessRaporMengaji(): bool
    {
        if ($this->isAdmin() || $this->isExecutive() || in_array($this->role, ['super_admin', 'superadmin', 'admin_sd', 'admin_smp', 'admin_paud', 'kepala_sekolah', 'waka'])) {
            return true;
        }

        if ($this->isActiveHomeroomTeacher() || $this->role === 'wali_kelas') {
            return true;
        }

        return $this->isGpqTeacher();
    }

    /**
     * Check if user is a GPQ Teacher or GPQ Coordinator.
     */
    public function isGpqTeacher(): bool
    {
        if (!$this->employee_id) {
            return false;
        }

        $emp = $this->employee;
        if ($emp) {
            $pos = strtolower($emp->position ?? '');
            if (str_contains($pos, 'gpq') || str_contains($pos, 'mengaji') || str_contains($pos, 'al-quran') || str_contains($pos, 'al quran')) {
                return true;
            }
        }

        return \Illuminate\Support\Facades\Cache::remember('user_is_gpq_smp_' . $this->employee_id, 120, function () use ($emp) {
            try {
                $hasGroup = \Illuminate\Support\Facades\DB::connection('sans_rapor')
                    ->table('gpq_group_assignments')
                    ->where('unit', 'smp')
                    ->where(function ($q) use ($emp) {
                        $q->where('teacher_employee_id', $this->employee_id);
                        if ($emp && $emp->name) {
                            $cleanName = trim(explode(',', $emp->name)[0]);
                            $q->orWhere('teacher_name', 'like', "%{$cleanName}%");
                        }
                    })
                    ->exists();
                if ($hasGroup) return true;

                $isCoord = \Illuminate\Support\Facades\DB::connection('sans_rapor')
                    ->table('unit_coordinators')
                    ->where('unit', 'smp')
                    ->where('type', 'gpq')
                    ->where(function ($q) use ($emp) {
                        $q->where('teacher_employee_id', $this->employee_id)
                          ->orWhere('user_id', $this->id);
                        if ($emp && $emp->name) {
                            $cleanName = trim(explode(',', $emp->name)[0]);
                            $q->orWhere('teacher_name', 'like', "%{$cleanName}%");
                        }
                    })
                    ->exists();
                return $isCoord;
            } catch (\Throwable $e) {
                return false;
            }
        });
    }

    /**
     * Get the employee profile associated with the user.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->employee_id && ($user->isDirty('name') || $user->isDirty('email'))) {
                $employee = \App\Models\Employee::find($user->employee_id);
                if ($employee) {
                    $rawName = $user->name;
                    $front = $employee->front_title;
                    $back = $employee->back_title;
                    \App\Models\Employee::sanitizeTitlesAndName($front, $rawName, $back);

                    $employee->updateQuietly([
                        'front_title' => $front ?: null,
                        'name' => $rawName,
                        'back_title' => $back ?: null,
                        'email' => $user->email,
                    ]);
                }
            }
        });
    }
}
