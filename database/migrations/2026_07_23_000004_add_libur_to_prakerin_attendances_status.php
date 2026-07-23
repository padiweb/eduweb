<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah nilai 'libur' ke enum status prakerin_attendances
        DB::statement("ALTER TABLE `prakerin_attendances`
            MODIFY COLUMN `status`
            ENUM('hadir','terlambat','izin','sakit','libur','alfa')
            NOT NULL DEFAULT 'hadir'");
    }

    public function down(): void
    {
        // Hapus record libur dulu sebelum revert
        DB::table('prakerin_attendances')->where('status', 'libur')->delete();

        DB::statement("ALTER TABLE `prakerin_attendances`
            MODIFY COLUMN `status`
            ENUM('hadir','terlambat','izin','sakit','alfa')
            NOT NULL DEFAULT 'hadir'");
    }
};
