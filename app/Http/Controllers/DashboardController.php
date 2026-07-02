<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return match($user->role) {
            'admin'                  => redirect()->route('admin.dashboard'),
            'guru', 'wali_kelas'     => redirect()->route('guru.dashboard'),
            'kesiswaan'              => redirect()->route('kesiswaan.dashboard'),
            'siswa'                  => redirect()->route('siswa.dashboard'),
            default                  => view('dashboard'),
        };
    }

    public function admin()
    {
        $user = auth()->user();
        return view('dashboard.admin', compact('user'));
    }

    public function guru()
    {
        $user = auth()->user();
        return view('dashboard.guru', compact('user'));
    }

    public function kesiswaan()
    {
        $user = auth()->user();
        return view('dashboard.kesiswaan', compact('user'));
    }

    public function siswa()
    {
        $user = auth()->user();
        return view('dashboard.siswa', compact('user'));
    }
}