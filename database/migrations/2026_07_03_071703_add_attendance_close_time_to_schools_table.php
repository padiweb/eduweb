<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->time('attendance_close_time')
                  ->default('08:00:00');

            $table->tinyInteger('school_program_years')
                  ->default(3);
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['attendance_close_time', 'school_program_years']);
        });
    }
};