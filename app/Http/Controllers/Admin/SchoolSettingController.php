<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\School;
use App\Models\TeacherAttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'logo'                     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'violation_warning1'       => ['required', 'integer', 'min:1', 'max:999'],
            'violation_warning2'       => ['required', 'integer', 'min:1', 'max:999'],
            'violation_warning3'       => ['required', 'integer', 'min:1', 'max:999'],
            'alfa_limit_per_semester'       => ['required', 'integer', 'min:0', 'max:999'],
            'prakerin_points_no_checkin'    => ['nullable', 'integer', 'min:0', 'max:99'],
            'prakerin_points_no_checkout'   => ['nullable', 'integer', 'min:0', 'max:99'],
            'prakerin_points_no_journal'    => ['nullable', 'integer', 'min:0', 'max:99'],
            'teacher_checkin_open'     => ['required', 'date_format:H:i'],
            'teacher_checkin_late'     => ['required', 'date_format:H:i'],
            'teacher_checkin_close'    => ['required', 'date_format:H:i'],
            'teacher_checkout_open'    => ['required', 'date_format:H:i'],
            'teacher_checkout_close'   => ['required', 'date_format:H:i'],
        ]);

        // Validasi urutan jam siswa
        if ($validated['school_start_time'] >= $validated['late_threshold_time']) {
            return back()->withErrors([
                'late_threshold_time' => 'Batas terlambat harus setelah jam buka absensi.',
            ])->withInput();
        }
        if ($validated['late_threshold_time'] >= $validated['attendance_close_time']) {
            return back()->withErrors([
                'attendance_close_time' => 'Jam tutup absensi harus setelah batas terlambat.',
            ])->withInput();
        }

        // Validasi urutan batas peringatan
        if ($validated['violation_warning1'] >= $validated['violation_warning2']) {
            return back()->withErrors([
                'violation_warning2' => 'Batas Peringatan 2 harus lebih besar dari Peringatan 1.',
            ])->withInput();
        }
        if ($validated['violation_warning2'] >= $validated['violation_warning3']) {
            return back()->withErrors([
                'violation_warning3' => 'Batas Peringatan 3 harus lebih besar dari Peringatan 2.',
            ])->withInput();
        }

        // Tambahkan detik ke format jam
        $validated['school_start_time']     .= ':00';
        $validated['late_threshold_time']   .= ':00';
        $validated['attendance_close_time'] .= ':00';
        $validated['teacher_checkin_open']  .= ':00';
        $validated['teacher_checkin_late']  .= ':00';
        $validated['teacher_checkin_close'] .= ':00';
        $validated['teacher_checkout_open'] .= ':00';
        $validated['teacher_checkout_close'].= ':00';

        // Handle upload logo
        if ($request->hasFile('logo')) {
            if ($school->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($school->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($school->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('school-logos', 'public');
        }

        $school->update($validated);

        // Update sesi siswa aktif hari ini
        \App\Models\AttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->where('is_closed', false)
            ->update([
                'open_time'  => $validated['school_start_time'],
                'late_after' => $validated['late_threshold_time'],
                'close_time' => $validated['attendance_close_time'],
            ]);

        // Sinkronisasi sesi guru — dengan token unik per sesi
        $this->syncTeacherSessions($school, $validated);

        config(['app.timezone' => $validated['timezone']]);
        date_default_timezone_set($validated['timezone']);

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

    private function syncTeacherSessions(School $school, array $validated): void
    {
        // ── Sesi MASUK ──
        $existingMasuk = TeacherAttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->where('session_type', 'masuk')
            ->first();

        if ($existingMasuk) {
            // Update saja — jangan ganti qr_token agar guru yang sudah scan tidak bermasalah
            $existingMasuk->update([
                'open_time'  => $validated['teacher_checkin_open'],
                'close_time' => $validated['teacher_checkin_close'],
                'late_after' => $validated['teacher_checkin_late'],
                'is_active'  => true,
            ]);
        } else {
            // Buat baru dengan token unik (pastikan tidak duplicate)
            TeacherAttendanceSession::create([
                'school_id'    => $school->id,
                'session_date' => today(),
                'session_type' => 'masuk',
                'open_time'    => $validated['teacher_checkin_open'],
                'close_time'   => $validated['teacher_checkin_close'],
                'late_after'   => $validated['teacher_checkin_late'],
                'qr_token'     => $this->generateUniqueToken(),
                'is_active'    => true,
            ]);
        }

        // ── Sesi PULANG ──
        $existingPulang = TeacherAttendanceSession::where('school_id', $school->id)
            ->whereDate('session_date', today())
            ->where('session_type', 'pulang')
            ->first();

        if ($existingPulang) {
            $existingPulang->update([
                'open_time'  => $validated['teacher_checkout_open'],
                'close_time' => $validated['teacher_checkout_close'],
                'late_after' => null,
                'is_active'  => true,
            ]);
        } else {
            TeacherAttendanceSession::create([
                'school_id'    => $school->id,
                'session_date' => today(),
                'session_type' => 'pulang',
                'open_time'    => $validated['teacher_checkout_open'],
                'close_time'   => $validated['teacher_checkout_close'],
                'late_after'   => null,
                'qr_token'     => $this->generateUniqueToken(),
                'is_active'    => true,
            ]);
        }
    }

    /**
     * Generate token unik yang dipastikan tidak ada di database.
     */
    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (TeacherAttendanceSession::where('qr_token', $token)->exists());

        return $token;
    }

    public function updateGps(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $school = auth()->user()->school;

        if ($request->hasFile('logo')) {
            if ($school->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($school->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($school->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('school-logos', 'public');
        }

        $school->update($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Koordinat GPS berhasil diperbarui.',
            'latitude'  => $school->latitude,
            'longitude' => $school->longitude,
        ]);
    }
}
