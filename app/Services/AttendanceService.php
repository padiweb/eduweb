<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Attendance;
use App\Models\AttendanceValidation;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    // ── 1. BUKA / PERBARUI SESI ────────────────────────────────────────────

    public function openOrRefreshSession(
        School    $school,
        Classroom $classroom,
        User      $openedBy
    ): array {
        $session = AttendanceSession::where('classroom_id', $classroom->id)
            ->whereDate('session_date', today())
            ->first();

        $plainToken = Str::random(40);
        $tokenHash  = hash('sha256', $plainToken);

        if ($session) {
            $session->update([
                'opened_by'       => $openedBy->id,
                'qr_token_hash'   => $tokenHash,
                'qr_generated_at' => now(),
                'is_closed'       => false,
                'closed_at'       => null,
            ]);
        } else {
            $session = AttendanceSession::create([
                'school_id'        => $school->id,
                'classroom_id'     => $classroom->id,
                'opened_by'        => $openedBy->id,
                'auto_created'     => false,
                'session_date'     => today(),
                'qr_token_hash'    => $tokenHash,
                'qr_generated_at'  => now(),
                'open_time'        => $school->school_start_time,
                'close_time'       => $school->attendance_close_time,
                'late_after'       => $school->late_threshold_time,
                'school_latitude'  => $school->latitude,
                'school_longitude' => $school->longitude,
                'radius_meters'    => $school->attendance_radius_meters,
            ]);
        }

        return [
            'session'     => $session->fresh(),
            'plain_token' => $plainToken,
        ];
    }

    // ── 2. REFRESH TOKEN (untuk QR update) ─────────────────────────────────

    public function refreshToken(AttendanceSession $session): string
    {
        $plainToken = Str::random(40);
        $tokenHash  = hash('sha256', $plainToken);

        $session->update([
            'qr_token_hash'   => $tokenHash,
            'qr_generated_at' => now(),
            'is_closed'       => false,
        ]);

        return $plainToken;
    }

    // ── 3. PROSES SCAN SISWA ───────────────────────────────────────────────

    public function processStudentScan(
        string $plainToken,
        User   $student,
        float  $latitude,
        float  $longitude,
        float  $gpsAccuracy,
        string $ipAddress,
        string $userAgent
    ): Attendance {
        $tokenHash = hash('sha256', $plainToken);
        $session   = AttendanceSession::where('qr_token_hash', $tokenHash)
            ->whereDate('session_date', today())
            ->first();

        if (! $session) {
            throw new \RuntimeException('QR Code tidak valid atau sudah kedaluwarsa. Minta guru memperbarui QR.');
        }

        if ($session->is_closed) {
            throw new \RuntimeException('Sesi absensi sudah ditutup.');
        }

        $isEnrolled = $session->classroom->students()
            ->where('users.id', $student->id)
            ->exists();

        if (! $isEnrolled) {
            throw new \RuntimeException('Kamu tidak terdaftar di kelas ini.');
        }

        $alreadyScanned = Attendance::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyScanned) {
            throw new \RuntimeException('Kamu sudah melakukan absensi hari ini.');
        }

        $distance = $this->calculateDistance(
            $latitude, $longitude,
            (float) $session->school_latitude,
            (float) $session->school_longitude
        );

        if ($distance > $session->radius_meters) {
            throw new \RuntimeException(
                sprintf(
                    'Lokasi kamu terlalu jauh dari sekolah (%.0fm). Absensi hanya bisa dilakukan dalam radius %dm dari sekolah.',
                    $distance,
                    $session->radius_meters
                )
            );
        }

        if ($gpsAccuracy > 150) {
            throw new \RuntimeException('Sinyal GPS terlalu lemah. Pindah ke area terbuka dan coba lagi.');
        }

        $now        = now()->format('H:i:s');
        $isLateScan = $now > $session->close_time;
        $status     = $now > $session->late_after ? 'terlambat' : 'hadir';

        if ($now < $session->open_time) {
            throw new \RuntimeException(
                'Absensi belum dibuka. Scan QR mulai jam ' .
                substr($session->open_time, 0, 5) . '.'
            );
        }

        return DB::transaction(function () use (
            $session, $student, $status, $isLateScan,
            $latitude, $longitude, $gpsAccuracy, $distance,
            $ipAddress, $userAgent
        ) {
            $attendance = Attendance::create([
                'school_id'            => $session->school_id,
                'session_id'           => $session->id,
                'student_id'           => $student->id,
                'status'               => $isLateScan ? 'terlambat' : $status,
                'scan_latitude'        => $latitude,
                'scan_longitude'       => $longitude,
                'gps_accuracy'         => $gpsAccuracy,
                'distance_from_school' => $distance,
                'is_within_radius'     => true,
                'scanned_at'           => now(),
                'ip_address'           => $ipAddress,
                'user_agent'           => $userAgent,
                'is_late_scan'         => $isLateScan,
            ]);

            if ($attendance->status === 'terlambat') {
                app(\App\Services\ViolationService::class)
                    ->createAttendanceViolation($attendance, 'absen_terlambat');
            }

            return $attendance;
        });
    }

    // ── 4. INPUT MANUAL GURU ───────────────────────────────────────────────

    public function manualEntry(
        AttendanceSession $session,
        int               $studentId,
        string            $status,
        string            $reason,
        User              $teacher,
        ?string           $permissionReason = null
    ): Attendance {
        return DB::transaction(function () use (
            $session, $studentId, $status, $reason, $teacher, $permissionReason
        ) {
            // Ambil status lama jika sudah ada
            $existing = Attendance::where('session_id', $session->id)
                ->where('student_id', $studentId)
                ->first();

            $oldStatus = $existing?->status;

            $attendance = Attendance::updateOrCreate(
                ['session_id' => $session->id, 'student_id' => $studentId],
                [
                    'school_id'         => $session->school_id,
                    'status'            => $status,
                    'scanned_at'        => now(),
                    'is_manual_entry'   => true,
                    'entered_by'        => $teacher->id,
                    'entry_reason'      => $reason,
                    'entry_at'          => now(),
                    'permission_reason' => $permissionReason,
                ]
            );

            // Sync violation jika status berubah
            if ($oldStatus !== $status) {
                app(\App\Services\ViolationService::class)
                    ->syncAttendanceViolation($attendance->fresh(), $status);
            }

            $this->logActivity('attendance.manual_entry', $teacher, [
                'session_id' => $session->id,
                'student_id' => $studentId,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'reason'     => $reason,
            ]);

            return $attendance;
        });
    }

    // ── 5. ROLL CALL ───────────────────────────────────────────────────────

    public function conductRollCall(
        AttendanceSession $session,
        array             $presentStudentIds,
        array             $absentStudentIds,
        User              $teacher,
        ?string           $subjectName = null,
        ?string           $notes = null
    ): void {
        DB::transaction(function () use (
            $session, $presentStudentIds, $absentStudentIds,
            $teacher, $subjectName, $notes
        ) {
            // ── Roll call: catat alfa TANPA buat violation dulu ──
            // Violation baru dibuat saat sesi DITUTUP (manual atau otomatis).
            // Siswa masih bisa absen sebelum sesi ditutup (lemah sinyal, dll).
            foreach ($absentStudentIds as $studentId) {
                Attendance::updateOrCreate(
                    ['session_id' => $session->id, 'student_id' => $studentId],
                    [
                        'school_id'       => $session->school_id,
                        'status'          => 'alfa',
                        'scanned_at'      => now(),
                        'is_manual_entry' => true,
                        'entered_by'      => $teacher->id,
                        'entry_reason'    => 'Tidak hadir saat roll call oleh ' . $teacher->name,
                        'entry_at'        => now(),
                        'violation_created' => false, // reset agar bisa dapat poin saat tutup sesi
                    ]
                );
            }

            $enrolledIds  = $session->classroom->students()->pluck('users.id');
            $recordedIds  = $session->attendances()->pluck('student_id');
            $stillMissing = $enrolledIds->diff($recordedIds)->diff(collect($presentStudentIds));

            foreach ($stillMissing as $studentId) {
                Attendance::create([
                    'school_id'       => $session->school_id,
                    'session_id'      => $session->id,
                    'student_id'      => $studentId,
                    'status'          => 'alfa',
                    'scanned_at'      => now(),
                    'is_manual_entry' => true,
                    'entered_by'      => $teacher->id,
                    'entry_reason'    => 'Tidak hadir saat roll call oleh ' . $teacher->name,
                    'entry_at'        => now(),
                    'violation_created' => false,
                ]);
            }

            AttendanceValidation::create([
                'session_id'   => $session->id,
                'teacher_id'   => $teacher->id,
                'subject_name' => $subjectName,
                'validated_at' => now(),
                'notes'        => $notes,
            ]);

            if (! $session->roll_call_done) {
                $session->update([
                    'roll_call_done' => true,
                    'roll_call_by'   => $teacher->id,
                    'roll_call_at'   => now(),
                ]);
            }
        });
    }

    // ── 6. AUTO-ALFA SAAT SESI DITUTUP ────────────────────────────────────
    // Ini dipanggil saat sesi DITUTUP (manual oleh guru atau otomatis sistem).
    // Pada titik ini, semua yang masih alfa mendapat poin pelanggaran.

    public function autoAlfaOnClose(AttendanceSession $session): void
    {
        $enrolledIds = $session->classroom->students()->pluck('users.id');

        // Ambil semua attendance yang ada di sesi ini
        $existingAttendances = $session->attendances()->get()->keyBy('student_id');

        // Siswa yang belum ada record sama sekali → buat alfa baru
        $presentIds = $existingAttendances->pluck('student_id');
        $missingIds = $enrolledIds->diff($presentIds);

        DB::transaction(function () use ($session, $missingIds, $existingAttendances) {

            // 1. Buat record alfa untuk yang belum ada sama sekali
            foreach ($missingIds as $studentId) {
                $att = Attendance::create([
                    'school_id'       => $session->school_id,
                    'session_id'      => $session->id,
                    'student_id'      => $studentId,
                    'status'          => 'alfa',
                    'scanned_at'      => null,
                    'is_manual_entry' => true,
                    'entry_reason'    => 'Otomatis alfa saat sesi ditutup',
                    'entry_at'        => now(),
                ]);
                $this->createAlfaViolation($att, $session);
            }

            // 2. Buat violation untuk yang SUDAH alfa dari roll call (violation_created = false)
            //    Ini menangani kasus: siswa di-roll call alfa, tapi sesi baru ditutup sekarang
            foreach ($existingAttendances as $att) {
                if ($att->status === 'alfa' && ! $att->violation_created) {
                    $this->createAlfaViolation($att, $session);
                }
            }
        });
    }

    // ── 7. REKAP ───────────────────────────────────────────────────────────

    public function getMonthlyRecap(User $student, int $month, int $year): array
    {
        $records = Attendance::where('student_id', $student->id)
            ->whereHas('session', fn($q) =>
                $q->whereMonth('session_date', $month)
                  ->whereYear('session_date', $year)
            )
            ->with('session')
            ->orderBy('scanned_at')
            ->get();

        return [
            'hadir'     => $records->where('status', 'hadir')->count(),
            'terlambat' => $records->where('status', 'terlambat')->count(),
            'izin'      => $records->where('status', 'izin')->count(),
            'sakit'     => $records->where('status', 'sakit')->count(),
            'alfa'      => $records->where('status', 'alfa')->count(),
            'total'     => $records->count(),
            'records'   => $records,
        ];
    }

    public function getSemesterRecap(User $student, int $academicYearId): array
    {
        $records = Attendance::where('student_id', $student->id)
            ->whereHas('session.classroom', fn($q) =>
                $q->where('academic_year_id', $academicYearId)
            )
            ->get();

        $total      = $records->count();
        $hadirCount = $records->whereIn('status', ['hadir', 'terlambat'])->count();

        return [
            'hadir'           => $records->where('status', 'hadir')->count(),
            'terlambat'       => $records->where('status', 'terlambat')->count(),
            'izin'            => $records->where('status', 'izin')->count(),
            'sakit'           => $records->where('status', 'sakit')->count(),
            'alfa'            => $records->where('status', 'alfa')->count(),
            'total'           => $total,
            'attendance_rate' => $total > 0 ? round(($hadirCount / $total) * 100, 1) : 0,
        ];
    }

    // ── PRIVATE HELPERS ────────────────────────────────────────────────────

    private function createAlfaViolation(Attendance $attendance, AttendanceSession $session): void
    {
        try {
            $category = ViolationCategory::firstOrCreate(
                ['school_id' => $session->school_id, 'name' => 'Tidak Hadir Tanpa Keterangan (Alfa)'],
                ['severity' => 'sedang', 'default_points' => 3]
            );

            Violation::create([
                'school_id'     => $session->school_id,
                'student_id'    => $attendance->student_id,
                'category_id'   => $category->id,
                'reported_by'   => $session->opened_by ?? auth()->id() ?? 1,
                'attendance_id' => $attendance->id,
                'incident_date' => $session->session_date,
                'description'   => 'Tidak hadir (alfa) pada ' . $session->session_date->translatedFormat('l, d F Y') .
                                   ' — ' . $session->classroom->name,
                'points'        => $category->default_points,
                'source'        => 'auto_attendance',
            ]);

            $attendance->update(['violation_created' => true]);

        } catch (\Throwable $e) {
            Log::warning('Gagal buat pelanggaran alfa: ' . $e->getMessage());
        }
    }

    private function createLateViolation(Attendance $attendance, AttendanceSession $session): void
    {
        try {
            $category = ViolationCategory::where('school_id', $session->school_id)
                ->where('name', 'Keterlambatan')
                ->first();

            if (! $category) return;

            Violation::create([
                'school_id'     => $session->school_id,
                'student_id'    => $attendance->student_id,
                'category_id'   => $category->id,
                'reported_by'   => $session->opened_by ?? 1,
                'incident_date' => today(),
                'description'   => 'Absensi terlambat (scan: ' . now()->format('H:i') . ')',
                'points'        => $category->default_points,
                'source'        => 'auto_attendance',
            ]);

            $attendance->update(['violation_created' => true]);

        } catch (\Throwable $e) {
            Log::warning('Gagal buat pelanggaran otomatis: ' . $e->getMessage());
        }
    }

    public function calculateDistance(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float {
        $earthRadius = 6371000;
        $dLat        = deg2rad($lat2 - $lat1);
        $dLon        = deg2rad($lon2 - $lon1);
        $a           = sin($dLat / 2) ** 2
                     + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function logActivity(string $action, User $actor, array $data = []): void
    {
        try {
            \App\Models\ActivityLog::create([
                'school_id'  => $actor->school_id,
                'user_id'    => $actor->id,
                'user_name'  => $actor->name,
                'user_role'  => $actor->role,
                'action'     => $action,
                'new_values' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLog gagal: ' . $e->getMessage());
        }
    }
}
