<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel koordinator prakerin per periode.
 * Koordinator bisa tambah DU/DI dan penempatan siswa
 * di lokasi yang mereka bimbing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prakerin_coordinators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('prakerin_periods')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['period_id', 'teacher_id']);
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prakerin_coordinators');
    }
};
