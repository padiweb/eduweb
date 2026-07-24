<?php

namespace App\Console\Commands;

use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinPlacement;
use App\Models\Violation;
use App\Models\ViolationCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPrakerinJurnal extends Command
{
    protected $signature   = 'prakerin:check-jurnal {--date= : Tanggal yang dicek (Y-m-d), default kemarin}';
    protected $description = 'Cek siswa prakerin yang tidak mengisi jurnal dan berikan poin pelanggaran';

    public function handle(): void
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : today()->subDay(); // default: cek kemarin

        $dateStr = $date->format('Y-m-d');
        $this->info("Cek jurnal prakerin untuk: {$dateStr}");

        // Lewati hari libur nasional (Sabtu/Minggu)
        if ($date->isWeekend()) {
            $this->info("Hari libur (Sabtu/Minggu), skip.");
            return;
        }

        // Ambil semua placement aktif yang ada absen masuk pada tanggal tersebut
        $placements = PrakerinPlacement::where('is_active', true)
            ->whereHas('attendances', fn($q) =>
                $q->where('attendance_date', $dateStr)
                  ->where('type', 'check_in')
                  ->whereIn('status', ['hadir', 'terlambat'])
            )
            ->with(['student.school', 'period'])
            ->get();

        $counted = 0;
        $skipped = 0;

        foreach ($placements as $placement) {
            // Cek apakah sudah ada jurnal untuk tanggal ini
            $hasJournal = PrakerinJournal::where('placement_id', $placement->id)
                ->where('journal_date', $dateStr)
                ->exists();

            if ($hasJournal) {
                $skipped++;
                continue;
            }

            // Cek apakah sudah ada pelanggaran untuk hari ini
            $alreadyViolation = Violation::where('student_id', $placement->student_id)
                ->where('source', 'prakerin_no_journal')
                ->whereDate('incident_date', $date)
                ->exists();

            if ($alreadyViolation) {
                $skipped++;
                continue;
            }

            $school = $placement->student->school;
            if (! $school) continue;

            // Ambil atau buat kategori pelanggaran
            $category = ViolationCategory::firstOrCreate(
                ['school_id' => $school->id, 'name' => 'Tidak Mengisi Jurnal Prakerin'],
                ['severity' => 'ringan', 'default_points' => $school->prakerin_points_no_journal ?? 2]
            );

            $points = $school->prakerin_points_no_journal ?? $category->default_points ?? 2;

            try {
                Violation::create([
                    'school_id'     => $school->id,
                    'student_id'    => $placement->student_id,
                    'category_id'   => $category->id,
                    'reported_by'   => 1, // system
                    'incident_date' => $date,
                    'description'   => 'Tidak mengisi jurnal harian prakerin pada '
                        . $date->translatedFormat('l, d F Y'),
                    'points'        => $points,
                    'source'        => 'prakerin_no_journal',
                ]);
                $counted++;
                $this->line("  Pelanggaran: {$placement->student->name} ({$dateStr})");
            } catch (\Throwable $e) {
                Log::error("CheckPrakerinJurnal gagal: " . $e->getMessage());
            }
        }

        $this->info("Selesai: {$counted} pelanggaran dibuat, {$skipped} dilewati.");
    }
}
