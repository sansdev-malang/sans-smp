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
        Schema::table('students', function (Blueprint $table) {
            // Ciri Fisik & Suku
            if (!Schema::hasColumn('students', 'ethnic_group')) {
                $table->string('ethnic_group', 100)->nullable()->after('home_language');
            }
            if (!Schema::hasColumn('students', 'skin_color')) {
                $table->string('skin_color', 50)->nullable()->after('blood_type');
            }
            if (!Schema::hasColumn('students', 'hair_type')) {
                $table->string('hair_type', 50)->nullable()->after('skin_color');
            }
            if (!Schema::hasColumn('students', 'hair_color')) {
                $table->string('hair_color', 50)->nullable()->after('hair_type');
            }

            // Tambahan Ayah
            if (!Schema::hasColumn('students', 'father_address')) {
                $table->text('father_address')->nullable()->after('father_nik');
            }
            if (!Schema::hasColumn('students', 'father_social_media')) {
                $table->string('father_social_media', 255)->nullable()->after('father_email');
            }

            // Tambahan Ibu
            if (!Schema::hasColumn('students', 'mother_address')) {
                $table->text('mother_address')->nullable()->after('mother_nik');
            }
            if (!Schema::hasColumn('students', 'mother_social_media')) {
                $table->string('mother_social_media', 255)->nullable()->after('mother_email');
            }

            // Tambahan Wali Lengkap
            if (!Schema::hasColumn('students', 'guardian_nik')) {
                $table->string('guardian_nik', 30)->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('students', 'guardian_company')) {
                $table->string('guardian_company', 255)->nullable()->after('guardian_job');
            }
            if (!Schema::hasColumn('students', 'guardian_company_address')) {
                $table->text('guardian_company_address')->nullable()->after('guardian_company');
            }
            if (!Schema::hasColumn('students', 'guardian_company_phone')) {
                $table->string('guardian_company_phone', 50)->nullable()->after('guardian_company_address');
            }
            if (!Schema::hasColumn('students', 'guardian_income')) {
                $table->string('guardian_income', 50)->nullable()->after('guardian_company_phone');
            }
            if (!Schema::hasColumn('students', 'guardian_email')) {
                $table->string('guardian_email', 100)->nullable()->after('guardian_income');
            }
            if (!Schema::hasColumn('students', 'guardian_social_media')) {
                $table->string('guardian_social_media', 255)->nullable()->after('guardian_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = [
                'ethnic_group', 'skin_color', 'hair_type', 'hair_color',
                'father_address', 'father_social_media',
                'mother_address', 'mother_social_media',
                'guardian_nik', 'guardian_company', 'guardian_company_address',
                'guardian_company_phone', 'guardian_income', 'guardian_email', 'guardian_social_media'
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('students', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
