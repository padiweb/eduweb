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
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('session_date');

            // QR token — hanya hash yang disimpan
            $table->string('qr_token_hash', 64);
            $table->timestamp('qr_generated_at')->nullable();

            // Jam aktif sesi — disalin dari school saat sesi dibuka
            $table->time('open_time')->default('06:30:00');
            $table->time('close_time')->default('08:00:00');
            $table->time('late_after')->default('07:15:00');

            // Geofence
            $table->decimal('school_latitude', 10, 8)->nullable();
            $table->decimal('school_longitude', 11, 8)->nullable();
            $table->smallInteger('radius_meters')->default(200);

            // Status
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();

            // Roll call
            $table->boolean('roll_call_done')->default(false);
            $table->foreignId('roll_call_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('roll_call_at')->nullable();

            $table->timestamps();

            // Satu kelas hanya boleh punya 1 sesi per hari
            $table->unique(['classroom_id', 'session_date']);
            $table->index(['school_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};