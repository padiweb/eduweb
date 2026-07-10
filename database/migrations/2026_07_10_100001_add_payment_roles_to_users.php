<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ALTER untuk extend enum (tidak bisa via Blueprint pada enum yang sudah ada)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'siswa',
            'guru',
            'wali_kelas',
            'kesiswaan',
            'admin',
            'bendahara',
            'kepala_sekolah'
        ) NOT NULL DEFAULT 'siswa'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'siswa',
            'guru',
            'wali_kelas',
            'kesiswaan',
            'admin'
        ) NOT NULL DEFAULT 'siswa'");
    }
};
