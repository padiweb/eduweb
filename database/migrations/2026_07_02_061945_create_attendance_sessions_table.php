<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            // QR token — hanya hash yang disimpan, plain token hanya ada di memori
            $table->string('qr_token', 64)->unique();
            $table->string('qr_token_hash', 64);
            $table->timestamp('token_expires_at');
            // Geofence — disalin dari school saat sesi dibuka
            $table->decimal('school_latitude', 10, 8);
            $table->decimal('school_longitude', 11, 8);
            $table->smallInteger('radius_meters')->default(200);
            // Status
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->boolean('roll_call_done')->default(false);
            $table->timestamp('roll_call_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'session_date']);
            $table->index(['classroom_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};