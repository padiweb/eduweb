<?php

namespace App\Console\Commands;

use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateDailySessions extends Command
{
    protected $signature   = 'attendance:create-daily-sessions
                             {--school= : ID sekolah spesifik}
                             {--date=   : Tanggal Y-m-d (default hari ini)}';

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
        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($schools as $school) {
            // Set timezone sekolah
            if ($school->timezone) {
                date_default_timezone_set($school->timezone);
            }

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
                $existing = AttendanceSession::where('classroom_id', $classroom->id)
                    ->whereDate('session_date', $date->toDateString())
                    ->first();

                if ($existing) {
                    // Sesi sudah ada — update jam dan koordinat dari settings terbaru
                    // tanpa generate token baru (token yang ada tetap berlaku)
                    $existing->update([
                        'open_time'        => $school->school_start_time,
                        'close_time'       => $school->attendance_close_time,
                        'late_after'       => $school->late_threshold_time,
                        'school_latitude'  => $school->latitude,
                        'school_longitude' => $school->longitude,
                        'radius_meters'    => $school->attendance_radius_meters,
                    ]);
                    $totalUpdated++;
                    continue;
                }

                // Buat sesi baru dengan token baru
                $plainToken = Str::random(40);
                $tokenHash  = hash('sha256', $plainToken);

                $session = AttendanceSession::create([
                    'school_id'        => $school->id,
                    'classroom_id'     => $classroom->id,
                    'opened_by'        => null,
                    'auto_created'     => true,
                    'session_date'     => $date->toDateString(),
                    'qr_token_hash'    => $tokenHash,
                    'qr_generated_at'  => now(),
                    // Selalu ambil dari school — bukan hardcode
                    'open_time'        => $school->school_start_time,
                    'close_time'       => $school->attendance_close_time,
                    'late_after'       => $school->late_threshold_time,
                    'school_latitude'  => $school->latitude,
                    'school_longitude' => $school->longitude,
                    'radius_meters'    => $school->attendance_radius_meters,
                ]);

                // Simpan plain token ke cache sementara (10 jam)
                // agar halaman QR guru bisa ambil token yang sama
                cache()->put(
                    "session_token_{$session->id}",
                    $plainToken,
                    now()->addHours(10)
                );

                $totalCreated++;
                $this->line("  OK {$school->name} - {$classroom->name}");
            }
        }

        $this->info("Selesai: {$totalCreated} sesi dibuat, {$totalUpdated} diperbarui, {$totalSkipped} skip.");

        return self::SUCCESS;
    }
}