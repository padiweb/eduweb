<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Batas poin untuk peringatan 1, 2, 3
            // 0 = fitur peringatan tidak aktif
            $table->tinyInteger('violation_warning1')->default(10)->after('feature_violations');
            $table->tinyInteger('violation_warning2')->default(20)->after('violation_warning1');
            $table->tinyInteger('violation_warning3')->default(30)->after('violation_warning2');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['violation_warning1', 'violation_warning2', 'violation_warning3']);
        });
    }
};