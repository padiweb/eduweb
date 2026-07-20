<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Poin pelanggaran prakerin — bisa dikonfigurasi admin
            $table->tinyInteger('prakerin_points_no_checkin')->default(2)->after('feature_prakerin');
            $table->tinyInteger('prakerin_points_no_checkout')->default(1)->after('prakerin_points_no_checkin');
            $table->tinyInteger('prakerin_points_no_journal')->default(1)->after('prakerin_points_no_checkout');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'prakerin_points_no_checkin',
                'prakerin_points_no_checkout',
                'prakerin_points_no_journal',
            ]);
        });
    }
};
