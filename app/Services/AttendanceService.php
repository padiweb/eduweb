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
    // ── 1. BUKA / PERBARUI SESI (per kelas per hari) ───────────────────────

    /**
     * Buka sesi absensi untuk satu kelas hari ini.
     * Jika sesi sudah ada, perbarui QR token-nya saja.
     * Kembalikan plain token — hanya ada sekali, tidak disimpan di DB.
     */
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
            // Sesi sudah ada — perbarui QR saja
            $session->update([
                'opened_by'       => $openedBy->id,
                'qr_token_hash'   => $tokenHash,
                'qr_generated_at' => now(),
                'is_closed'       => false,
                'closed_at'       => null,
            ]);
        } else {
            // Buka sesi baru
            $session = AttendanceSession::create([
                'school_id'        => $school->id,
                'classroom_id'     => $classroom->id,
                'opened_by'        => $openedBy->id,
                'session_date'     => today(),
                'qr_token_hash'    => $tokenHash,
                'qr_generated_at'  => now(),
                'open_time'        => $school->school_start_time,  // e.g. "06:30:00"
                'close_time'       => $school->attendance_close_time, // e.g. "08:00:00"
                'late_after'       => $school->late_threshold_time, // e.g. "07:15:00"
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

    // ── 2. PROSES SCAN SISWA ───────────────────────────────────────────────

    /**
     * Proses scan QR dari siswa.
     * Validasi berlapis: token → GPS → duplikat → jam.
     */
    public function processStudentScan(
        string $plainToken,
        User   $student,
        float  $latitude,
        float  $longitude,
        float  $gpsAccuracy,
        string $ipAddress,
        string $userAgent
    ): Attendance {
        // ── Cari sesi dari token ──
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

        // ── Validasi: siswa terdaftar di kelas ini ──
        $isEnrolled = $session->classroom->students()
            ->where('users.id', $student->id)
            ->exists();

        if (! $isEnrolled) {
            throw new \RuntimeException('Kamu tidak terdaftar di kelas ini.');
        }

        // ── Validasi: belum absen hari ini ──
        $alreadyScanned = Attendance::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyScanned) {
            throw new \RuntimeException('Kamu sudah melakukan absensi hari ini.');
        }

        // ── Validasi: GPS dalam radius sekolah ──
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

        // ── Tentukan status berdasar jam scan ──
        $now        = now()->format('H:i:s');
        $isLateScan = $now > $session->close_time;  // scan setelah jam tutup
        $status     = $now > $session->late_after ? 'terlambat' : 'hadir';

        // Jika scan di luar jam aktif (sebelum open atau setelah close)
        // tetap catat tapi flag sebagai late_scan untuk proses pelanggaran
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

            // Jika scan di luar jam → buat pelanggaran otomatis
            if ($isLateScan) {
                $this->createLateViolation($attendance, $session);
            }

            return $attendance;
        });
    }

    // ── 3. INPUT MANUAL OLEH GURU ──────────────────────────────────────────

    /**
     * Guru input absen manual untuk siswa tertentu.
     * Bisa untuk: HP rusak, sakit, izin, alfa, koreksi.
     * Alasan wajib diisi — masuk audit trail.
     */
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
            $attendance = Attendance::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                ],
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

            // Log ke activity_logs
            $this->logActivity('attendance.manual_entry', $teacher, [
                'session_id'  => $session->id,
                'student_id'  => $studentId,
                'status'      => $status,
                'reason'      => $reason,
            ]);

            return $attendance;
        });
    }

    // ── 4. ROLL CALL / VALIDASI GURU ───────────────────────────────────────

    /**
     * Guru lakukan roll call — panggil nama siswa satu per satu.
     * Siswa yang tidak hadir secara fisik diubah ke alfa.
     * Guru mana saja yang mengajar di kelas itu bisa melakukan ini.
     */
    public function conductRollCall(
        AttendanceSession $session,
        array             $presentStudentIds,   // ID siswa yang terbukti hadir fisik
        array             $absentStudentIds,    // ID siswa yang tidak hadir saat dipanggil
        User              $teacher,
        ?string           $subjectName = null,
        ?string           $notes = null
    ): void {
        DB::transaction(function () use (
            $session, $presentStudentIds, $absentStudentIds, $teacher, $subjectName, $notes
        ) {
            // Siswa yang scan tapi ternyata tidak hadir fisik → ubah ke alfa
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
                    ]
                );
            }

            // Siswa yang belum absen sama sekali → alfa
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
                ]);
            }

            // Catat record validasi
            AttendanceValidation::create([
                'session_id'   => $session->id,
                'teacher_id'   => $teacher->id,
                'subject_name' => $subjectName,
                'validated_at' => now(),
                'notes'        => $notes,
            ]);

            // Update flag roll_call_done jika belum
            if (! $session->roll_call_done) {
                $session->update([
                    'roll_call_done' => true,
                    'roll_call_by'   => $teacher->id,
                    'roll_call_at'   => now(),
                ]);
            }
        });

        $this->logActivity('attendance.roll_call', $teacher, [
            'session_id'      => $session->id,
            'present_count'   => count($presentStudentIds),
            'absent_count'    => count($absentStudentIds),
        ]);
    }

    // ── 5. AUTO-ALFA saat sesi ditutup ─────────────────────────────────────

    /**
     * Saat sesi ditutup, siswa yang tidak absen sama sekali → alfa otomatis.
     */
    public function autoAlfaOnClose(AttendanceSession $session): void
    {
        $enrolledIds = $session->classroom->students()->pluck('users.id');
        $presentIds  = $session->attendances()->pluck('student_id');
        $missingIds  = $enrolledIds->diff($presentIds);

        DB::transaction(function () use ($session, $missingIds) {
            foreach ($missingIds as $studentId) {
                Attendance::create([
                    'school_id'       => $session->school_id,
                    'session_id'      => $session->id,
                    'student_id'      => $studentId,
                    'status'          => 'alfa',
                    'scanned_at'      => null,
                    'is_manual_entry' => true,
                    'entry_reason'    => 'Tidak absen — otomatis saat sesi ditutup',
                    'entry_at'        => now(),
                ]);
            }
        });
    }

    // ── 6. REKAP PER SISWA ─────────────────────────────────────────────────

    /**
     * Rekap absensi siswa per bulan.
     */
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
            'alfa'       => $records->where('status', 'alfa')->count(),
            'total'     => $records->count(),
            'records'   => $records,
        ];
    }

    /**
     * Rekap absensi siswa per semester.
     */
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

    private function createLateViolation(Attendance $attendance, AttendanceSession $session): void
    {
        try {
            $category = ViolationCategory::where('school_id', $session->school_id)
                ->where('name', 'Keterlambatan')
                ->first();

            if (! $category) return; // Kategori belum dibuat admin

            Violation::create([
                'school_id'    => $session->school_id,
                'student_id'   => $attendance->student_id,
                'category_id'  => $category->id,
                'reported_by'  => $session->opened_by,
                'incident_date'=> today(),
                'description'  => 'Absensi di luar jam yang ditentukan (scan: ' . now()->format('H:i') . ')',
                'points'       => $category->default_points,
                'source'       => 'auto_attendance',
            ]);

            $attendance->update(['violation_created' => true]);

        } catch (\Throwable $e) {
            Log::warning('Gagal buat pelanggaran otomatis: ' . $e->getMessage());
        }
    }

    /**
     * Haversine formula — jarak dua koordinat GPS dalam meter.
     */
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