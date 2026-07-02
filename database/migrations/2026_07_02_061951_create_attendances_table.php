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
            $table->enum('status', ['hadir', 'izin', 'sakit', 'terlambat', 'alfa']);
            // GPS saat scan
            $table->decimal('scan_latitude', 10, 8)->nullable();
            $table->decimal('scan_longitude', 11, 8)->nullable();
            $table->float('gps_accuracy')->nullable();
            $table->float('distance_from_school')->nullable();
            $table->boolean('is_within_radius')->default(true);
            // Timestamp server — tidak bisa dimanipulasi client
            $table->timestamp('scanned_at');
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            // Izin / sakit
            $table->string('permission_reason', 500)->nullable();
            $table->string('attachment_path')->nullable();
            // Koreksi manual guru
            $table->boolean('is_manual_override')->default(false);
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('override_reason')->nullable();
            $table->timestamp('override_at')->nullable();
            // Flag anomali
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'student_id']); // 1 siswa = 1x per sesi
            $table->index(['student_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};