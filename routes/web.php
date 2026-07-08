<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\StudentScanController;
use App\Http\Controllers\Attendance\ClassQrController;
use App\Http\Controllers\Admin\SchoolSettingController;
use App\Http\Controllers\Admin\QrManagementController;
use App\Http\Controllers\Kesiswaan\ViolationController;
use App\Http\Controllers\Guru\AssignmentController;
use App\Http\Controllers\Siswa\StudentAssignmentController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/absensi/scan', [StudentScanController::class, 'landing'])
     ->name('attendance.scan.landing');

Route::get('/absensi/kelas/{slug}', [ClassQrController::class, 'scan'])
     ->name('attendance.class.scan');

Route::middleware(['auth', 'school.active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/pengaturan', [SchoolSettingController::class, 'index'])->name('settings.school');
        Route::put('/pengaturan', [SchoolSettingController::class, 'update'])->name('settings.school.update');
        Route::post('/pengaturan/gps', [SchoolSettingController::class, 'updateGps'])->name('settings.school.gps');
        Route::get('/qr', [QrManagementController::class, 'index'])->name('qr.index');
        Route::post('/qr/{classroom}/refresh', [QrManagementController::class, 'refreshToken'])->name('qr.refresh');
    });

    // GURU / WALI KELAS / KESISWAAN / ADMIN
    Route::middleware('role:guru,wali_kelas,kesiswaan,admin')->prefix('guru')->name('guru.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');

        // Absensi
        Route::prefix('absensi')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::post('/buka', [AttendanceController::class, 'openSession'])->name('open');
            Route::get('/sesi/{session}', [AttendanceController::class, 'show'])->name('show');
            Route::post('/sesi/{session}/refresh-qr', [AttendanceController::class, 'refreshQr'])->name('refresh-qr');
            Route::get('/sesi/{session}/rekap', [AttendanceController::class, 'recap'])->name('recap');
            Route::post('/sesi/{session}/manual', [AttendanceController::class, 'manualEntry'])->name('manual');
            Route::post('/sesi/{session}/roll-call', [AttendanceController::class, 'rollCall'])->name('roll-call');
            Route::patch('/sesi/{session}/tutup', [AttendanceController::class, 'close'])->name('close');
            Route::get('/kelas/{classroom}/cetak-qr', [ClassQrController::class, 'print'])->name('class.print-qr');
        });

        // Tugas & Nilai
        Route::prefix('tugas')->name('assignments.')->group(function () {
            Route::get('/', [AssignmentController::class, 'index'])->name('index');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::get('/nilai', [AssignmentController::class, 'scores'])->name('scores');
            Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
            Route::patch('/{assignment}/tutup', [AssignmentController::class, 'close'])->name('close');
            Route::post('/{assignment}/nilai', [AssignmentController::class, 'grade'])->name('grade');
            // Guru lihat file tugas siswa
            Route::get('/{assignment}/file/{submission}', [AssignmentController::class, 'viewSubmissionFile'])->name('view-file');
        });
    });

    // KESISWAAN
    Route::middleware('role:kesiswaan,admin')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kesiswaan'])->name('dashboard');
        Route::prefix('pelanggaran')->name('violations.')->group(function () {
            Route::get('/', [ViolationController::class, 'index'])->name('index');
            Route::post('/', [ViolationController::class, 'store'])->name('store');
            Route::get('/kategori', [ViolationController::class, 'categories'])->name('categories');
            Route::post('/kategori', [ViolationController::class, 'storeCategory'])->name('categories.store');
            Route::get('/{student}', [ViolationController::class, 'show'])->name('show');
            Route::patch('/{violation}/arsip', [ViolationController::class, 'archive'])->name('archive');
        });
    });

    // SISWA
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('siswa.dashboard');

        // Absensi
        Route::get('/absensi', fn() => view('attendance.student.absensi'))->name('attendance.absensi');
        Route::post('/absensi/submit', [StudentScanController::class, 'submit'])->name('attendance.submit');
        Route::get('/absensi/riwayat', [StudentScanController::class, 'history'])->name('attendance.history');

        // Pelanggaran
        Route::get('/pelanggaran', fn() => view('siswa.violations'))->name('violations');

        // Tugas & Nilai
        Route::prefix('tugas')->name('assignments.')->group(function () {
            Route::get('/', [StudentAssignmentController::class, 'index'])->name('index');
            Route::get('/nilai', [StudentAssignmentController::class, 'scores'])->name('scores');
            Route::get('/{assignment}', [StudentAssignmentController::class, 'show'])->name('show');
            Route::post('/{assignment}/kumpul', [StudentAssignmentController::class, 'submit'])->name('submit');
            // Siswa lihat file tugasnya sendiri
            Route::get('/{assignment}/file', [StudentAssignmentController::class, 'viewFile'])->name('view-file');
        });
    });
});

require __DIR__.'/auth.php';