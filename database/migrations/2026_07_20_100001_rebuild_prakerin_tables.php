<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rebuild modul prakerin dari awal dengan struktur baru:
 * - prakerin_periods       : periode pelaksanaan prakerin per sekolah
 * - prakerin_locations     : master DU/DI dengan jam masuk/pulang masing-masing
 * - prakerin_loc_supervisors: guru pembimbing per DU/DI (bisa lebih dari 1)
 * - prakerin_placements    : penempatan siswa ke DU/DI dalam periode tertentu
 * - prakerin_attendances   : absensi selfie + GPS per placement
 * - prakerin_journals      : jurnal harian per placement
 * - prakerin_journal_photos: foto dokumentasi jurnal
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop semua tabel lama (urutan terbalik karena FK)
        Schema::dropIfExists('prakerin_journal_photos');
        Schema::dropIfExists('prakerin_journals');
        Schema::dropIfExists('prakerin_attendances');
        Schema::dropIfExists('prakerin_placements');
        Schema::dropIfExists('prakerin_loc_supervisors');
        Schema::dropIfExists('prakerin_locations');
        Schema::dropIfExists('prakerin_periods');

        // ── 1. Periode Prakerin ──────────────────────────────────────────
        Schema::create('prakerin_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);            // "Prakerin Semester Genap 2025/2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
        });

        // ── 2. Master DU/DI ─────────────────────────────────────────────
        Schema::create('prakerin_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('prakerin_periods')->cascadeOnDelete();
            $table->string('name', 150);                        // Nama DU/DI
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->smallInteger('radius_meters')->default(300);
            $table->string('field_supervisor_name', 100)->nullable();  // Pembimbing dari DU/DI
            $table->string('field_supervisor_phone', 20)->nullable();
            // Jam khusus per DU/DI (nullable = ikut jam default periode)
            $table->time('checkin_time')->nullable();           // Jam masuk DU/DI ini
            $table->time('checkout_time')->nullable();          // Jam pulang DU/DI ini
            $table->time('checkin_late_after')->nullable();     // Toleransi terlambat
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'period_id']);
        });

        // ── 3. Guru Pembimbing per DU/DI (pivot) ────────────────────────
        Schema::create('prakerin_loc_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('prakerin_locations')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['location_id', 'teacher_id']);
        });

        // ── 4. Penempatan Siswa ──────────────────────────────────────────
        Schema::create('prakerin_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('prakerin_periods')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('prakerin_locations')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            // Tanggal bisa override dari periode (untuk siswa yang pindah tengah jalan)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'period_id']);
            $table->index(['location_id']);
        });

        // ── 5. Absensi Prakerin ──────────────────────────────────────────
        Schema::create('prakerin_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('prakerin_placements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('type', ['check_in', 'check_out']);
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alfa'])->default('hadir');
            // Selfie
            $table->string('selfie_path')->nullable();
            $table->timestamp('selfie_taken_at')->nullable();
            // GPS
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->float('gps_accuracy')->nullable();
            $table->float('distance_from_location')->nullable();
            $table->boolean('is_within_geofence')->default(false);
            // Meta
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            // Verifikasi pembimbing
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('violation_created')->default(false);
            $table->timestamps();

            $table->unique(['placement_id', 'attendance_date', 'type']);
            $table->index(['student_id', 'attendance_date']);
        });

        // ── 6. Jurnal Harian ─────────────────────────────────────────────
        Schema::create('prakerin_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('prakerin_placements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('journal_date');
            $table->text('content');
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            // Catatan guru
            $table->text('teacher_note')->nullable();
            $table->foreignId('noted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('noted_at')->nullable();
            $table->boolean('violation_created')->default(false);
            $table->timestamps();

            $table->unique(['placement_id', 'journal_date']);
            $table->index(['student_id', 'journal_date']);
        });

        // ── 7. Foto Jurnal ───────────────────────────────────────────────
        Schema::create('prakerin_journal_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('prakerin_journals')->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption', 200)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prakerin_journal_photos');
        Schema::dropIfExists('prakerin_journals');
        Schema::dropIfExists('prakerin_attendances');
        Schema::dropIfExists('prakerin_placements');
        Schema::dropIfExists('prakerin_loc_supervisors');
        Schema::dropIfExists('prakerin_locations');
        Schema::dropIfExists('prakerin_periods');
    }
};
