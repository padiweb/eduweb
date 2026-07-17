<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Batas alfa per semester — 0 = tidak ada batas
            $table->tinyInteger('alfa_limit_per_semester')->default(0)
                  ;
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('alfa_limit_per_semester');
        });
    }
};