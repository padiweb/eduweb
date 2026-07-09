<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherRewardPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherAttendanceAdminController extends Controller
{
    // ── Monitor absensi hari ini ──────────────────────────────────────────

    public function index(Request $request)
    {
        $school = auth()->user()->school;
        $date   = $request->get('date', today()->format('Y-m-d'));

        $sessions = TeacherAttendanceSession::where('school_id', $school->id)
            ->where('session_date', $date)
            ->with(['attendances.teacher'])
            ->orderBy('session_type')
            ->get();

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas', 'kesiswaan', 'admin', 'bendahara'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ringkasan per sesi
        $summary = $sessions->map(function ($session) use ($teachers) {
            $attended   = $session->attendances->pluck('teacher_id');
            $notAttended = $teachers->whereNotIn('id', $attended);
            return [
                'session'      => $session,
                'hadir'        => $session->attendances->whereIn('status', ['hadir'])->count(),
                'terlambat'    => $session->attendances->where('status', 'terlambat')->count(),
                'izin'         => $session->attendances->where('status', 'izin')->count(),
                'sakit'        => $session->attendances->where('status', 'sakit')->count(),
                'dinas'        => $session->attendances->where('status', 'dinas')->count(),
                'alfa'         => $notAttended->count(),
                'not_attended' => $notAttended,
            ];
        });

        return view('admin.teacher-attendance.index', compact(
            'sessions', 'summary', 'teachers', 'date'
        ));
    }

    // ── Input manual absensi guru ─────────────────────────────────────────

    public function manualEntry(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'session_id' => ['required', 'exists:teacher_attendance_sessions,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'status'     => ['required', 'in:hadir,terlambat,izin,sakit,dinas,alfa'],
            'notes'      => ['nullable', 'string', 'max:255'],
        ]);

        $session = TeacherAttendanceSession::findOrFail($validated['session_id']);
        if ($session->school_id !== $school->id) abort(403);

        TeacherAttendance::updateOrCreate(
            ['session_id' => $session->id, 'teacher_id' => $validated['teacher_id']],
            [
                'school_id'       => $school->id,
                'status'          => $validated['status'],
                'notes'           => $validated['notes'] ?? null,
                'is_manual_entry' => true,
                'scanned_at'      => now(),
            ]
        );

        return back()->with('success', 'Absensi guru berhasil dicatat.');
    }

    // ── Rekap poin reward semua guru ──────────────────────────────────────

    public function rewards(Request $request)
    {
        $school = auth()->user()->school;
        $month  = $request->get('month', now()->month);
        $year   = $request->get('year', now()->year);

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', ['guru', 'wali_kelas', 'kesiswaan', 'admin', 'bendahara'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($teacher) use ($school, $month, $year) {
                $points = TeacherRewardPoint::where('school_id', $school->id)
                    ->where('teacher_id', $teacher->id)
                    ->whereMonth('point_date', $month)
                    ->whereYear('point_date', $year)
                    ->get();

                return [
                    'teacher'           => $teacher,
                    'total'             => $points->sum('points'),
                    'absen_tepat_waktu' => $points->where('type', 'absen_tepat_waktu')->sum('points'),
                    'isi_jurnal'        => $points->where('type', 'isi_jurnal')->sum('points'),
                    'bonus'             => $points->where('type', 'bonus')->sum('points'),
                    'pengurang'         => $points->where('type', 'pengurang')->sum('points'),
                ];
            })
            ->sortByDesc('total');

        return view('admin.teacher-attendance.rewards', compact('teachers', 'month', 'year'));
    }

    // ── Tambah/kurang poin manual ─────────────────────────────────────────

    public function addRewardPoint(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'teacher_id'  => ['required', 'exists:users,id'],
            'type'        => ['required', 'in:bonus,pengurang'],
            'points'      => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        TeacherRewardPoint::create([
            'school_id'   => $school->id,
            'teacher_id'  => $validated['teacher_id'],
            'type'        => $validated['type'],
            'points'      => $validated['type'] === 'pengurang' ? -abs($validated['points']) : $validated['points'],
            'description' => $validated['description'],
            'point_date'  => today(),
        ]);

        return back()->with('success', 'Poin reward berhasil ditambahkan.');
    }

    // ── Generate/refresh QR token sekolah ────────────────────────────────

    public function refreshQr()
    {
        $school = auth()->user()->school;
        $school->update(['teacher_qr_token' => Str::random(32)]);

        return back()->with('success', 'QR absensi guru berhasil diperbarui.');
    }
}
