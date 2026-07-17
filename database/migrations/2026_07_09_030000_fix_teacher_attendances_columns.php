<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            // Tambah kolom yang kurang jika belum ada
            if (! Schema::hasColumn('teacher_attendances', 'session_id')) {
                $table->foreignId('session_id')
                    ->after('school_id')
                    ->constrained('teacher_attendance_sessions')
                    ->cascadeOnDelete();
            }
            if (! Schema::hasColumn('teacher_attendances', 'teacher_id')) {
                $table->foreignId('teacher_id')
                    ->after('session_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }
            if (! Schema::hasColumn('teacher_attendances', 'status')) {
                $table->enum('status', ['hadir','terlambat','izin','sakit','dinas','alfa'])
                    ->after('teacher_id');
            }
            if (! Schema::hasColumn('teacher_attendances', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('status');
            }
            if (! Schema::hasColumn('teacher_attendances', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('teacher_attendances', 'distance_meters')) {
                $table->float('distance_meters')->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('teacher_attendances', 'is_within_radius')) {
                $table->boolean('is_within_radius')->default(false)->after('distance_meters');
            }
            if (! Schema::hasColumn('teacher_attendances', 'notes')) {
                $table->string('notes', 255)->nullable()->after('is_within_radius');
            }
            if (! Schema::hasColumn('teacher_attendances', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('teacher_attendances', 'is_manual_entry')) {
                $table->boolean('is_manual_entry')->default(false)->after('attachment_path');
            }
            if (! Schema::hasColumn('teacher_attendances', 'scanned_at')) {
                $table->timestamp('scanned_at')->nullable()->after('is_manual_entry');
            }
        });

        // Tambah unique constraint jika belum ada
        try {
            Schema::table('teacher_attendances', function (Blueprint $table) {
                $table->unique(['session_id', 'teacher_id']);
            });
        } catch (\Exception $e) {
            // Constraint sudah ada, skip
        }
    }

    public function down(): void
    {
        // Tidak perlu rollback kolom tambahan
    }
};