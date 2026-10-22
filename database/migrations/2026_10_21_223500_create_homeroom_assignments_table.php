<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('homeroom_assignments')) {
            Schema::create('homeroom_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('class_level_id')->nullable()->constrained('class_levels')->nullOnDelete();
                $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('role', 30)->default('wali_kelas'); // wali_kelas, guru_mapel, gpk, gpq
                $table->string('subject', 100)->nullable(); // Mata pelajaran jika role = guru_mapel
                $table->boolean('is_active')->default(true)->index();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['academic_year_id', 'classroom_id', 'role']);
                $table->index(['employee_id', 'is_active']);
            });

            // Backfill existing homeroom teachers from classrooms table
            $now = now();
            $classrooms = \Illuminate\Support\Facades\DB::table('classrooms')
                ->whereNotNull('homeroom_teacher_id')
                ->get();

            $activeAy = \Illuminate\Support\Facades\DB::table('academic_years')->where('is_active', true)->first();

            foreach ($classrooms as $cr) {
                $ayId = $cr->academic_year_id ?: ($activeAy?->id ?? null);
                if ($ayId) {
                    \Illuminate\Support\Facades\DB::table('homeroom_assignments')->insert([
                        'academic_year_id' => $ayId,
                        'class_level_id' => $cr->class_level_id,
                        'classroom_id' => $cr->id,
                        'employee_id' => $cr->homeroom_teacher_id,
                        'role' => 'wali_kelas',
                        'subject' => null,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homeroom_assignments');
    }
};
