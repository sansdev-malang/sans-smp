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
            $table->string('transportation', 255)->nullable()->change();
            $table->string('living_with', 255)->nullable()->change();
            $table->string('emergency_contact_name', 255)->nullable()->change();
            $table->string('emergency_contact_phone', 100)->nullable()->change();
            $table->string('emergency_contact_relation', 150)->nullable()->change();
            $table->string('distance_to_school', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET SESSION innodb_strict_mode = 0');

        Schema::table('students', function (Blueprint $table) {
            $table->string('transportation', 50)->nullable()->change();
            $table->string('living_with', 50)->nullable()->change();
            $table->string('emergency_contact_name', 100)->nullable()->change();
            $table->string('emergency_contact_phone', 30)->nullable()->change();
            $table->string('emergency_contact_relation', 50)->nullable()->change();
            $table->string('distance_to_school', 50)->nullable()->change();
        });
    }
};
