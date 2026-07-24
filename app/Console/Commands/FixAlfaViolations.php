<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Services\ViolationService;
use Illuminate\Console\Command;

/**
 * Retroactive fix: buat violation untuk attendance alfa yang belum punya violation.
 * Jalankan sekali: php artisan attendance:fix-alfa-violations
 */
class FixAlfaViolations extends Command
{
    protected $signature   = 'attendance:fix-alfa-violations';
    protected $description = 'Buat poin pelanggaran untuk siswa yang sudah alfa tapi belum mendapat poin';

    public function handle(ViolationService $vs): int
    {
        // Ambil semua attendance alfa yang belum punya violation
        $attendances = Attendance::where('status', 'alfa')
            ->where('violation_created', false)
            ->whereHas('session', fn($q) => $q->where('is_closed', true)) // hanya sesi yang sudah ditutup
            ->with(['session.classroom', 'school'])
            ->get();

        $this->info("Ditemukan {$attendances->count()} alfa belum dapat poin.");

        $fixed = 0;
        foreach ($attendances as $att) {
            $result = $vs->createAttendanceViolation($att, 'absen_alfa');
            if ($result) {
                $fixed++;
                $this->line("  Fix: student_id={$att->student_id} tanggal={$att->session->session_date->format('Y-m-d')}");
            }
        }

        $this->info("Selesai. {$fixed} poin pelanggaran dibuat.");
        return self::SUCCESS;
    }
}
