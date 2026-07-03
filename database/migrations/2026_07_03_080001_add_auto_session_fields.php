<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah slug ke classrooms — dipakai untuk URL QR permanen
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('slug', 30)->nullable()->after('name');
        });

        // Tambah flag auto_created ke attendance_sessions
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->boolean('auto_created')->default(false)->after('opened_by');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn('auto_created');
        });
    }
};
