<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prakerin_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('prakerin_placements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('type', ['check_in', 'check_out']);
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa'])->default('hadir');
            // Selfie capture-only
            $table->string('selfie_path');
            $table->timestamp('selfie_taken_at');
            // GPS
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->float('gps_accuracy')->nullable();
            $table->float('distance_from_company')->nullable();
            $table->boolean('is_within_geofence')->default(false);
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            // Verifikasi guru PKL
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['placement_id', 'attendance_date', 'type']);
            $table->index(['student_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prakerin_attendances');
    }
};