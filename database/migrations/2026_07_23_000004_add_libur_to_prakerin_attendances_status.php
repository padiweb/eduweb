<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE `prakerin_attendances`
            MODIFY COLUMN `status`
            ENUM('hadir','terlambat','izin','sakit','libur','alfa')
            NOT NULL DEFAULT 'hadir'");
    }
    public function down(): void {
        DB::table('prakerin_attendances')->where('status','libur')->delete();
        DB::statement("ALTER TABLE `prakerin_attendances`
            MODIFY COLUMN `status`
            ENUM('hadir','terlambat','izin','sakit','alfa')
            NOT NULL DEFAULT 'hadir'");
    }
};
