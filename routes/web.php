<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\StudentScanController;

// ── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── QR Scan Landing (publik, redirect ke login jika belum auth) ───────────────
Route::get('/absensi/scan', [StudentScanController::class, 'landing'])
     ->name('attendance.scan.landing');

// ── Semua route butuh login + sekolah aktif ───────────────────────────────────
Route::middleware(['auth', 'school.active'])->group(function () {

    // Redirect ke dashboard sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')
         ->prefix('admin')
         ->name('admin.')
         ->group(function () {

             Route::get('/dashboard', [DashboardController::class, 'admin'])
                  ->name('dashboard');
         });

    // ─────────────────────────────────────────────────────────────────────────
    // GURU / WALI KELAS / KESISWAAN / ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:guru,wali_kelas,kesiswaan,admin')
         ->prefix('guru')
         ->name('guru.')
         ->group(function () {

             Route::get('/dashboard', [DashboardController::class, 'guru'])
                  ->name('dashboard');

             // ── Absensi Siswa ─────────────────────────────────────────────
             Route::prefix('absensi')
                  ->name('attendance.')
                  ->group(function () {

                      // Daftar kelas + status sesi hari ini
                      Route::get('/', [AttendanceController::class, 'index'])
                           ->name('index');

                      // Buka / perbarui sesi kelas
                      Route::post('/buka', [AttendanceController::class, 'openSession'])
                           ->name('open');

                      // Halaman tampil QR + rekap
                      Route::get('/sesi/{session}', [AttendanceController::class, 'show'])
                           ->name('show');

                      // Perbarui QR (AJAX)
                      Route::post('/sesi/{session}/refresh-qr', [AttendanceController::class, 'refreshQr'])
                           ->name('refresh-qr');

                      // Rekap real-time (AJAX polling)
                      Route::get('/sesi/{session}/rekap', [AttendanceController::class, 'recap'])
                           ->name('recap');

                      // Input manual oleh guru (AJAX)
                      Route::post('/sesi/{session}/manual', [AttendanceController::class, 'manualEntry'])
                           ->name('manual');

                      // Roll call / validasi kehadiran fisik
                      Route::post('/sesi/{session}/roll-call', [AttendanceController::class, 'rollCall'])
                           ->name('roll-call');

                      // Tutup sesi (siswa belum absen → alfa otomatis)
                      Route::patch('/sesi/{session}/tutup', [AttendanceController::class, 'close'])
                           ->name('close');
                  });
         });

    // ─────────────────────────────────────────────────────────────────────────
    // KESISWAAN
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:kesiswaan,admin')
         ->prefix('kesiswaan')
         ->name('kesiswaan.')
         ->group(function () {

             Route::get('/dashboard', [DashboardController::class, 'kesiswaan'])
                  ->name('dashboard');
         });

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:siswa')
         ->prefix('siswa')
         ->name('siswa.')
         ->group(function () {

             Route::get('/dashboard', [DashboardController::class, 'siswa'])
                  ->name('siswa.dashboard');

             // Submit absensi dari halaman scan QR (AJAX)
             Route::post('/absensi/submit', [StudentScanController::class, 'submit'])
                  ->name('attendance.submit');

             // Riwayat absensi per hari / bulan / semester
             Route::get('/absensi/riwayat', [StudentScanController::class, 'history'])
                  ->name('attendance.history');
         });
});

require __DIR__.'/auth.php';