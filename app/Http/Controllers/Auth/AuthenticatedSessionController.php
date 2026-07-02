<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        return redirect()->intended($this->redirectByRole($user->role));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectByRole(string $role): string
    {
        return match($role) {
            'admin'      => route('admin.dashboard'),
            'guru'       => route('guru.dashboard'),
            'wali_kelas' => route('guru.dashboard'),
            'kesiswaan'  => route('kesiswaan.dashboard'),
            'siswa'      => route('siswa.dashboard'),
            default      => route('dashboard'),
        };
    }
}