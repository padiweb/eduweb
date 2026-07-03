<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class ManualOverrideController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function store(AttendanceSession $session, Request $request)
    {
        $teacher = auth()->user();

        if ($session->teacher_id !== $teacher->id && $teacher->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'new_status' => ['required', 'in:hadir,terlambat,izin,sakit,alfa'],
            'reason'     => ['required', 'string', 'min:5'],
        ]);

        try {
            $this->service->manualOverride(
                session:   $session,
                studentId: (int) $validated['student_id'],
                newStatus: $validated['new_status'],
                reason:    $validated['reason'],
                teacher:   $teacher,
            );

            return response()->json(['success' => true, 'message' => 'Absensi berhasil dikoreksi.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}