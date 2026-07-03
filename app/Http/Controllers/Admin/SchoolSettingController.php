<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        return view('admin.settings.school', compact('school'));
    }

    public function update(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'address'                  => ['nullable', 'string'],
            'phone'                    => ['nullable', 'string', 'max:20'],
            'email'                    => ['nullable', 'email', 'max:100'],
            'npsn'                     => ['nullable', 'string', 'max:20'],
            'latitude'                 => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'                => ['nullable', 'numeric', 'between:-180,180'],
            'attendance_radius_meters' => ['required', 'integer', 'min:50', 'max:1000'],
            'school_start_time'        => ['required', 'date_format:H:i'],
            'late_threshold_time'      => ['required', 'date_format:H:i'],
            'attendance_close_time'    => ['required', 'date_format:H:i'],
            'school_program_years'     => ['required', 'in:3,4'],
            'timezone'                 => ['required', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
        ]);

        // Validasi urutan jam
        if ($validated['school_start_time'] >= $validated['late_threshold_time']) {
            return back()->withErrors([
                'late_threshold_time' => 'Batas terlambat harus setelah jam buka absensi.'
            ])->withInput();
        }

        if ($validated['late_threshold_time'] >= $validated['attendance_close_time']) {
            return back()->withErrors([
                'attendance_close_time' => 'Jam tutup absensi harus setelah batas terlambat.'
            ])->withInput();
        }

        // Tambahkan detik ke format jam
        $validated['school_start_time']     .= ':00';
        $validated['late_threshold_time']   .= ':00';
        $validated['attendance_close_time'] .= ':00';

        $school->update($validated);

        // Set timezone langsung setelah disimpan
        config(['app.timezone' => $validated['timezone']]);
        date_default_timezone_set($validated['timezone']);

        // Log perubahan
        ActivityLog::create([
            'school_id'  => $school->id,
            'user_id'    => auth()->id(),
            'user_name'  => auth()->user()->name,
            'user_role'  => 'admin',
            'action'     => 'school.settings.update',
            'new_values' => $validated,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }

    public function updateGps(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $school = auth()->user()->school;
        $school->update($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Koordinat GPS berhasil diperbarui.',
            'latitude'  => $school->latitude,
            'longitude' => $school->longitude,
        ]);
    }
}