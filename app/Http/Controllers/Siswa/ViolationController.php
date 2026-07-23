<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Violation;

class ViolationController extends Controller
{
    public function index()
    {
        $student = auth()->user();
        $school  = $student->school;

        $violations = Violation::where('student_id', $student->id)
            ->where('is_archived', false)
            ->with(['category'])
            ->orderByDesc('incident_date')
            ->orderByDesc('created_at')
            ->get();

        $totalPoints = $violations->sum('points');

        $w1 = $school->violation_warning1 ?? 10;
        $w2 = $school->violation_warning2 ?? 20;
        $w3 = $school->violation_warning3 ?? 30;

        $warningLevel = match(true) {
            $totalPoints >= $w3 => 3,
            $totalPoints >= $w2 => 2,
            $totalPoints >= $w1 => 1,
            default             => 0,
        };

        // Rekap absensi semester ini
        $activeYear = $school->academicYears()->where('is_active', true)->first();
        $alfaThisSemester = 0;
        $alfaLimit = $school->alfa_limit_per_semester ?? 10;

        if ($activeYear) {
            $alfaThisSemester = Attendance::where('student_id', $student->id)
                ->where('status', 'alfa')
                ->whereBetween('scanned_at', [
                    $activeYear->start_date ?? now()->startOfYear(),
                    $activeYear->end_date   ?? now()->endOfYear(),
                ])->count();
        }

        return view('siswa.violations', compact(
            'violations', 'totalPoints', 'w1', 'w2', 'w3',
            'warningLevel', 'alfaThisSemester', 'alfaLimit'
        ));
    }
}
