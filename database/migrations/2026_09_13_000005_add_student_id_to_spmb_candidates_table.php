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
        Schema::table('spmb_candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_candidates', 'is_enrolled')) {
                $table->boolean('is_enrolled')->default(false)->index()->after('is_active_student');
            }
            if (!Schema::hasColumn('spmb_candidates', 'enrolled_at')) {
                $table->timestamp('enrolled_at')->nullable()->after('is_enrolled');
            }
            if (!Schema::hasColumn('spmb_candidates', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete()->after('enrolled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_candidates', function (Blueprint $table) {
            if (Schema::hasColumn('spmb_candidates', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }
            if (Schema::hasColumn('spmb_candidates', 'enrolled_at')) {
                $table->dropColumn('enrolled_at');
            }
            if (Schema::hasColumn('spmb_candidates', 'is_enrolled')) {
                $table->dropColumn('is_enrolled');
            }
        });
    }
};
