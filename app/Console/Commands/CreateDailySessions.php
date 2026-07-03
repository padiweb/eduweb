<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateDailySessions extends Command
{
    protected $signature   = 'attendance:create-daily-sessions
                             {--school= : ID sekolah spesifik (opsional, default semua)}
                             {--date=   : Tanggal spesifik Y-m-d (opsional, default hari ini)}';

    protected $description = 'Buat sesi absensi otomatis untuk semua kelas aktif hari ini';

    public function handle(): int
    {
        $date     = $this->option('date') ? now()->parse($this->option('date')) : now();
        $schoolId = $this->option('school');

        // Lewati hari Minggu
        if ($date->dayOfWeek === 0) {
            $this->info('Hari Minggu - tidak ada sesi absensi.');
            return self::SUCCESS;
        }

        $schools = School::where('is_active', true)
            ->when($schoolId, fn($q) => $q->where('id', $schoolId))
            ->get();

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($schools as $school) {
            if (! $school->latitude || ! $school->longitude) {
                $this->warn("Sekolah [{$school->name}] belum punya koordinat GPS - skip.");
                continue;
            }

            $activeYear = $school->activeAcademicYear();
            if (! $activeYear) {
                $this->warn("Sekolah [{$school->name}] tidak punya tahun ajaran aktif - skip.");
                continue;
            }

            $classrooms = Classroom::where('school_id', $school->id)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            foreach ($classrooms as $classroom) {
                $exists = AttendanceSession::where('classroom_id', $classroom->id)
                    ->whereDate('session_date', $date->toDateString())
                    ->exists();

                if ($exists) {
                    $totalSkipped++;
                    continue;
                }

                $plainToken = Str::random(40);
                $tokenHash  = hash('sha256', $plainToken);

                AttendanceSession::create([
                    'school_id'        => $school->id,
                    'classroom_id'     => $classroom->id,
                    'opened_by'        => null,
                    'auto_created'     => true,
                    'session_date'     => $date->toDateString(),
                    'qr_token_hash'    => $tokenHash,
                    'qr_generated_at'  => now(),
                    'open_time'        => $school->school_start_time,
                    'close_time'       => $school->attendance_close_time,
                    'late_after'       => $school->late_threshold_time,
                    'school_latitude'  => $school->latitude,
                    'school_longitude' => $school->longitude,
                    'radius_meters'    => $school->attendance_radius_meters,
                ]);

                $totalCreated++;
                $this->line("  OK {$school->name} - {$classroom->name}");
            }
        }

        $this->info("Selesai: {$totalCreated} sesi dibuat, {$totalSkipped} sudah ada.");

        return self::SUCCESS;
    }
}
