<?php

namespace App\Console\Commands;

use App\Models\PrakerinAbsence;
use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinPlacement;
use App\Services\ViolationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPrakerinViolations extends Command
{
    protected $signature   = 'prakerin:check-violations {--date= : Tanggal cek Y-m-d, default kemarin}';
    protected $description = 'Beri poin pelanggaran siswa prakerin yang tidak absen / tidak isi jurnal';

    public function __construct(private ViolationService $violationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $date    = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();
        $dateStr = $date->format('Y-m-d');

        $this->info("Cek pelanggaran prakerin: {$dateStr}");

        $placements = PrakerinPlacement::with(['student.school', 'location', 'period'])
            ->where('is_active', true)
            ->whereHas('period', fn($q) => $q->where('start_date', '<=', $dateStr)
                ->where('end_date', '>=', $dateStr)
                ->where('is_active', true))
            ->whereHas('student.school', fn($q) => $q->where('feature_prakerin', true))
            ->get();

        $total = 0;

        foreach ($placements as $placement) {
            // Cek tanggal efektif placement (bisa override dari periode)
            $start = $placement->start_date ?? $placement->period->start_date;
            $end   = $placement->end_date   ?? $placement->period->end_date;
            if (! $date->between($start, $end)) continue;

            $student = $placement->student;
            if (! $student) continue;

            // Skip jika ada izin/sakit/libur yang disetujui untuk hari ini
            $hasApprovedAbsence = PrakerinAbsence::where('placement_id', $placement->id)
                ->where('absence_date', $dateStr)
                ->where('status', 'approved')
                ->exists();
            if ($hasApprovedAbsence) continue;

            // Skip jika ada pengajuan izin/sakit/libur yang disetujui
            $hasApprovedAbsence = PrakerinAbsence::where('placement_id', $placement->id)
                ->where('absence_date', $dateStr)
                ->where('status', 'approved')
                ->exists();
            if ($hasApprovedAbsence) continue;

            // 1. Absen masuk
            $checkin = PrakerinAttendance::where('placement_id', $placement->id)
                ->where('attendance_date', $dateStr)
                ->where('type', 'check_in')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->first();

            if (! $checkin) {
                $v = $this->violationService->createPrakerinViolation($placement, 'prakerin_no_checkin', $dateStr);
                if ($v) { $total++; Log::info("Prakerin no-checkin: {$student->name} {$dateStr}"); }
            }

            // 2. Absen pulang
            $checkout = PrakerinAttendance::where('placement_id', $placement->id)
                ->where('attendance_date', $dateStr)
                ->where('type', 'check_out')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->first();

            if (! $checkout) {
                $v = $this->violationService->createPrakerinViolation($placement, 'prakerin_no_checkout', $dateStr);
                if ($v) { $total++; Log::info("Prakerin no-checkout: {$student->name} {$dateStr}"); }
            }

            // 3. Jurnal harian
            $journal = PrakerinJournal::where('placement_id', $placement->id)
                ->where('journal_date', $dateStr)
                ->where('status', 'submitted')
                ->first();

            if (! $journal) {
                $v = $this->violationService->createPrakerinViolation($placement, 'prakerin_no_journal', $dateStr);
                if ($v) { $total++; Log::info("Prakerin no-journal: {$student->name} {$dateStr}"); }
            }
        }

        $this->info("Selesai. Total pelanggaran: {$total}");
        return self::SUCCESS;
    }
}