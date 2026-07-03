<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Attendance\AttendanceSessionController;
use App\Http\Controllers\Attendance\StudentScanController;
use App\Http\Controllers\Attendance\ManualOverrideController;

Route::get('/', fn() => redirect()->route('login'));

// ── Scan QR publik (redirect ke login jika belum login) ──────────────────
Route::get('/absensi/scan', [StudentScanController::class, 'landing'])
     ->name('attendance.student.scan');

Route::middleware(['auth', 'school.active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── ADMIN ────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });

    // ── GURU & WALI KELAS ────────────────────────────────────────────────
    Route::middleware('role:guru,wali_kelas,kesiswaan,admin')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');

        // Absensi sesi
        Route::get('/absensi', [AttendanceSessionController::class, 'index'])->name('attendance.index');
        Route::get('/absensi/buka', [AttendanceSessionController::class, 'create'])->name('attendance.create');
        Route::post('/absensi/buka', [AttendanceSessionController::class, 'store'])->name('attendance.store');
        Route::get('/absensi/sesi/{session}', [AttendanceSessionController::class, 'show'])->name('attendance.show');
        Route::patch('/absensi/sesi/{session}/tutup', [AttendanceSessionController::class, 'close'])->name('attendance.close');
        Route::post('/absensi/sesi/{session}/refresh-qr', [AttendanceSessionController::class, 'refreshQr'])->name('attendance.refresh-qr');
        Route::get('/absensi/sesi/{session}/rekap', [AttendanceSessionController::class, 'recap'])->name('attendance.recap');
        Route::post('/absensi/sesi/{session}/roll-call', [AttendanceSessionController::class, 'rollCall'])->name('attendance.roll-call');
        Route::post('/absensi/sesi/{session}/koreksi', [ManualOverrideController::class, 'store'])->name('attendance.override');
    });

    // ── KESISWAAN ────────────────────────────────────────────────────────
    Route::middleware('role:kesiswaan,admin')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kesiswaan'])->name('dashboard');
    });

    // ── SISWA ────────────────────────────────────────────────────────────
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('siswa.dashboard');
        Route::post('/absensi/submit', [StudentScanController::class, 'submit'])->name('attendance.submit');
        Route::get('/absensi/riwayat', [StudentScanController::class, 'history'])->name('siswa.attendance.history');
    });
});

require __DIR__.'/auth.php';