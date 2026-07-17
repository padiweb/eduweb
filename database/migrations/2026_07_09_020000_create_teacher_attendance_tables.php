<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sesi absensi guru — dibuat otomatis oleh scheduler
        if (! Schema::hasTable('teacher_attendance_sessions'))
        Schema::create('teacher_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->enum('session_type', ['masuk', 'pulang']); // 2 sesi per hari
            $table->time('open_time');   // jam sesi dibuka
            $table->time('close_time');  // jam sesi ditutup
            $table->time('late_after')->nullable(); // terlambat setelah jam ini
            $table->string('qr_token', 64)->unique(); // token QR permanen per sekolah
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'session_date', 'session_type'], 'tas_school_date_type_unique');
        });

        // Record absensi guru
        if (! Schema::hasTable('teacher_attendances'))
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('teacher_attendance_sessions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', [
                'hadir',          // hadir tepat waktu
                'terlambat',      // hadir tapi lewat late_after
                'izin',           // izin resmi
                'sakit',          // sakit
                'dinas',          // perjalanan dinas
                'alfa',           // tidak hadir tanpa keterangan
            ]);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->float('distance_meters')->nullable(); // jarak dari sekolah
            $table->boolean('is_within_radius')->default(false);
            $table->string('notes', 255)->nullable();
            $table->string('attachment_path')->nullable(); // bukti izin/sakit/dinas
            $table->boolean('is_manual_entry')->default(false);
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'teacher_id'], 'ta_session_teacher_unique');
        });

        // Reward points guru
        if (! Schema::hasTable('teacher_reward_points'))
        Schema::create('teacher_reward_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', [
                'absen_tepat_waktu', // +1 hadir tepat waktu
                'isi_jurnal',        // +1 isi jurnal mengajar
                'bonus',             // bonus manual dari admin
                'pengurang',         // pengurangan manual dari admin
            ]);
            $table->integer('points')->default(1); // bisa negatif untuk pengurang
            $table->string('description', 255)->nullable();
            $table->date('point_date');
            $table->foreignId('reference_id')->nullable(); // id absensi / jurnal terkait
            $table->string('reference_type', 50)->nullable(); // 'attendance' / 'journal'
            $table->timestamps();
        });

        // Tambah kolom ke schools untuk jam absensi guru
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'teacher_checkin_open'))
                $table->time('teacher_checkin_open')->default('06:30:00')->after('attendance_close_time');
            if (! Schema::hasColumn('schools', 'teacher_checkin_close'))
                $table->time('teacher_checkin_close')->default('08:00:00')->after('teacher_checkin_open');
            if (! Schema::hasColumn('schools', 'teacher_checkin_late'))
                $table->time('teacher_checkin_late')->default('07:15:00')->after('teacher_checkin_close');
            if (! Schema::hasColumn('schools', 'teacher_checkout_open'))
                $table->time('teacher_checkout_open')->default('14:00:00')->after('teacher_checkin_late');
            if (! Schema::hasColumn('schools', 'teacher_checkout_close'))
                $table->time('teacher_checkout_close')->default('16:00:00')->after('teacher_checkout_open');
            if (! Schema::hasColumn('schools', 'teacher_qr_token'))
                $table->string('teacher_qr_token', 64)->nullable()->unique()->after('teacher_checkout_close');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_checkin_open', 'teacher_checkin_close', 'teacher_checkin_late',
                'teacher_checkout_open', 'teacher_checkout_close', 'teacher_qr_token',
            ]);
        });
        Schema::dropIfExists('teacher_reward_points');
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('teacher_attendance_sessions');
    }
};