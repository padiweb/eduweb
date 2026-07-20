<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'prakerin_points_no_checkin')) {
                $table->tinyInteger('prakerin_points_no_checkin')->default(2)->after('feature_prakerin');
            }
            if (! Schema::hasColumn('schools', 'prakerin_points_no_checkout')) {
                $table->tinyInteger('prakerin_points_no_checkout')->default(1)->after('prakerin_points_no_checkin');
            }
            if (! Schema::hasColumn('schools', 'prakerin_points_no_journal')) {
                $table->tinyInteger('prakerin_points_no_journal')->default(1)->after('prakerin_points_no_checkout');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $cols = ['prakerin_points_no_checkin', 'prakerin_points_no_checkout', 'prakerin_points_no_journal'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('schools', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};