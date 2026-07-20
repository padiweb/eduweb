<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prakerin_attendances', function (Blueprint $table) {
            $table->boolean('violation_created')->default(false)->after('notes');
        });

        Schema::table('prakerin_journals', function (Blueprint $table) {
            $table->boolean('violation_created')->default(false)->after('noted_at');
        });
    }

    public function down(): void
    {
        Schema::table('prakerin_attendances', function (Blueprint $table) {
            $table->dropColumn('violation_created');
        });
        Schema::table('prakerin_journals', function (Blueprint $table) {
            $table->dropColumn('violation_created');
        });
    }
};
