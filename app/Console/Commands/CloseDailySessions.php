<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\School;
use App\Services\AttendanceService;
use Illuminate\Console\Command;

class CloseDailySessions extends Command
{
    protected $signature   = 'attendance:close-daily-sessions
                             {--school= : ID sekolah spesifik}
                             {--date=   : Tanggal Y-m-d (default hari ini)}';

    protected $description = 'Tutup sesi absensi yang masih aktif dan alfa-kan siswa yang belum absen';

    public function handle(AttendanceService $service): int
    {
        $date     = $this->option('date') ? now()->parse($this->option('date')) : now();
        $schoolId = $this->option('school');

        // Ambil semua sesi yang masih aktif hari ini
        $sessions = AttendanceSession::where('is_closed', false)
            ->whereDate('session_date', $date->toDateString())
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->with(['classroom.students', 'attendances'])
            ->get();

        $totalClosed = 0;
        $totalAlfa   = 0;

        foreach ($sessions as $session) {
            // Alfa-kan siswa yang belum absen
            $alfaCount = $this->autoAlfa($session);
            $totalAlfa += $alfaCount;

            // Tutup sesi
            $session->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);

            $totalClosed++;
            $this->line("  Tutup: {$session->classroom->name} — {$alfaCount} siswa alfa");
        }

        $this->info("Selesai: {$totalClosed} sesi ditutup, {$totalAlfa} siswa ditandai alfa.");

        return self::SUCCESS;
    }

    private function autoAlfa(AttendanceSession $session): int
    {
        $enrolledIds = $session->classroom->students()->pluck('users.id');
        $presentIds  = $session->attendances()->pluck('student_id');
        $missingIds  = $enrolledIds->diff($presentIds);

        foreach ($missingIds as $studentId) {
            Attendance::create([
                'school_id'       => $session->school_id,
                'session_id'      => $session->id,
                'student_id'      => $studentId,
                'status'          => 'alfa',
                'scanned_at'      => null,
                'is_manual_entry' => true,
                'entry_reason'    => 'Otomatis alfa saat sesi ditutup oleh sistem',
                'entry_at'        => now(),
            ]);
        }

        return $missingIds->count();
    }
}
