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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable()->index();
            $table->string('nik', 30)->nullable()->index();
            $table->string('full_name')->index();
            $table->string('nickname')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion')->default('Islam');
            
            // Academic linkage
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('class_level_id')->nullable()->constrained('class_levels')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            
            // Contact & Parents
            $table->text('address')->nullable();
            $table->string('parent_phone', 30)->nullable();
            $table->string('parent_email')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_phone', 30)->nullable();
            $table->string('father_job')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone', 30)->nullable();
            $table->string('mother_job')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 30)->nullable();
            
            // Origin & Enrolment
            $table->string('previous_school')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->enum('enrollment_type', ['spmb', 'mutasi', 'manual', 'import'])->default('spmb');
            $table->enum('status', ['aktif', 'lulus', 'mutasi_keluar', 'drop_out', 'non_aktif'])->default('aktif')->index();
            
            // Media & Metadata
            $table->string('photo_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
