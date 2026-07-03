<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Illuminate\Console\Command;

class CloseExpiredSessions extends Command
{
    protected $signature   = 'attendance:close-expired-sessions';
    protected $description = 'Tutup sesi absensi yang sudah melewati jam tutup dan alfa-kan siswa yang tidak absen';

    public function handle(): int
    {
        $now = now();

        // Ambil semua sesi yang belum ditutup dan jam tutupnya sudah lewat
        $expiredSessions = AttendanceSession::where('is_closed', false)
            ->whereDate('session_date', today())
            ->get()
            ->filter(fn($session) => $now->format('H:i:s') > $session->close_time);

        $totalClosed = 0;
        $totalAlfa   = 0;

        foreach ($expiredSessions as $session) {
            // Alfa-kan semua siswa yang belum absen
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
                    'entry_reason'    => 'Alfa otomatis — tidak absen sampai jam tutup sesi',
                    'entry_at'        => now(),
                ]);
                $totalAlfa++;
            }

            // Tutup sesi
            $session->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);

            $totalClosed++;
            $this->line("  ✓ Ditutup: {$session->classroom->name} ({$missingIds->count()} alfa)");
        }

        $this->info("Selesai. Ditutup: {$totalClosed} sesi, Alfa: {$totalAlfa} siswa.");
        return self::SUCCESS;
    }
}
