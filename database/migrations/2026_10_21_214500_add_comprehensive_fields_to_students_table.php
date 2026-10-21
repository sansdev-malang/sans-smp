<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET SESSION innodb_strict_mode = 0');

        Schema::table('students', function (Blueprint $table) {
            // 1. Make NIS nullable and length 50
            if (Schema::hasColumn('students', 'nis')) {
                $table->string('nis', 50)->nullable()->change();
            }

            // 2. Parent & Guardian Titles & Flags
            if (!Schema::hasColumn('students', 'father_title_prefix')) {
                $table->string('father_title_prefix', 50)->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('students', 'father_title_suffix')) {
                $table->string('father_title_suffix', 50)->nullable()->after('father_title_prefix');
            }
            if (!Schema::hasColumn('students', 'mother_title_prefix')) {
                $table->string('mother_title_prefix', 50)->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('students', 'mother_title_suffix')) {
                $table->string('mother_title_suffix', 50)->nullable()->after('mother_title_prefix');
            }
            if (!Schema::hasColumn('students', 'guardian_title_prefix')) {
                $table->string('guardian_title_prefix', 50)->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('students', 'guardian_title_suffix')) {
                $table->string('guardian_title_suffix', 50)->nullable()->after('guardian_title_prefix');
            }
            if (!Schema::hasColumn('students', 'is_accompanied_by_guardian')) {
                $table->boolean('is_accompanied_by_guardian')->default(false)->after('guardian_relation');
            }

            // 3. Domisili & Transportasi Tambahan
            if (!Schema::hasColumn('students', 'dusun')) {
                $table->string('dusun', 50)->nullable()->after('address');
            }
            if (!Schema::hasColumn('students', 'living_with')) {
                $table->string('living_with', 50)->nullable()->after('residence_status');
            }
            if (!Schema::hasColumn('students', 'transportation')) {
                $table->string('transportation', 50)->nullable()->after('distance_to_school');
            }
            if (!Schema::hasColumn('students', 'travel_time_minutes')) {
                $table->integer('travel_time_minutes')->nullable()->after('transportation');
            }

            // 4. Kontak Darurat
            if (!Schema::hasColumn('students', 'emergency_contact_name')) {
                $table->string('emergency_contact_name', 100)->nullable()->after('parent_email');
            }
            if (!Schema::hasColumn('students', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('students', 'emergency_contact_relation')) {
                $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact_phone');
            }

            // 5. Cita-cita, Hobi, Harapan & Pesan Ortu
            if (!Schema::hasColumn('students', 'student_hobby')) {
                $table->text('student_hobby')->nullable()->after('severe_disease_history');
            }
            if (!Schema::hasColumn('students', 'student_aspiration')) {
                $table->text('student_aspiration')->nullable()->after('student_hobby');
            }
            if (!Schema::hasColumn('students', 'parent_aspiration')) {
                $table->text('parent_aspiration')->nullable()->after('student_aspiration');
            }
            if (!Schema::hasColumn('students', 'planned_school_after')) {
                $table->text('planned_school_after')->nullable()->after('parent_aspiration');
            }
            if (!Schema::hasColumn('students', 'parent_message')) {
                $table->text('parent_message')->nullable()->after('planned_school_after');
            }
            if (!Schema::hasColumn('students', 'parent_suggestion')) {
                $table->text('parent_suggestion')->nullable()->after('parent_message');
            }

            // 6. Hybrid JSON Fields (Structured Storage for all 150 survey & questionnaire columns)
            if (!Schema::hasColumn('students', 'family_data')) {
                $table->json('family_data')->nullable()->after('emergency_contact_relation');
            }
            if (!Schema::hasColumn('students', 'health_habits')) {
                $table->json('health_habits')->nullable()->after('family_data');
            }
            if (!Schema::hasColumn('students', 'development_psychology')) {
                $table->json('development_psychology')->nullable()->after('health_habits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET SESSION innodb_strict_mode = 0');

        Schema::table('students', function (Blueprint $table) {
            $cols = [
                'father_title_prefix', 'father_title_suffix',
                'mother_title_prefix', 'mother_title_suffix',
                'guardian_title_prefix', 'guardian_title_suffix',
                'is_accompanied_by_guardian',
                'dusun', 'living_with', 'transportation', 'travel_time_minutes',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
                'student_hobby', 'student_aspiration', 'parent_aspiration',
                'planned_school_after', 'parent_message', 'parent_suggestion',
                'family_data', 'health_habits', 'development_psychology',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('students', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
