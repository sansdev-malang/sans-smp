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
        Schema::table('class_levels', function (Blueprint $table) {
            if (!Schema::hasColumn('class_levels', 'order')) {
                $table->integer('order')->default(1)->after('name');
            }
        });

        // Copy values from order_level if order_level exists
        if (Schema::hasColumn('class_levels', 'order_level')) {
            DB::statement('UPDATE class_levels SET `order` = `order_level` WHERE `order` IS NULL OR `order` = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe
    }
};
