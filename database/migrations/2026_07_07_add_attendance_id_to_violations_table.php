<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            // Link pelanggaran ke absensi — untuk auto-rollback saat status diubah
            $table->foreignId('attendance_id')
                  ->nullable()
                  ->after('reported_by')
                  ->constrained('attendances')
                  ->nullOnDelete();

            $table->index(['attendance_id']);
            $table->index(['student_id', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
            $table->dropColumn('attendance_id');
        });
    }
};
