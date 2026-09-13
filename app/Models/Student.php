<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nisn',
        'nik',
        'full_name',
        'nickname',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'academic_year_id',
        'class_level_id',
        'classroom_id',
        'address',
        'parent_phone',
        'parent_email',
        'father_name',
        'father_phone',
        'father_job',
        'mother_name',
        'mother_phone',
        'mother_job',
        'guardian_name',
        'guardian_phone',
        'previous_school',
        'enrollment_date',
        'enrollment_type',
        'status',
        'photo_url',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
    ];

    protected $appends = [
        'initials',
        'formatted_birth_date',
        'whatsapp_url',
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

    public function spmbCandidate(): HasOne
    {
        return $this->hasOne(SpmbCandidate::class);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->full_name ?? ''));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->full_name ?? 'S', 0, 2));
    }

    public function getFormattedBirthDateAttribute(): ?string
    {
        if (!$this->birth_date) return null;
        return $this->birth_date->translatedFormat('d F Y');
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $phone = $this->parent_phone ?: ($this->father_phone ?: $this->mother_phone);
        if (!$phone) return null;

        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        $appName = function_exists('setting') ? setting('app_name', 'SMP Anak Saleh') : 'SMP Anak Saleh';
        $message = urlencode("Assalamu'alaikum wr. wb. Wali murid ananda *{$this->full_name}*, kami dari *{$appName}*.");
        return "https://wa.me/{$clean}?text={$message}";
    }
}
