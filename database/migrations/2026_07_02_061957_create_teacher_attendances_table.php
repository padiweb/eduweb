<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'dinas_luar', 'alfa']);
            // Selfie
            $table->string('selfie_path')->nullable();
            $table->timestamp('selfie_taken_at')->nullable();
            // GPS
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->float('gps_accuracy')->nullable();
            $table->boolean('is_within_radius')->default(true);
            // Jam masuk / pulang
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            // Otomatis flag terlambat
            $table->boolean('is_late')->default(false);
            $table->smallInteger('late_minutes')->nullable();
            // Verifikasi admin
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'attendance_date']);
            $table->index(['school_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
    }
};