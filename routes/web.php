<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'school.active'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Admin
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
         ->middleware('role:admin')
         ->name('admin.dashboard');

    // Guru & Wali Kelas
    Route::get('/guru/dashboard', [DashboardController::class, 'guru'])
         ->middleware('role:guru,wali_kelas,kesiswaan')
         ->name('guru.dashboard');

    // Kesiswaan
    Route::get('/kesiswaan/dashboard', [DashboardController::class, 'kesiswaan'])
         ->middleware('role:kesiswaan,admin')
         ->name('kesiswaan.dashboard');

    // Siswa
    Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])
         ->middleware('role:siswa')
         ->name('siswa.dashboard');
});

require __DIR__.'/auth.php';