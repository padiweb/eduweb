<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\TeacherAttendanceSession;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTeacherDailySessions extends Command
{
    protected $signature   = 'attendance:create-teacher-sessions';
    protected $description = 'Buat sesi absensi guru harian (masuk + pulang) berdasarkan pengaturan sekolah';

    public function handle(): int
    {
        $today  = today();
        $count  = 0;

        School::where('is_active', true)->each(function (School $school) use ($today, &$count) {
            // Sesi masuk
            TeacherAttendanceSession::firstOrCreate(
                [
                    'school_id'    => $school->id,
                    'session_date' => $today,
                    'session_type' => 'masuk',
                ],
                [
                    'open_time'  => $school->teacher_checkin_open  ?? '06:30:00',
                    'close_time' => $school->teacher_checkin_close ?? '08:00:00',
                    'late_after' => $school->teacher_checkin_late  ?? '07:15:00',
                    'qr_token'   => $school->teacher_qr_token ?? Str::random(32),
                    'is_active'  => true,
                ]
            );

            // Sesi pulang
            TeacherAttendanceSession::firstOrCreate(
                [
                    'school_id'    => $school->id,
                    'session_date' => $today,
                    'session_type' => 'pulang',
                ],
                [
                    'open_time'  => $school->teacher_checkout_open  ?? '14:00:00',
                    'close_time' => $school->teacher_checkout_close ?? '16:00:00',
                    'late_after' => null,
                    'qr_token'   => $school->teacher_qr_token ?? Str::random(32),
                    'is_active'  => true,
                ]
            );

            $count++;
        });

        $this->info("Sesi absensi guru dibuat untuk {$count} sekolah.");
        return self::SUCCESS;
    }
}
