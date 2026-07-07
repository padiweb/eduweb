<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViolationService
{
    // ── Poin default per sumber otomatis ──────────────────────────────────
    const POINTS = [
        'absen_terlambat'        => 1,
        'tugas_terlambat'        => 1,
        'tugas_tidak_kumpul'     => 2,
        'absen_alfa'             => 3,
    ];

    // ── 1. Buat pelanggaran otomatis dari absensi ─────────────────────────

    /**
     * Dipanggil saat siswa scan QR terlambat atau status jadi alfa.
     * source: 'absen_terlambat' | 'absen_alfa'
     */
    public function createAttendanceViolation(
        Attendance $attendance,
        string     $source
    ): ?Violation {
        try {
            $points   = self::POINTS[$source] ?? 1;
            $category = $this->getOrCreateAutoCategory($attendance->school_id, $source);

            if (! $category) return null;

            // Cek apakah sudah ada violation untuk attendance ini
            $exists = Violation::where('attendance_id', $attendance->id)
                ->where('source', $source)
                ->exists();

            if ($exists) return null;

            $violation = Violation::create([
                'school_id'     => $attendance->school_id,
                'student_id'    => $attendance->student_id,
                'category_id'   => $category->id,
                'reported_by'   => 1, // sistem
                'attendance_id' => $attendance->id,
                'incident_date' => today(),
                'description'   => $this->getAutoDescription($source, $attendance),
                'points'        => $points,
                'source'        => $source,
            ]);

            // Tandai attendance sudah dibuat violation
            $attendance->update(['violation_created' => true]);

            return $violation;

        } catch (\Throwable $e) {
            Log::warning('Gagal buat pelanggaran otomatis: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hapus pelanggaran otomatis dari absensi ketika status diubah.
     * Contoh: alfa → izin → poin 3 dihapus otomatis.
     */
    public function revokeAttendanceViolation(Attendance $attendance): int
    {
        $deleted = Violation::where('attendance_id', $attendance->id)
            ->whereIn('source', ['absen_terlambat', 'absen_alfa'])
            ->delete();

        if ($deleted > 0) {
            $attendance->update(['violation_created' => false]);
        }

        return $deleted;
    }

    /**
     * Update pelanggaran absensi saat status berubah.
     * Dipanggil dari AttendanceService::manualEntry().
     *
     * Logika:
     * - Jadi alfa → buat violation absen_alfa (hapus terlambat jika ada)
     * - Jadi terlambat → buat violation absen_terlambat (hapus alfa jika ada)
     * - Jadi hadir/izin/sakit → hapus semua violation absensi
     */
    public function syncAttendanceViolation(
        Attendance $attendance,
        string     $newStatus
    ): void {
        DB::transaction(function () use ($attendance, $newStatus) {
            // Hapus semua violation absensi lama dulu
            Violation::where('attendance_id', $attendance->id)
                ->whereIn('source', ['absen_terlambat', 'absen_alfa'])
                ->delete();

            $attendance->update(['violation_created' => false]);

            // Buat violation baru sesuai status
            if ($newStatus === 'alfa') {
                $this->createAttendanceViolation($attendance, 'absen_alfa');
            } elseif ($newStatus === 'terlambat') {
                $this->createAttendanceViolation($attendance, 'absen_terlambat');
            }
            // hadir, izin, sakit → tidak ada violation
        });
    }

    // ── 2. Buat pelanggaran manual oleh kesiswaan ─────────────────────────

    public function createManualViolation(
        int    $schoolId,
        int    $studentId,
        int    $categoryId,
        int    $reportedBy,
        string $description,
        int    $points,
        string $incidentDate,
        ?string $evidencePath = null
    ): Violation {
        return Violation::create([
            'school_id'     => $schoolId,
            'student_id'    => $studentId,
            'category_id'   => $categoryId,
            'reported_by'   => $reportedBy,
            'attendance_id' => null,
            'incident_date' => $incidentDate,
            'description'   => $description,
            'points'        => $points,
            'source'        => 'manual',
            'evidence_path' => $evidencePath,
        ]);
    }

    // ── 3. Rekap poin siswa ───────────────────────────────────────────────

    public function getStudentPoints(int $studentId): int
    {
        return Violation::where('student_id', $studentId)
            ->where('is_archived', false)
            ->sum('points');
    }

    public function getStudentViolations(
        int  $studentId,
        bool $includeArchived = false
    ) {
        return Violation::where('student_id', $studentId)
            ->when(! $includeArchived, fn($q) => $q->where('is_archived', false))
            ->with(['category', 'reportedBy'])
            ->orderByDesc('incident_date')
            ->orderByDesc('created_at')
            ->get();
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function getOrCreateAutoCategory(int $schoolId, string $source): ?ViolationCategory
    {
        $names = [
            'absen_terlambat'    => 'Keterlambatan',
            'absen_alfa'         => 'Alfa Tanpa Keterangan',
            'tugas_terlambat'    => 'Terlambat Mengumpulkan Tugas',
            'tugas_tidak_kumpul' => 'Tidak Mengumpulkan Tugas',
        ];

        $name = $names[$source] ?? $source;

        return ViolationCategory::firstOrCreate(
            ['school_id' => $schoolId, 'name' => $name],
            [
                'severity'       => $source === 'absen_alfa' ? 'sedang' : 'ringan',
                'default_points' => self::POINTS[$source] ?? 1,
            ]
        );
    }

    private function getAutoDescription(string $source, Attendance $attendance): string
    {
        return match ($source) {
            'absen_terlambat' => 'Terlambat absensi pada ' .
                $attendance->session->session_date->translatedFormat('l, d F Y') .
                ' (scan: ' . ($attendance->scanned_at?->format('H:i') ?? '-') . ')',
            'absen_alfa'      => 'Tidak hadir tanpa keterangan pada ' .
                $attendance->session->session_date->translatedFormat('l, d F Y'),
            default           => 'Pelanggaran otomatis: ' . $source,
        };
    }
}
