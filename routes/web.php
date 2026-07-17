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
use App\Http\Controllers\Guru\TeachingJournalController;
use App\Http\Controllers\Guru\TeacherAttendanceController;
use App\Http\Controllers\Siswa\StudentAssignmentController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/absensi/scan', [StudentScanController::class, 'landing'])
     ->name('attendance.scan.landing');

Route::get('/absensi/kelas/{slug}', [ClassQrController::class, 'scan'])
     ->name('attendance.class.scan');

// Route publik absensi guru — dibuka saat scan QR dari kamera HP
Route::get('/absensi-guru/{token}', [TeacherAttendanceController::class, 'scanFromUrl'])
     ->name('teacher.attendance.scan-url');
Route::post('/absensi-guru/confirm', [TeacherAttendanceController::class, 'confirmScan'])
     ->middleware('auth')
     ->name('teacher.attendance.confirm');

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
        Route::get('/qr/{classroom}/cetak', [\App\Http\Controllers\Attendance\ClassQrController::class, 'print'])->name('qr.cetak');

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

        // Absensi Guru — static routes HARUS di atas /{id}
        Route::get('/teacher-attendance/qr', [TeacherAttendanceAdminController::class, 'qr'])->name('teacher-attendance.qr');
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

        // Jurnal Mengajar — /isi & /riwayat HARUS di atas /{journal}
        Route::prefix('jurnal')->name('journal.')->group(function () {
            Route::get('/', [TeachingJournalController::class, 'index'])->name('index');
            Route::get('/isi', [TeachingJournalController::class, 'create'])->name('create');
            Route::post('/', [TeachingJournalController::class, 'store'])->name('store');
            Route::get('/riwayat', [TeachingJournalController::class, 'history'])->name('history');
            Route::get('/{journal}', [TeachingJournalController::class, 'show'])->name('show');
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

        Route::get('/payment', [\App\Http\Controllers\Siswa\PaymentController::class, 'index'])->name('payment.index');
        Route::get('/payment/{bill}', [\App\Http\Controllers\Siswa\PaymentController::class, 'show'])->name('payment.show');
        Route::post('/payment/{bill}/upload', [\App\Http\Controllers\Siswa\PaymentController::class, 'uploadReceipt'])->name('payment.upload');


    });

    // ─────────────────────────────────────────────────────────────────────────
    // BENDAHARA
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:bendahara,kepala_sekolah')->prefix('bendahara')->name('bendahara.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'bendahara'])->name('dashboard');

        // Jenis & Tarif
        Route::get('/payment-types', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'index'])->name('payment-types.index');
        Route::post('/payment-types', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'store'])->name('payment-types.store');
        Route::put('/payment-types/{paymentType}', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'update'])->name('payment-types.update');
        Route::patch('/payment-types/{paymentType}/toggle', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'toggleActive'])->name('payment-types.toggle');
        Route::post('/payment-types/{paymentType}/rates', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'storeRate'])->name('payment-types.rates.store');
        Route::put('/payment-rates/{rate}', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'updateRate'])->name('payment-rates.update');
        Route::delete('/payment-rates/{rate}', [\App\Http\Controllers\Bendahara\PaymentTypeController::class, 'destroyRate'])->name('payment-rates.destroy');
        // Beasiswa — /search HARUS di atas /{discount}
        Route::get('/discounts', [\App\Http\Controllers\Bendahara\StudentDiscountController::class, 'index'])->name('discounts.index');
        Route::get('/discount-programs', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'index'])->name('discount-programs.index');
        Route::post('/discount-programs', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'store'])->name('discount-programs.store');
        Route::put('/discount-programs/{program}', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'update'])->name('discount-programs.update');
        Route::patch('/discount-programs/{program}/toggle', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'toggle'])->name('discount-programs.toggle');
        Route::post('/discount-programs/{program}/apply', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'apply'])->name('discount-programs.apply');
        Route::get('/discount-programs/{program}/members', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'members'])->name('discount-programs.members');
        Route::post('/discount-programs/{program}/members', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'addMembers'])->name('discount-programs.members.add');
        Route::get('/discount-programs/{program}/search', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'searchStudents'])->name('discount-programs.search');
        Route::patch('/discount-program-members/{member}', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'updateMember'])->name('discount-programs.member.update');
        Route::delete('/discount-program-members/{member}', [\App\Http\Controllers\Bendahara\DiscountProgramController::class, 'removeMember'])->name('discount-programs.member.remove');
        Route::get('/discounts/search', [\App\Http\Controllers\Bendahara\StudentDiscountController::class, 'searchStudent'])->name('discounts.search');
        Route::post('/discounts', [\App\Http\Controllers\Bendahara\StudentDiscountController::class, 'store'])->name('discounts.store');
        Route::delete('/discounts/{discount}', [\App\Http\Controllers\Bendahara\StudentDiscountController::class, 'destroy'])->name('discounts.destroy');

        // Tagihan — static routes HARUS di atas /{bill}
        Route::get('/bills', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'index'])->name('bills.index');
        Route::get('/bills/create', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'store'])->name('bills.store');
        Route::post('/bills/check-rate', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'checkRate'])->name('bills.check-rate');
        Route::get('/bills/student/{student}', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'studentBills'])->name('bills.student');
        Route::get('/bills/overrides', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'overrides'])->name('bills.overrides');
        Route::post('/bills/overrides', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'storeOverride'])->name('bills.overrides.store');
        Route::delete('/bills/overrides/{override}', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'destroyOverride'])->name('bills.overrides.destroy');
        Route::get('/bills/{bill}/receipt', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'receipt'])->name('bills.receipt');
        Route::get('/bills/{bill}', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'show'])->name('bills.show');
        Route::get('/bills/{bill}/edit', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'edit'])->name('bills.edit');
        Route::put('/bills/{bill}', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'update'])->name('bills.update');
        Route::delete('/bills/{bill}', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'destroy'])->name('bills.destroy');
        Route::post('/bills/{bill}/cash', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'storeCash'])->name('bills.cash');
        Route::patch('/bills/{bill}/waive', [\App\Http\Controllers\Bendahara\PaymentBillController::class, 'waive'])->name('bills.waive');

        // Transaksi — /index HARUS di atas /{transaction}
        Route::get('/transactions', [\App\Http\Controllers\Bendahara\PaymentTransactionController::class, 'index'])->name('transactions.index');
        Route::patch('/transactions/{transaction}/approve', [\App\Http\Controllers\Bendahara\PaymentTransactionController::class, 'approve'])->name('transactions.approve');
        Route::patch('/transactions/{transaction}/reject', [\App\Http\Controllers\Bendahara\PaymentTransactionController::class, 'reject'])->name('transactions.reject');
        Route::get('/transactions/{transaction}/receipt', [\App\Http\Controllers\Bendahara\PaymentTransactionController::class, 'viewReceipt'])->name('transactions.receipt');

        // ── KEUANGAN SEKOLAH ────────────────────────────────────────────────────
        // Dashboard keuangan
        Route::get('/finance', [\App\Http\Controllers\Bendahara\FinanceDashboardController::class, 'index'])->name('finance.index');

        // Sumber dana — /incomes harus di atas /{fundSource}
        Route::get('/fund-sources', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'index'])->name('fund-sources.index');
        Route::post('/fund-sources', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'store'])->name('fund-sources.store');
        Route::put('/fund-sources/{fundSource}', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'update'])->name('fund-sources.update');
        Route::patch('/fund-sources/{fundSource}/toggle', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'toggleActive'])->name('fund-sources.toggle');
        Route::get('/fund-sources/{fundSource}/incomes', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'incomes'])->name('fund-sources.incomes');
        Route::post('/fund-sources/{fundSource}/incomes', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'storeIncome'])->name('fund-sources.incomes.store');
        Route::put('/fund-income/{income}', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'updateIncome'])->name('fund-sources.incomes.update');
        Route::delete('/fund-income/{income}', [\App\Http\Controllers\Bendahara\FundSourceController::class, 'destroyIncome'])->name('fund-sources.incomes.destroy');

        // Kategori pengeluaran — /store HARUS di atas /{category}
        Route::get('/expense-categories', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'categories'])->name('expenses.categories');
        Route::post('/expense-categories', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
        Route::put('/expense-categories/{category}', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'updateCategory'])->name('expenses.categories.update');

        // Pengeluaran — /create, /pending HARUS di atas /{expense}
        Route::get('/expenses', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/pending', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'pendingApprovals'])->name('expenses.pending');
        Route::get('/expenses/{expense}', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'show'])->name('expenses.show');
        Route::patch('/expenses/{expense}/approve', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::patch('/expenses/{expense}/reject', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'reject'])->name('expenses.reject');
        Route::delete('/expenses/{expense}', [\App\Http\Controllers\Bendahara\ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Penggajian (placeholder, akan diisi fase 2)
        Route::get('/payroll', fn() => view('bendahara.payroll.index'))->name('payroll.index');
    });

    // ─────────────────────────────────────────────────────────────────────────
    // KEPALA SEKOLAH
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('role:kepala_sekolah')->prefix('kepala')->name('kepala.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kepala'])->name('dashboard');
    });

});

require __DIR__.'/auth.php';