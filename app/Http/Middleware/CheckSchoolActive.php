<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $school = $user->school;

        if (! $school || ! $school->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun sekolah tidak aktif. Hubungi administrator.']);
        }

        if ($school->active_until && $school->active_until->isPast()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Masa aktif langganan sekolah telah berakhir.']);
        }

        // Set timezone sesuai sekolah — penting untuk absensi
        if ($school->timezone) {
            config(['app.timezone' => $school->timezone]);
            date_default_timezone_set($school->timezone);
        }

        return $next($request);
    }
}