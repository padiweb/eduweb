<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alfa']);

            // GPS saat scan
            $table->decimal('scan_latitude', 10, 8)->nullable();
            $table->decimal('scan_longitude', 11, 8)->nullable();
            $table->float('gps_accuracy')->nullable();
            $table->float('distance_from_school')->nullable();
            $table->boolean('is_within_radius')->default(true);

            // Timestamp server
            $table->timestamp('scanned_at')->nullable();

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Keterangan izin/sakit
            $table->string('permission_reason', 500)->nullable();
            $table->string('attachment_path')->nullable();

            // Input manual oleh guru
            $table->boolean('is_manual_entry')->default(false);
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('entry_reason')->nullable();
            $table->timestamp('entry_at')->nullable();

            // Flag scan di luar jam
            $table->boolean('is_late_scan')->default(false);
            $table->boolean('violation_created')->default(false);

            // Flag anomali
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();

            $table->timestamps();

            // 1 siswa = 1 absen per sesi
            $table->unique(['session_id', 'student_id']);
            $table->index(['student_id', 'scanned_at']);
        });

        // Tabel validasi roll call guru
        Schema::create('attendance_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject_name', 150)->nullable();
            $table->timestamp('validated_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_validations');
        Schema::dropIfExists('attendances');
    }
};