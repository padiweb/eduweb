<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Attendance;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function openSession(
        School $school,
        User   $teacher,
        int    $classroomId,
        int    $subjectId,
        ?int   $scheduleId,
        int    $durationMinutes = 10
    ): array {
        $existing = AttendanceSession::where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->whereDate('session_date', today())
            ->where('is_closed', false)
            ->where('token_expires_at', '>', now())
            ->first();

        if ($existing) {
            throw new \RuntimeException('Sesi absensi aktif untuk kelas ini sudah ada. Tutup sesi sebelumnya terlebih dahulu.');
        }

        $plainToken = Str::random(32);
        $tokenHash  = hash('sha256', $plainToken);

        $session = AttendanceSession::create([
            'school_id'        => $school->id,
            'classroom_id'     => $classroomId,
            'subject_id'       => $subjectId,
            'teacher_id'       => $teacher->id,
            'schedule_id'      => $scheduleId,
            'session_date'     => today(),
            'qr_token'         => Str::random(16),
            'qr_token_hash'    => $tokenHash,
            'token_expires_at' => now()->addMinutes($durationMinutes),
            'school_latitude'  => $school->latitude,
            'school_longitude' => $school->longitude,
            'radius_meters'    => $school->attendance_radius_meters,
        ]);

        return [
            'session'     => $session,
            'plain_token' => $plainToken,
        ];
    }

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
        $session   = AttendanceSession::where('qr_token_hash', $tokenHash)->first();

        if (! $session) {
            throw new \RuntimeException('QR Code tidak valid. Minta guru menampilkan ulang.');
        }

        if ($session->is_closed || now()->isAfter($session->token_expires_at)) {
            throw new \RuntimeException('Sesi absensi sudah ditutup atau kedaluwarsa.');
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
            throw new \RuntimeException('Kamu sudah melakukan absensi untuk sesi ini.');
        }

        $distance       = $this->calculateDistance(
            $latitude, $longitude,
            (float) $session->school_latitude,
            (float) $session->school_longitude
        );

        if ($distance > $session->radius_meters) {
            throw new \RuntimeException(
                'Lokasi kamu terlalu jauh dari sekolah (' . round($distance) . 'm). ' .
                'Absensi hanya bisa dilakukan dalam radius ' . $session->radius_meters . 'm dari sekolah.'
            );
        }

        if ($gpsAccuracy > 150) {
            throw new \RuntimeException('Sinyal GPS terlalu lemah. Pastikan GPS aktif dan berada di area terbuka.');
        }

        $school = $session->school;
        $status = now()->format('H:i:s') > $school->late_threshold_time ? 'terlambat' : 'hadir';

        return DB::transaction(function () use (
            $session, $student, $status,
            $latitude, $longitude, $gpsAccuracy, $distance,
            $ipAddress, $userAgent
        ) {
            return Attendance::create([
                'school_id'            => $session->school_id,
                'session_id'           => $session->id,
                'student_id'           => $student->id,
                'status'               => $status,
                'scan_latitude'        => $latitude,
                'scan_longitude'       => $longitude,
                'gps_accuracy'         => $gpsAccuracy,
                'distance_from_school' => $distance,
                'is_within_radius'     => true,
                'scanned_at'           => now(),
                'ip_address'           => $ipAddress,
                'user_agent'           => $userAgent,
            ]);
        });
    }

    public function manualOverride(
        AttendanceSession $session,
        int               $studentId,
        string            $newStatus,
        string            $reason,
        User              $teacher
    ): Attendance {
        return DB::transaction(function () use ($session, $studentId, $newStatus, $reason, $teacher) {
            $attendance = Attendance::firstOrNew([
                'session_id' => $session->id,
                'student_id' => $studentId,
            ]);

            $attendance->fill([
                'school_id'          => $session->school_id,
                'status'             => $newStatus,
                'scanned_at'         => $attendance->scanned_at ?? now(),
                'is_manual_override' => true,
                'override_by'        => $teacher->id,
                'override_reason'    => $reason,
                'override_at'        => now(),
            ])->save();

            return $attendance;
        });
    }

    public function completeRollCall(AttendanceSession $session, User $teacher): void
    {
        $enrolledIds = $session->classroom->students()->pluck('users.id');
        $presentIds  = $session->attendances()->pluck('student_id');
        $missingIds  = $enrolledIds->diff($presentIds);

        DB::transaction(function () use ($session, $teacher, $missingIds) {
            foreach ($missingIds as $studentId) {
                Attendance::create([
                    'school_id'          => $session->school_id,
                    'session_id'         => $session->id,
                    'student_id'         => $studentId,
                    'status'             => 'alfa',
                    'scanned_at'         => now(),
                    'is_manual_override' => true,
                    'override_by'        => $teacher->id,
                    'override_reason'    => 'Tidak hadir saat roll call',
                    'override_at'        => now(),
                ]);
            }

            $session->update([
                'roll_call_done' => true,
                'roll_call_at'   => now(),
            ]);
        });
    }

    public function refreshToken(AttendanceSession $session, int $durationMinutes = 10): string
    {
        $plainToken = Str::random(32);
        $tokenHash  = hash('sha256', $plainToken);

        $session->update([
            'qr_token_hash'    => $tokenHash,
            'token_expires_at' => now()->addMinutes($durationMinutes),
            'is_closed'        => false,
        ]);

        return $plainToken;
    }

    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat        = deg2rad($lat2 - $lat1);
        $dLon        = deg2rad($lon2 - $lon1);
        $a           = sin($dLat / 2) ** 2
                     + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}