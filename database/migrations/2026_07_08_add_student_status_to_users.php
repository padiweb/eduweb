<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status khusus siswa
            $table->enum('student_status', [
                'aktif',    // siswa aktif
                'alumni',   // sudah lulus
                'keluar',   // keluar/DO di tengah jalan
                'pindah',   // pindah ke sekolah lain
            ])->default('aktif')->after('is_active');

            // Tanggal dan keterangan perubahan status
            $table->date('status_changed_at')->nullable()->after('student_status');
            $table->string('status_notes', 255)->nullable()->after('status_changed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['student_status', 'status_changed_at', 'status_notes']);
        });
    }
};
