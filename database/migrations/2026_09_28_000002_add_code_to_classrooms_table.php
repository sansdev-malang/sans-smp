<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (!Schema::hasColumn('classrooms', 'code')) {
                $table->string('code', 50)->nullable()->after('class_level_id');
            }
        });

        // Auto-generate code from name if empty
        $classrooms = DB::table('classrooms')->get();
        foreach ($classrooms as $cr) {
            if (empty($cr->code)) {
                // e.g. "7-A" -> "7A", "7A - Samudra Pasai" -> "7A", "8-B" -> "8B"
                $code = strtoupper(str_replace(['-', ' '], '', preg_replace('/^([0-9]+[\s\-_]*[A-Za-z]+).*/', '$1', $cr->name)));
                if (empty($code)) {
                    $code = substr($cr->name, 0, 3);
                }
                DB::table('classrooms')->where('id', $cr->id)->update(['code' => $code]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('classrooms', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
