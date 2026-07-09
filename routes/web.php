<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\StudentScanController;
use App\Http\Controllers\Attendance\ClassQrController;
use App\Http\Controllers\Admin\SchoolSettingController;
use App\Http\Controllers\Admin\QrManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\TeacherAttendanceAdminController;
use App\Http\Controllers\Kesiswaan\ViolationController;
use App\Http\Controllers\Guru\AssignmentController;
use App\Http\Controllers\Guru\TeacherAttendanceController;
use App\Http\Controllers\Siswa\StudentAssignmentController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/absensi/scan', [StudentScanController::class, 'landing'])
     ->name('attendance.scan.landing');

Route::get('/absensi/kelas/{slug}', [ClassQrController::class, 'scan'])
     ->name('attendance.class.scan');

Route::middleware(['auth', 'school.active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // Pengaturan Sekolah
        Route::get('/pengaturan', [SchoolSettingController::class, 'index'])->name('settings.school');
        Route::put('/pengaturan', [SchoolSettingController::class, 'update'])->name('settings.school.update');
        Route::post('/pengaturan/gps', [SchoolSettingController::class, 'updateGps'])->name('settings.school.gps');

        // Kelola QR Kelas
        Route::get('/qr', [QrManagementController::class, 'index'])->name('qr.index');
        Route::post('/qr/{classroom}/refresh', [QrManagementController::class, 'refreshToken'])->name('qr.refresh');

        // Manajemen User — /positions & /create HARUS di atas /{user}
        Route::get('/users/positions', [UserManagementController::class, 'positions'])->name('users.positions');
        Route::post('/users/positions', [UserManagementController::class, 'storePosition'])->name('users.positions.store');
        Route::delete('/users/positions/{position}', [UserManagementController::class, 'destroyPosition'])->name('users.positions.destroy');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [UserManagementController::class, 'toggleActive'])->name('users.toggle');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        // Tahun Ajaran
        Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::patch('/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

        // Jurusan
        Route::get('/majors', [MajorController::class, 'index'])->name('majors.index');
        Route::post('/majors', [MajorController::class, 'store'])->name('majors.store');
        Route::put('/majors/{major}', [MajorController::class, 'update'])->name('majors.update');
        Route::delete('/majors/{major}', [MajorController::class, 'destroy'])->name('majors.destroy');

        // Kelas — static routes HARUS di atas /{classroom}
        Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::get('/classrooms/{classroom}/edit', [ClassroomController::class, 'edit'])->name('classrooms.edit');
        Route::put('/classrooms/{classroom}', [ClassroomController::class, 'update'])->name('classrooms.update');
        Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');
        Route::post('/classrooms/{classroom}/siswa', [ClassroomController::class, 'assignStudent'])->name('classrooms.assign-student');
        Route::delete('/classrooms/{classroom}/siswa/{student}', [ClassroomController::class, 'removeStudent'])->name('classrooms.remove-student');
        Route::post('/classrooms/{classroom}/import', [ClassroomController::class, 'importStudents'])->name('classrooms.import');

        // Promosi siswa
        Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
        Route::post('/promotions/load-source', [PromotionController::class, 'loadSource'])->name('promotions.load-source');
        Route::post('/promotions/process', [PromotionController::class, 'process'])->name('promotions.process');
        Route::post('/promotions/transfer/{student}', [PromotionController::class, 'transferStudent'])->name('promotions.transfer');
        Route::post('/promotions/status/{student}', [PromotionController::class, 'updateStatus'])->name('promotions.update-status');

        // Kelompok & Mata Pelajaran — /groups HARUS di atas /{subject}
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::post('/subjects/groups', [SubjectController::class, 'storeGroup'])->name('subjects.groups.store');
        Route::put('/subjects/groups/{group}', [SubjectController::class, 'updateGroup'])->name('subjects.groups.update');
        Route::delete('/subjects/groups/{group}', [SubjectController::class, 'destroyGroup'])->name('subjects.groups.destroy');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        // Jadwal — /by-teacher HARUS di atas /{schedule}
        Route::get('/schedules/by-teacher', [ScheduleController::class, 'byTeacher'])->name('schedules.by-teacher');
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Absensi Guru — /rewards HARUS di atas /{id}
        Route::get('/teacher-attendance', [TeacherAttendanceAdminController::class, 'index'])->name('teacher-attendance.index');
        Route::post('/teacher-attendance/manual', [TeacherAttendanceAdminController::class, 'manualEntry'])->name('teacher-attendance.manual');
        Route::get('/teacher-attendance/rewards', [TeacherAttendanceAdminController::class, 'rewards'])->name('teacher-attendance.rewards');
        Route::post('/teacher-attendance/rewards/add', [TeacherAttendanceAdminController::class, 'addRewardPoint'])->name('teacher-attendance.add-reward');
        Route::post('/teacher-attendance/refresh-qr', [TeacherAttendanceAdminController::class, 'refreshQr'])->name('teacher-attendance.refresh-qr');
    });

    // ─────────────────────────────────────────────────────────────────────────
    // GURU / WALI KELAS / KESISWAAN / ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:guru,wali_kelas,kesiswaan,admin')->prefix('guru')->name('guru.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');

        // Absensi Siswa (kelola sesi)
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

        // Absensi Guru sendiri — /poin HARUS di atas /{id}
        Route::prefix('absensi-saya')->name('teacher-attendance.')->group(function () {
            Route::get('/', [TeacherAttendanceController::class, 'index'])->name('index');
            Route::post('/scan', [TeacherAttendanceController::class, 'scan'])->name('scan');
            Route::post('/status', [TeacherAttendanceController::class, 'submitStatus'])->name('submit-status');
            Route::get('/poin', [TeacherAttendanceController::class, 'rewards'])->name('rewards');
        });

        // Tugas & Nilai — /nilai HARUS di atas /{assignment}
        Route::prefix('tugas')->name('assignments.')->group(function () {
            Route::get('/', [AssignmentController::class, 'index'])->name('index');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::get('/nilai', [AssignmentController::class, 'scores'])->name('scores');
            Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
            Route::patch('/{assignment}/tutup', [AssignmentController::class, 'close'])->name('close');
            Route::post('/{assignment}/nilai', [AssignmentController::class, 'grade'])->name('grade');
            Route::get('/{assignment}/file/{submission}', [AssignmentController::class, 'viewSubmissionFile'])->name('view-file');
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    // KESISWAAN
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:kesiswaan,admin')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'kesiswaan'])->name('dashboard');

        // Pelanggaran — /kategori HARUS di atas /{student}
        Route::prefix('pelanggaran')->name('violations.')->group(function () {
            Route::get('/', [ViolationController::class, 'index'])->name('index');
            Route::post('/', [ViolationController::class, 'store'])->name('store');
            Route::get('/kategori', [ViolationController::class, 'categories'])->name('categories');
            Route::post('/kategori', [ViolationController::class, 'storeCategory'])->name('categories.store');
            Route::get('/{student}', [ViolationController::class, 'show'])->name('show');
            Route::patch('/{violation}/arsip', [ViolationController::class, 'archive'])->name('archive');
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('siswa.dashboard');

        // Absensi
        Route::get('/absensi', fn() => view('attendance.student.absensi'))->name('attendance.absensi');
        Route::post('/absensi/submit', [StudentScanController::class, 'submit'])->name('attendance.submit');
        Route::get('/absensi/riwayat', [StudentScanController::class, 'history'])->name('attendance.history');

        // Pelanggaran
        Route::get('/pelanggaran', fn() => view('siswa.violations'))->name('violations');

        // Tugas & Nilai — /nilai HARUS di atas /{assignment}
        Route::prefix('tugas')->name('assignments.')->group(function () {
            Route::get('/', [StudentAssignmentController::class, 'index'])->name('index');
            Route::get('/nilai', [StudentAssignmentController::class, 'scores'])->name('scores');
            Route::get('/{assignment}', [StudentAssignmentController::class, 'show'])->name('show');
            Route::post('/{assignment}/kumpul', [StudentAssignmentController::class, 'submit'])->name('submit');
            Route::get('/{assignment}/file', [StudentAssignmentController::class, 'viewFile'])->name('view-file');
        });
    });
});

require __DIR__.'/auth.php';