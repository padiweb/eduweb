<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->time('open_time')->default('06:30:00')->after('qr_token_hash');
            $table->time('close_time')->default('08:00:00')->after('open_time');
            $table->time('late_after')->default('07:15:00')->after('close_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time', 'late_after']);
        });
    }
};