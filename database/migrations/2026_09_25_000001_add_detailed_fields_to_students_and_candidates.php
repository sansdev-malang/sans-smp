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
        // 1. Tambah detail lengkap ke tabel students
        Schema::table('students', function (Blueprint $table) {
            // Kolom dasar pendukung jika belum ada
            if (!Schema::hasColumn('students', 'city')) {
                $table->string('city', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'province')) {
                $table->string('province', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'documents')) {
                $table->json('documents')->nullable();
            }
            if (!Schema::hasColumn('students', 'student_photo_url')) {
                $table->text('student_photo_url')->nullable();
            }
            if (!Schema::hasColumn('students', 'spmb_candidate_id')) {
                $table->foreignId('spmb_candidate_id')->nullable()->constrained('spmb_candidates')->nullOnDelete();
            }

            // Legalitas & Berkas
            if (!Schema::hasColumn('students', 'no_kk')) {
                $table->string('no_kk', 30)->nullable()->index();
            }
            if (!Schema::hasColumn('students', 'birth_certificate_no')) {
                $table->string('birth_certificate_no', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'citizenship')) {
                $table->string('citizenship', 50)->default('WNI');
            }
            if (!Schema::hasColumn('students', 'checklist_documents')) {
                $table->json('checklist_documents')->nullable();
            }

            // Inklusi & Kekhususan
            if (!Schema::hasColumn('students', 'student_type')) {
                $table->string('student_type', 50)->default('REGULER')->index();
            }
            if (!Schema::hasColumn('students', 'special_needs_type')) {
                $table->string('special_needs_type', 255)->nullable();
            }
            if (!Schema::hasColumn('students', 'special_needs_notes')) {
                $table->text('special_needs_notes')->nullable();
            }

            // Alamat & Domisili Detail
            if (!Schema::hasColumn('students', 'rt')) {
                $table->string('rt', 10)->nullable();
            }
            if (!Schema::hasColumn('students', 'rw')) {
                $table->string('rw', 10)->nullable();
            }
            if (!Schema::hasColumn('students', 'village')) {
                $table->string('village', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'district')) {
                $table->string('district', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'district_category')) {
                $table->string('district_category', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'postal_code')) {
                $table->string('postal_code', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'residence_status')) {
                $table->string('residence_status', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'distance_to_school')) {
                $table->string('distance_to_school', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'home_phone')) {
                $table->string('home_phone', 50)->nullable();
            }

            // Keluarga & Saudara
            if (!Schema::hasColumn('students', 'child_number')) {
                $table->integer('child_number')->nullable();
            }
            if (!Schema::hasColumn('students', 'siblings_count')) {
                $table->integer('siblings_count')->nullable();
            }
            if (!Schema::hasColumn('students', 'step_siblings_count')) {
                $table->integer('step_siblings_count')->nullable();
            }
            if (!Schema::hasColumn('students', 'adoptive_siblings_count')) {
                $table->integer('adoptive_siblings_count')->nullable();
            }
            if (!Schema::hasColumn('students', 'home_language')) {
                $table->string('home_language', 100)->nullable();
            }

            // Fisik, Kesehatan, UKS
            if (!Schema::hasColumn('students', 'weight')) {
                $table->string('weight', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'height')) {
                $table->string('height', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'blood_type')) {
                $table->string('blood_type', 10)->nullable();
            }
            if (!Schema::hasColumn('students', 'severe_disease_history')) {
                $table->text('severe_disease_history')->nullable();
            }
            if (!Schema::hasColumn('students', 'frequent_disease')) {
                $table->text('frequent_disease')->nullable();
            }

            // Detail Ayah
            if (!Schema::hasColumn('students', 'father_nik')) {
                $table->string('father_nik', 30)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_birth_place')) {
                $table->string('father_birth_place', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_birth_date')) {
                $table->date('father_birth_date')->nullable();
            }
            if (!Schema::hasColumn('students', 'father_religion')) {
                $table->string('father_religion', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_education')) {
                $table->string('father_education', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_company')) {
                $table->string('father_company', 255)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_company_address')) {
                $table->text('father_company_address')->nullable();
            }
            if (!Schema::hasColumn('students', 'father_company_phone')) {
                $table->string('father_company_phone', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_income')) {
                $table->string('father_income', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'father_email')) {
                $table->string('father_email', 100)->nullable();
            }

            // Detail Ibu
            if (!Schema::hasColumn('students', 'mother_nik')) {
                $table->string('mother_nik', 30)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_birth_place')) {
                $table->string('mother_birth_place', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_birth_date')) {
                $table->date('mother_birth_date')->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_religion')) {
                $table->string('mother_religion', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_education')) {
                $table->string('mother_education', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_company')) {
                $table->string('mother_company', 255)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_company_address')) {
                $table->text('mother_company_address')->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_company_phone')) {
                $table->string('mother_company_phone', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_income')) {
                $table->string('mother_income', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_email')) {
                $table->string('mother_email', 100)->nullable();
            }

            // Detail Wali
            if (!Schema::hasColumn('students', 'guardian_relation')) {
                $table->string('guardian_relation', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_birth_place')) {
                $table->string('guardian_birth_place', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_birth_date')) {
                $table->date('guardian_birth_date')->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_education')) {
                $table->string('guardian_education', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_job')) {
                $table->string('guardian_job', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_religion')) {
                $table->string('guardian_religion', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_address')) {
                $table->text('guardian_address')->nullable();
            }

            // Asal Sekolah & Kelulusan
            if (!Schema::hasColumn('students', 'origin_category')) {
                $table->string('origin_category', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'previous_school_address')) {
                $table->text('previous_school_address')->nullable();
            }
            if (!Schema::hasColumn('students', 'sttb_number_date')) {
                $table->string('sttb_number_date', 100)->nullable();
            }
            if (!Schema::hasColumn('students', 'graduation_year')) {
                $table->string('graduation_year', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'diploma_number')) {
                $table->string('diploma_number', 100)->nullable();
            }
        });

        // 2. Tambah kolom pendukung ke spmb_candidates agar sinkron
        Schema::table('spmb_candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_candidates', 'no_kk')) {
                $table->string('no_kk', 30)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'student_type')) {
                $table->string('student_type', 50)->default('REGULER');
            }
            if (!Schema::hasColumn('spmb_candidates', 'special_needs_type')) {
                $table->string('special_needs_type', 255)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'father_nik')) {
                $table->string('father_nik', 30)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'mother_nik')) {
                $table->string('mother_nik', 30)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'father_education')) {
                $table->string('father_education', 50)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'mother_education')) {
                $table->string('mother_education', 50)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'blood_type')) {
                $table->string('blood_type', 10)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'weight')) {
                $table->string('weight', 20)->nullable();
            }
            if (!Schema::hasColumn('spmb_candidates', 'height')) {
                $table->string('height', 20)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback if needed
    }
};
